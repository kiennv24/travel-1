<?php
/**
 * Media Library offload to R2.
 *
 * Hooks into WordPress upload flow to automatically send media to R2
 * and optionally rewrite URLs.
 *
 * @package R2CloudStorage
 */

namespace R2CS;

defined( 'ABSPATH' ) || exit;

class R2_Media_Offload {

	/** @var R2_Client */
	private $client;

	/** @var R2_Settings */
	private $settings;

	/** @var string Meta key to track offloaded files. */
	const META_KEY = '_r2cs_offloaded';

	/** @var string Meta key for remote key. */
	const REMOTE_KEY_META = '_r2cs_remote_key';

	/**
	 * @param R2_Client   $client
	 * @param R2_Settings $settings
	 */
	public function __construct( R2_Client $client, R2_Settings $settings ) {
		$this->client   = $client;
		$this->settings = $settings;

		// Only hook if offload is enabled and configured.
		if ( $this->settings->is_configured() ) {
			$this->init_hooks();
		}
	}

	/**
	 * Register WordPress hooks.
	 */
	private function init_hooks() {
		// Upload: offload after WordPress processes the upload.
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'offload_on_upload' ), 10, 2 );

		// Delete: remove from R2 when attachment is deleted.
		add_action( 'delete_attachment', array( $this, 'delete_from_r2' ) );

		// URL rewrite: serve from R2 instead of local.
		add_filter( 'wp_get_attachment_url', array( $this, 'rewrite_attachment_url' ), 10, 2 );

		// Intercept thumbnail/intermediate size URL generation.
		add_filter( 'image_downsize', array( $this, 'rewrite_image_downsize' ), 10, 3 );

		// Also rewrite srcset URLs for responsive images.
		add_filter( 'wp_calculate_image_srcset', array( $this, 'rewrite_srcset_urls' ), 10, 5 );

		/**
		 * Filter to allow add-ons to hook into post-offload actions.
		 */
		add_action( 'r2cs_after_offload', array( $this, 'maybe_remove_local' ), 10, 2 );
	}

	/**
	 * Offload attachment and its thumbnails after upload.
	 *
	 * @param array $metadata Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array Unmodified metadata.
	 */
	public function offload_on_upload( $metadata, $attachment_id ) {
		if ( ! $this->settings->get( 'offload_media' ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return $metadata;
		}

		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] );
		$prefix     = $this->settings->get( 'path_prefix', 'wp-content/uploads/' );

		// Upload the main file.
		$relative_path = str_replace( $base_dir, '', $file );
		$remote_key    = trailingslashit( $prefix ) . $relative_path;

		$result = $this->client->upload_file( $file, $remote_key );

		if ( is_wp_error( $result ) ) {
			return $metadata;
		}

		// Mark as offloaded.
		update_post_meta( $attachment_id, self::META_KEY, true );
		update_post_meta( $attachment_id, self::REMOTE_KEY_META, $remote_key );

		// Upload thumbnails.
		if ( ! empty( $metadata['sizes'] ) ) {
			$file_dir = dirname( $relative_path );
			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$thumb_path = $base_dir . trailingslashit( $file_dir ) . $size_data['file'];
				if ( file_exists( $thumb_path ) ) {
					$thumb_remote = trailingslashit( $prefix ) . trailingslashit( $file_dir ) . $size_data['file'];
					$this->client->upload_file( $thumb_path, $thumb_remote );
				}
			}
		}

		/**
		 * Fires after an attachment is offloaded to R2.
		 *
		 * @param int   $attachment_id Attachment ID.
		 * @param array $metadata      Attachment metadata.
		 */
		do_action( 'r2cs_after_offload', $attachment_id, $metadata );

		return $metadata;
	}

	/**
	 * Delete object from R2 when attachment is deleted.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public function delete_from_r2( $attachment_id ) {
		if ( ! get_post_meta( $attachment_id, self::META_KEY, true ) ) {
			return;
		}

		$remote_key = get_post_meta( $attachment_id, self::REMOTE_KEY_META, true );
		if ( ! empty( $remote_key ) ) {
			$this->client->delete_object( $remote_key );
		}

		// Delete thumbnails.
		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! empty( $metadata['sizes'] ) ) {
			$prefix    = $this->settings->get( 'path_prefix', 'wp-content/uploads/' );
			$upload_dir = wp_upload_dir();
			$base_dir   = trailingslashit( $upload_dir['basedir'] );
			$file       = get_attached_file( $attachment_id );
			$relative   = str_replace( $base_dir, '', $file );
			$file_dir   = dirname( $relative );

			foreach ( $metadata['sizes'] as $size_data ) {
				$thumb_remote = trailingslashit( $prefix ) . trailingslashit( $file_dir ) . $size_data['file'];
				$this->client->delete_object( $thumb_remote );
			}
		}
	}

	/**
	 * Rewrite attachment URL to R2.
	 *
	 * @param string $url           Original URL.
	 * @param int    $attachment_id Attachment ID.
	 * @return string
	 */
	public function rewrite_attachment_url( $url, $attachment_id ) {
		if ( ! get_post_meta( $attachment_id, self::META_KEY, true ) ) {
			return $url;
		}

		$remote_key = get_post_meta( $attachment_id, self::REMOTE_KEY_META, true );
		if ( empty( $remote_key ) ) {
			return $url;
		}

		// The R2 S3 endpoint always requires authentication, so we must use
		// presigned URLs unless a custom domain with public access is configured.
		$custom_domain = $this->settings->get( 'custom_domain' );
		if ( $this->settings->get( 'signed_urls' ) || empty( $custom_domain ) ) {
			$expiry = (int) $this->settings->get( 'signed_expiry', 3600 );
			return $this->client->get_presigned_url( $remote_key, $expiry );
		}

		return $this->client->get_object_url( $remote_key );
	}

	/**
	 * Intercept image_downsize to return correct R2 URLs for intermediate sizes.
	 *
	 * Without this, WordPress tries to manipulate the presigned URL string to
	 * swap the filename, which breaks the signature query parameters.
	 *
	 * @param false|array $downsize  False to proceed with default behavior.
	 * @param int         $id        Attachment ID.
	 * @param string      $size      Requested image size name.
	 * @return false|array Array of (url, width, height, is_intermediate) or false.
	 */
	public function rewrite_image_downsize( $downsize, $id, $size ) {
		if ( ! get_post_meta( $id, self::META_KEY, true ) ) {
			return false;
		}

		$metadata = wp_get_attachment_metadata( $id );
		if ( empty( $metadata ) ) {
			return false;
		}

		$prefix        = $this->settings->get( 'path_prefix', 'wp-content/uploads/' );
		$custom_domain = $this->settings->get( 'custom_domain' );
		$use_signed    = $this->settings->get( 'signed_urls' ) || empty( $custom_domain );
		$expiry        = (int) $this->settings->get( 'signed_expiry', 3600 );

		// Determine the file path relative to the uploads directory.
		$attached_file = get_post_meta( $id, '_wp_attached_file', true );
		$file_dir      = dirname( $attached_file );

		// Handle 'full' size.
		if ( 'full' === $size || empty( $metadata['sizes'] ) ) {
			$remote_key = get_post_meta( $id, self::REMOTE_KEY_META, true );
			if ( empty( $remote_key ) ) {
				return false;
			}

			$url    = $use_signed ? $this->client->get_presigned_url( $remote_key, $expiry ) : $this->client->get_object_url( $remote_key );
			$width  = isset( $metadata['width'] ) ? (int) $metadata['width'] : 0;
			$height = isset( $metadata['height'] ) ? (int) $metadata['height'] : 0;

			return array( $url, $width, $height, false );
		}

		// Named size (thumbnail, medium, large, etc.).
		if ( is_string( $size ) && isset( $metadata['sizes'][ $size ] ) ) {
			$size_data  = $metadata['sizes'][ $size ];
			$remote_key = trailingslashit( $prefix ) . trailingslashit( $file_dir ) . $size_data['file'];
			$url        = $use_signed ? $this->client->get_presigned_url( $remote_key, $expiry ) : $this->client->get_object_url( $remote_key );

			return array( $url, (int) $size_data['width'], (int) $size_data['height'], true );
		}

		// Array size [width, height] — find the best match.
		if ( is_array( $size ) ) {
			$candidates = array();
			foreach ( $metadata['sizes'] as $size_data ) {
				if ( $size_data['width'] == $size[0] && $size_data['height'] == $size[1] ) {
					$remote_key = trailingslashit( $prefix ) . trailingslashit( $file_dir ) . $size_data['file'];
					$url        = $use_signed ? $this->client->get_presigned_url( $remote_key, $expiry ) : $this->client->get_object_url( $remote_key );

					return array( $url, (int) $size_data['width'], (int) $size_data['height'], true );
				}
			}
		}

		// Requested size not found; fall back to full.
		$remote_key = get_post_meta( $id, self::REMOTE_KEY_META, true );
		if ( ! empty( $remote_key ) ) {
			$url    = $use_signed ? $this->client->get_presigned_url( $remote_key, $expiry ) : $this->client->get_object_url( $remote_key );
			$width  = isset( $metadata['width'] ) ? (int) $metadata['width'] : 0;
			$height = isset( $metadata['height'] ) ? (int) $metadata['height'] : 0;

			return array( $url, $width, $height, false );
		}

		return false;
	}

	/**
	 * Rewrite srcset URLs to R2.
	 *
	 * @param array  $sources       Array of srcset sources.
	 * @param array  $size_array    Size array.
	 * @param string $image_src     Image src URL.
	 * @param array  $image_meta    Image metadata.
	 * @param int    $attachment_id Attachment ID.
	 * @return array
	 */
	public function rewrite_srcset_urls( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! get_post_meta( $attachment_id, self::META_KEY, true ) ) {
			return $sources;
		}

		$upload_dir = wp_upload_dir();
		$prefix     = $this->settings->get( 'path_prefix', 'wp-content/uploads/' );

		$custom_domain = $this->settings->get( 'custom_domain' );

		foreach ( $sources as &$source ) {
			// Convert local URL to R2 URL.
			$relative = str_replace( trailingslashit( $upload_dir['baseurl'] ), '', $source['url'] );
			$remote_key = trailingslashit( $prefix ) . $relative;

			if ( $this->settings->get( 'signed_urls' ) || empty( $custom_domain ) ) {
				$expiry = (int) $this->settings->get( 'signed_expiry', 3600 );
				$source['url'] = $this->client->get_presigned_url( $remote_key, $expiry );
			} else {
				$source['url'] = $this->client->get_object_url( $remote_key );
			}
		}

		return $sources;
	}

	/**
	 * Remove local file after offload if configured.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 */
	public function maybe_remove_local( $attachment_id, $metadata ) {
		if ( ! $this->settings->get( 'remove_local' ) ) {
			return;
		}

		$file = get_attached_file( $attachment_id );
		if ( $file && file_exists( $file ) ) {
			wp_delete_file( $file );
		}

		// Remove thumbnails.
		if ( ! empty( $metadata['sizes'] ) ) {
			$upload_dir = wp_upload_dir();
			$base_dir   = trailingslashit( $upload_dir['basedir'] );
			$relative   = str_replace( $base_dir, '', $file );
			$file_dir   = $base_dir . dirname( $relative );

			foreach ( $metadata['sizes'] as $size_data ) {
				$thumb = trailingslashit( $file_dir ) . $size_data['file'];
				if ( file_exists( $thumb ) ) {
					wp_delete_file( $thumb );
				}
			}
		}
	}

	/**
	 * Check if an attachment is offloaded.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	public function is_offloaded( $attachment_id ) {
		return (bool) get_post_meta( $attachment_id, self::META_KEY, true );
	}

	/**
	 * Manually offload a single attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return true|\WP_Error
	 */
	public function offload_attachment( $attachment_id ) {
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return new \WP_Error( 'r2cs_file_missing', __( 'Local file not found.', 'r2-cloud-storage' ) );
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		$this->offload_on_upload( $metadata ?: array(), $attachment_id );

		// Force the offload regardless of setting.
		$upload_dir    = wp_upload_dir();
		$base_dir      = trailingslashit( $upload_dir['basedir'] );
		$prefix        = $this->settings->get( 'path_prefix', 'wp-content/uploads/' );
		$relative_path = str_replace( $base_dir, '', $file );
		$remote_key    = trailingslashit( $prefix ) . $relative_path;

		$result = $this->client->upload_file( $file, $remote_key );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		update_post_meta( $attachment_id, self::META_KEY, true );
		update_post_meta( $attachment_id, self::REMOTE_KEY_META, $remote_key );

		return true;
	}

	/**
	 * Get offload statistics.
	 *
	 * @return array { total: int, offloaded: int, pending: int }
	 */
	public function get_stats() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Aggregate count, not cacheable.
		$total = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
			'attachment'
		) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Aggregate count, not cacheable.
		$offloaded = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'",
			self::META_KEY
		) );

		return array(
			'total'     => $total,
			'offloaded' => $offloaded,
			'pending'   => max( 0, $total - $offloaded ),
		);
	}
}
