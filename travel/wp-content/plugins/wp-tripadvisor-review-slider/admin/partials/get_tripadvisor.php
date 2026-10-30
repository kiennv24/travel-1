<?php

/**
 * Provide a admin area view for the plugin
 *
 * Get TripAdvisor Reviews — multiple sources with AJAX download per source.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/admin/partials
 */

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

// Ensure crawls option exists and migrate legacy single URL.
$tripadvisor_crawls = $this->wptripadvisor_get_crawls();

// Delete source if requested.
if ( isset( $_GET['ract'] ) && $_GET['ract'] === 'del' && isset( $_GET['pageid'] ) ) {
	check_admin_referer( 'wptripadvisor_del_source' );
	$del_pageid = sanitize_text_field( wp_unslash( $_GET['pageid'] ) );
	$this->wptripadvisor_delete_source( $del_pageid );
	$tripadvisor_crawls = $this->wptripadvisor_get_crawls();
	add_settings_error( 'tripadvisor-radio', 'wptripadvisor_message', __( 'Source deleted.', 'wp-tripadvisor-review-slider' ), 'updated' );
}

$del_base = wp_nonce_url(
	admin_url( 'admin.php?page=wp_tripadvisor-get_tripadvisor&ract=del' ),
	'wptripadvisor_del_source'
);
?>

<div class="">
<h1></h1>
<div class="wrap" id="wp_rev_maindiv">

<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png?v=' . $this->version ); ?>">
<?php
include 'tabmenu.php';
?>
	<div class="wpfbr_margin10">
		<div class="w3-col welcomediv w3-container w3-white w3-border w3-border-light-gray2 w3-round-small">

			<p>
				<?php esc_html_e( 'Add one or more TripAdvisor business pages, then download reviews for each source. Badge averages use the official TripAdvisor rating and review count for that source.', 'wp-tripadvisor-review-slider' ); ?>
				<br><?php esc_html_e( 'Note: The free version does not currently work for Vacation Rentals.', 'wp-tripadvisor-review-slider' ); ?>
			</p>
			<p class="small_message" style="margin-top:8px;">
				<?php
				echo wp_kses_post(
					__( 'Tired of downloading manually? <a href="https://wpreviewslider.com/" target="_blank">Upgrade to Pro</a> to auto-sync new reviews daily across multiple locations. Use code <strong>WPPRO15</strong> for 15% off.', 'wp-tripadvisor-review-slider' )
				);
				?>
			</p>

			<?php settings_errors( 'tripadvisor-radio' ); ?>

			<div id="wptrip_add_source_box" style="margin-bottom:20px;">
				<h3><?php esc_html_e( 'Add TripAdvisor Source', 'wp-tripadvisor-review-slider' ); ?></h3>
				<p>
					<label for="tripadvisor_new_url"><strong><?php esc_html_e( 'Business URL', 'wp-tripadvisor-review-slider' ); ?></strong></label><br>
					<input type="text" id="tripadvisor_new_url" class="regular-text" style="width:100%;max-width:700px;" placeholder="https://www.tripadvisor.com/Restaurant_Review-g30620-d10262422-Reviews-Example-City.html" value="">
				</p>
				<p>
					<label for="tripadvisor_new_name"><strong><?php esc_html_e( 'Business Name (optional)', 'wp-tripadvisor-review-slider' ); ?></strong></label><br>
					<input type="text" id="tripadvisor_new_name" class="regular-text" style="width:100%;max-width:400px;" placeholder="<?php esc_attr_e( 'Leave blank to detect from URL', 'wp-tripadvisor-review-slider' ); ?>" value="">
				</p>
				<p>
					<button type="button" id="wptrip_add_source" class="button button-primary"><?php esc_html_e( 'Add Source', 'wp-tripadvisor-review-slider' ); ?></button>
					<span id="wptrip_add_loader" class="wprevloader" style="display:none;width:20px;height:20px;border-width:3px;vertical-align:middle;margin-left:8px;"></span>
					<span id="wptrip_add_msg" style="margin-left:8px;"></span>
				</p>
				<p class="description">
					<?php esc_html_e( 'Example:', 'wp-tripadvisor-review-slider' ); ?>
					https://www.tripadvisor.com/Restaurant_Review-g30620-d10262422-Reviews-Yellowhammer_Brewing-Huntsville_Alabama.html
				</p>
			</div>

			<div id="currentsources">
				<style>
				#currentsources table { max-width: 100%; table-layout: fixed; word-wrap: break-word; }
				#currentsources table td { word-wrap: break-word; overflow-wrap: break-word; }
				#currentsources .trip-source-msg { display: inline-block; margin-left: 8px; }
				#currentsources .buttonloader2.wprevloader { display:none; width:20px; height:20px; border-width:3px; vertical-align:middle; margin-left:6px; }
				</style>
				<table class="w3-table-all wpfbr_mb15 w3-white w3-border w3-border-light-gray2 w3-round-small">
					<tr>
						<th><?php esc_html_e( 'Business Name', 'wp-tripadvisor-review-slider' ); ?></th>
						<th><?php esc_html_e( 'Page ID', 'wp-tripadvisor-review-slider' ); ?></th>
						<th><?php esc_html_e( 'Source Avg / Total', 'wp-tripadvisor-review-slider' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wp-tripadvisor-review-slider' ); ?></th>
					</tr>
					<tbody id="wptrip_sources_tbody">
					<?php
					$source_count = 0;
					if ( is_array( $tripadvisor_crawls ) ) {
						foreach ( $tripadvisor_crawls as $pageid => $source ) {
							if ( ! is_array( $source ) || $pageid === '' || $pageid === '0' ) {
								continue;
							}
							$source_count++;
							$bname  = isset( $source['businessname'] ) ? $source['businessname'] : '';
							$url    = isset( $source['url'] ) ? $source['url'] : '';
							$avg    = isset( $source['avg'] ) ? $source['avg'] : '';
							$total  = isset( $source['total'] ) ? $source['total'] : '';
							$avg_total_label = ( $avg !== '' || $total !== '' ) ? esc_html( $avg ) . ' / ' . esc_html( $total ) : '—';
							$del_url = add_query_arg( 'pageid', rawurlencode( $pageid ), $del_base );
							?>
							<tr data-pageid="<?php echo esc_attr( $pageid ); ?>">
								<td>
									<?php echo esc_html( $bname ); ?>
									<?php if ( $url ) : ?>
										<br><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View on TripAdvisor', 'wp-tripadvisor-review-slider' ); ?></a>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $pageid ); ?></td>
								<td class="trip-source-stats"><?php echo $avg_total_label; ?></td>
								<td>
									<button type="button" class="button button-primary downloadrevs" data-pageid="<?php echo esc_attr( $pageid ); ?>"><?php esc_html_e( 'Download Reviews', 'wp-tripadvisor-review-slider' ); ?></button>
									<span class="buttonloader2 wprevloader"></span>
									<a class="button" style="color:#a00;" href="<?php echo esc_url( $del_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this source and its reviews?', 'wp-tripadvisor-review-slider' ) ); ?>');"><?php esc_html_e( 'Delete', 'wp-tripadvisor-review-slider' ); ?></a>
									<span class="trip-source-msg"></span>
								</td>
							</tr>
							<?php
						}
					}
					if ( $source_count === 0 ) {
						echo '<tr class="wptrip-no-sources"><td colspan="4">' . esc_html__( 'No sources yet. Add a TripAdvisor business URL above.', 'wp-tripadvisor-review-slider' ) . '</td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>

			<p><b><?php esc_html_e( 'The Pro version can download all your reviews with avatars from multiple locations and check for new reviews daily!', 'wp-tripadvisor-review-slider' ); ?></b></p>

		</div>
	</div>
</div>
</div>

<div id="popup_info" class="popup-wrapper wptripadvisor_hide">
  <div class="popup-content">
	<div class="popup-title">
	  <button type="button" class="popup-close">&times;</button>
	  <h3 id="popup_titletext"></h3>
	</div>
	<div class="popup-body">
	  <div id="popup_bobytext1"></div>
	  <div id="popup_bobytext2"></div>
	</div>
  </div>
</div>
