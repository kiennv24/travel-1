<?php

/**
 * Provide a admin area view for the plugin
 *
 * Review List — hide, edit, delete, and Source Name column (Google parity).
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

$nonce = wp_create_nonce( 'my-nonce' );
$html  = '';
// db function variables
global $wpdb;
$table_name  = $wpdb->prefix . 'wptripadvisor_reviews';
$rowsperpage = 20;

$dbmsg         = '';
$currentreview = new stdClass();
$currentreview->id                 = '';
$currentreview->rating             = '';
$currentreview->review_title       = '';
$currentreview->review_text        = '';
$currentreview->reviewer_name      = '';
$currentreview->reviewer_id        = '';
$currentreview->created_time       = '';
$currentreview->created_time_stamp = '';
$currentreview->userpic            = '';
$currentreview->review_length      = '';
$currentreview->type               = '';
$currentreview->pagename           = '';
$currentreview->from_url           = '';
$currentreview->hide               = '';

$default_avatar = plugin_dir_url( __FILE__ ) . 'tripadvisor_mystery_man.png';

/**
 * Verify action nonce; on failure ignore the action instead of dying the whole admin page.
 *
 * @return bool
 */
$wptrip_action_nonce_ok = static function () {
	$nonce_check = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
	return (bool) wp_verify_nonce( $nonce_check, 'my-nonce' );
};

// Load review for editing.
if ( isset( $_GET['editrev'] ) ) {
	if ( ! $wptrip_action_nonce_ok() ) {
		// Stale editrev in the URL (e.g. carried over from another tab) — ignore it.
		$_GET['editrev'] = null;
		unset( $_GET['editrev'] );
	} else {
		$rid = absint( $_GET['editrev'] );
		if ( $rid > 0 ) {
			$currentreview = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $rid ) );
			if ( ! $currentreview ) {
				$currentreview = new stdClass();
				$currentreview->id = '';
			}
		}
	}
}

// Delete one review.
if ( isset( $_GET['deleterev'] ) ) {
	if ( $wptrip_action_nonce_ok() ) {
		$rid = absint( $_GET['deleterev'] );
		if ( $rid > 0 ) {
			$wpdb->delete( $table_name, array( 'id' => $rid ), array( '%d' ) );
		}
	}
	$currenturl = remove_query_arg( array( 'deleterev', '_wpnonce' ) );
}

// Hide / unhide one review.
if ( isset( $_GET['hiderev'] ) ) {
	if ( $wptrip_action_nonce_ok() ) {
		$rid      = absint( $_GET['hiderev'] );
		$newvalue = isset( $_GET['newvalue'] ) ? sanitize_text_field( wp_unslash( $_GET['newvalue'] ) ) : '';
		if ( $newvalue !== 'yes' ) {
			$newvalue = '';
		}
		if ( $rid > 0 ) {
			$wpdb->update(
				$table_name,
				array( 'hide' => $newvalue ),
				array( 'id' => $rid ),
				array( '%s' ),
				array( '%d' )
			);

			// Keep hidden-reviews option in sync for re-downloads.
			$myreview = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $rid ) );
			if ( $myreview ) {
				$tripadvisorhidden = get_option( 'wptripadvisor_hidden_reviews' );
				$tripadvisorhiddenarray = $tripadvisorhidden ? json_decode( $tripadvisorhidden, true ) : array();
				if ( ! is_array( $tripadvisorhiddenarray ) ) {
					$tripadvisorhiddenarray = array();
				}
				$this_tripadvisor_val = $myreview->reviewer_name . '-' . $myreview->created_time_stamp . '-' . $myreview->review_length . '-' . $myreview->type . '-' . $myreview->rating;
				if ( $newvalue === 'yes' ) {
					if ( ! in_array( $this_tripadvisor_val, $tripadvisorhiddenarray, true ) ) {
						$tripadvisorhiddenarray[] = $this_tripadvisor_val;
					}
				} else {
					$key = array_search( $this_tripadvisor_val, $tripadvisorhiddenarray, true );
					if ( $key !== false ) {
						unset( $tripadvisorhiddenarray[ $key ] );
					}
				}
				update_option( 'wptripadvisor_hidden_reviews', wp_json_encode( array_values( $tripadvisorhiddenarray ) ) );
			}
		}
	}
}

// Delete by source (pagename).
if ( isset( $_GET['opt_type'] ) && $_GET['opt_type'] === 'page' && isset( $_GET['opt'] ) ) {
	if ( $wptrip_action_nonce_ok() ) {
		$delpagename  = sanitize_text_field( wp_unslash( $_GET['opt'] ) );
		$delpagename2 = str_replace( '&', '&amp;', $delpagename );
		$pagenamearray = $wpdb->get_col( "SELECT pagename FROM {$table_name} GROUP BY pagename" );
		if ( in_array( $delpagename2, $pagenamearray, true ) ) {
			$wpdb->delete( $table_name, array( 'pagename' => $delpagename2 ), array( '%s' ) );
		}
		if ( in_array( $delpagename, $pagenamearray, true ) ) {
			$wpdb->delete( $table_name, array( 'pagename' => $delpagename ), array( '%s' ) );
		}
	}
}

// Save edited review (avatar URL + date).
if ( isset( $_POST['wprevpro_submitreviewbtn'] ) ) {
	check_admin_referer( 'wprevpro_save_review' );
	$r_id       = isset( $_POST['editrid'] ) ? absint( $_POST['editrid'] ) : 0;
	$avatar_url = isset( $_POST['wprevpro_nr_avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['wprevpro_nr_avatar_url'] ) ) : '';
	$rdate_raw  = isset( $_POST['wprevpro_nr_date'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_nr_date'] ) ) : '';

	if ( $r_id > 0 ) {
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $r_id ) );
		$data     = array( 'userpic' => $avatar_url );
		$format   = array( '%s' );

		// Allow editing display date; keep stamp in sync for sorting.
		// Duplicate downloads still match on reviewer_name + review_length, so this is safe.
		$parsed_stamp = $rdate_raw !== '' ? strtotime( $rdate_raw ) : false;
		if ( $parsed_stamp ) {
			$created_time = date( 'Y-m-d H:i:s', $parsed_stamp );
			$data['created_time']       = $created_time;
			$data['created_time_stamp'] = $parsed_stamp;
			$format[] = '%s';
			$format[] = '%d';

			// Keep hidden-reviews fingerprint in sync when date changes.
			if ( $existing && $existing->hide === 'yes' ) {
				$old_val = $existing->reviewer_name . '-' . $existing->created_time_stamp . '-' . $existing->review_length . '-' . $existing->type . '-' . $existing->rating;
				$new_val = $existing->reviewer_name . '-' . $parsed_stamp . '-' . $existing->review_length . '-' . $existing->type . '-' . $existing->rating;
				$tripadvisorhidden = get_option( 'wptripadvisor_hidden_reviews' );
				$tripadvisorhiddenarray = $tripadvisorhidden ? json_decode( $tripadvisorhidden, true ) : array();
				if ( ! is_array( $tripadvisorhiddenarray ) ) {
					$tripadvisorhiddenarray = array();
				}
				$key = array_search( $old_val, $tripadvisorhiddenarray, true );
				if ( $key !== false ) {
					$tripadvisorhiddenarray[ $key ] = $new_val;
					update_option( 'wptripadvisor_hidden_reviews', wp_json_encode( array_values( $tripadvisorhiddenarray ) ) );
				}
			}
		}

		$updatetempquery = $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $r_id ),
			$format,
			array( '%d' )
		);
		if ( false !== $updatetempquery ) {
			$dbmsg = '<div id="setting-error-wprevpro_message" class="updated settings-error notice is-dismissible"><p><strong>' . esc_html__( 'Review Updated!', 'wp-tripadvisor-review-slider' ) . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}

		// Reset instead of re-fetching the just-saved review: keeping $currentreview->id
		// populated here would leave the edit panel open (and its fields filled) after
		// every save, since the panel's visibility/values are driven by $currentreview.
		$currentreview     = new stdClass();
		$currentreview->id = '';
	}
}
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

<div class="wptripadvisor_margin10">
	<a id="wptripadvisor_helpicon" class="wptripadvisor_btnicononly button dashicons-before dashicons-editor-help"></a>
	<a id="wptripadvisor_removeallbtn" data-sec="<?php echo esc_attr( $nonce ); ?>" class="button dashicons-before dashicons-no"><?php esc_html_e( 'Remove All Reviews', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="https://wpreviewslider.com/" target="_blank" class="button dashicons-before dashicons-plus-alt" title="<?php esc_attr_e( 'Pro feature', 'wp-tripadvisor-review-slider' ); ?>"><?php esc_html_e( 'Add Review', 'wp-tripadvisor-review-slider' ); ?> <span style="background:#ff6b35;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:bold;margin-left:4px;">PRO</span></a>
	<a href="https://wpreviewslider.com/" target="_blank" class="button dashicons-before dashicons-download" title="<?php esc_attr_e( 'Pro feature', 'wp-tripadvisor-review-slider' ); ?>"><?php esc_html_e( 'Download CSV', 'wp-tripadvisor-review-slider' ); ?> <span style="background:#ff6b35;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:bold;margin-left:4px;">PRO</span></a>
	<br><span class="small_message">
	<?php
	echo wp_kses_post(
		__( 'Manually add reviews, download/import a CSV, tag reviews, bulk edit, and more available in the <a href="https://wpreviewslider.com/" target="_blank">Pro Version</a> of this plugin!', 'wp-tripadvisor-review-slider' )
	);
	?>
	</span>
	<p>
	<?php
	esc_html_e( 'Click the eye icon to hide or show a review, the wrench to edit the reviewer photo, or the trash icon to delete.', 'wp-tripadvisor-review-slider' );
	?>
	</p>
	<div id="wprevpro_notices_area"><?php echo $dbmsg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin notice built above ?></div>
</div>

<div class="wprevpro_modal_overlay<?php echo empty( $currentreview->id ) ? '' : ' is-open'; ?>" id="wptripadvisor_new_review">
<div class="wprevpro_modal_box wprevpro_margin10 w3-container w3-white w3-round-small">
<button type="button" class="wprevpro_modal_closebtn" id="wptripadvisor_modal_closebtn" aria-label="<?php esc_attr_e( 'Close', 'wp-tripadvisor-review-slider' ); ?>">&times;</button>
<h2><?php esc_html_e( 'Edit Review', 'wp-tripadvisor-review-slider' ); ?></h2>
<div id="wprevpro_save_review_msg"></div>
<form name="newreviewform" id="newreviewform" action="?page=wp_tripadvisor-reviews" method="post">
	<table class="form-table ">
		<tbody>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Rating (1 - 5):', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td><div id="divtemplatestyles">
				<?php $tempdisable = 'disabled'; ?>
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating1-radio" value="1" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '1' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating1-radio">1</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating2-radio" value="2" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '2' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating2-radio">2</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating3-radio" value="3" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '3' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating3-radio">3</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating4-radio" value="4" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '4' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating4-radio">4</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating5-radio" value="5" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '5' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating5-radio">5</label>
					</div>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Title:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_title" type="text" name="wprevpro_nr_title" value="<?php echo esc_attr( isset( $currentreview->review_title ) ? $currentreview->review_title : '' ); ?>" readonly class="regular-text">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Text:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<textarea name="wprevpro_nr_text" id="wprevpro_nr_text" cols="50" rows="4" readonly><?php echo esc_textarea( isset( $currentreview->review_text ) ? $currentreview->review_text : '' ); ?></textarea>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Reviewer Name:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_name" type="text" name="wprevpro_nr_name" value="<?php echo esc_attr( isset( $currentreview->reviewer_name ) ? $currentreview->reviewer_name : '' ); ?>" readonly class="regular-text">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Source Name:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_pagename" type="text" name="wprevpro_nr_pagename" value="<?php echo esc_attr( isset( $currentreview->pagename ) ? $currentreview->pagename : '' ); ?>" readonly class="regular-text">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Reviewer Pic URL:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_avatar_url" type="text" name="wprevpro_nr_avatar_url" value="<?php echo esc_url( ! empty( $currentreview->userpic ) ? $currentreview->userpic : $default_avatar ); ?>" class="regular-text">
					<a id="upload_avatar_button" class="button"><?php esc_html_e( 'Upload', 'wp-tripadvisor-review-slider' ); ?></a>
					<br><p class="description">
					<?php esc_html_e( 'Avatar for the person who wrote the review. Click the following image to insert a generic avatar URL.', 'wp-tripadvisor-review-slider' ); ?>
					</p>
					<div class="avatar_images_list">
					<img src="<?php echo esc_url( $default_avatar ); ?>" alt="thumb" class="rlimg default_avatar_img">&nbsp;&nbsp;&nbsp;
					</div>
					</br>
					<img class="" height="100px" id="avatar_preview" src="<?php echo esc_url( ! empty( $currentreview->userpic ) ? $currentreview->userpic : $default_avatar ); ?>" alt="">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Date:', 'wp-tripadvisor-review-slider' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_date" type="text" name="wprevpro_nr_date" class="regular-text" value="<?php echo esc_attr( ! empty( $currentreview->created_time ) ? $currentreview->created_time : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ); ?>" required>
					<p class="description"><?php esc_html_e( 'Format: YYYY-MM-DD HH:MM:SS. Changing the date will not create duplicates on re-download (matched by reviewer name and review length).', 'wp-tripadvisor-review-slider' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php wp_nonce_field( 'wprevpro_save_review' ); ?>
	<input type="hidden" name="editrid" id="editrid" value="<?php echo esc_attr( isset( $currentreview->id ) ? $currentreview->id : '' ); ?>">
	<input type="hidden" name="editrtype" id="editrtype" value="<?php echo esc_attr( isset( $currentreview->type ) ? $currentreview->type : '' ); ?>">
	<input type="submit" name="wprevpro_submitreviewbtn" id="wprevpro_submitreviewbtn" class="button button-primary" value="<?php esc_attr_e( 'Save Review', 'wp-tripadvisor-review-slider' ); ?>">
	<a id="wptripadvisor_addnewreview_cancel" class="button button-secondary"><?php esc_html_e( 'Cancel', 'wp-tripadvisor-review-slider' ); ?></a>
</form>
</div>
</div>

<?php

// Remove all.
if ( isset( $_GET['opt'] ) && $_GET['opt'] === 'delall' ) {
	if ( $wptrip_action_nonce_ok() ) {
		$wpdb->query( "TRUNCATE TABLE `{$table_name}`" );
	}
}

// Pagination.
if ( isset( $_GET['pnum'] ) ) {
	$temppagenum = $_GET['pnum'];
} else {
	$temppagenum = '';
}
if ( $temppagenum === '' ) {
	$pagenum = 1;
} elseif ( is_numeric( $temppagenum ) ) {
	$pagenum = absint( $temppagenum );
} else {
	$pagenum = 1;
}

if ( ! isset( $_GET['sortdir'] ) ) {
	$_GET['sortdir'] = '';
}
if ( $_GET['sortdir'] === '' || $_GET['sortdir'] === 'DESC' ) {
	$sortdirection = '&sortdir=ASC';
} else {
	$sortdirection = '&sortdir=DESC';
}
$currenturl = remove_query_arg( array( 'sortdir', 'editrev', 'deleterev', 'hiderev', 'newvalue', '_wpnonce', 'opt', 'opt_type' ) );

if ( ! isset( $_GET['sortby'] ) ) {
	$_GET['sortby'] = '';
}
$allowed_keys = array( 'created_time_stamp', 'reviewer_name', 'rating', 'review_length', 'pagename', 'type' );
$checkorderby = sanitize_key( $_GET['sortby'] );

if ( in_array( $checkorderby, $allowed_keys, true ) && $_GET['sortby'] !== '' ) {
	$sorttable = $_GET['sortby'] . ' ';
} else {
	$sorttable = 'created_time_stamp ';
}
if ( $_GET['sortdir'] === 'ASC' || $_GET['sortdir'] === 'DESC' ) {
	$sortdir = $_GET['sortdir'];
} else {
	$sortdir = 'DESC';
}

$sorticoncolor = array_fill( 0, 11, '' );
if ( $sorttable === 'hide ' ) {
	$sorticoncolor[0] = 'text_green';
} elseif ( $sorttable === 'reviewer_name ' ) {
	$sorticoncolor[1] = 'text_green';
} elseif ( $sorttable === 'rating ' ) {
	$sorticoncolor[2] = 'text_green';
} elseif ( $sorttable === 'created_time_stamp ' ) {
	$sorticoncolor[3] = 'text_green';
} elseif ( $sorttable === 'review_length ' ) {
	$sorticoncolor[4] = 'text_green';
} elseif ( $sorttable === 'pagename ' ) {
	$sorticoncolor[5] = 'text_green';
} elseif ( $sorttable === 'type ' ) {
	$sorticoncolor[6] = 'text_green';
}

$html .= '
		<table class="wp-list-table widefat striped posts">
			<thead>
				<tr>
					<th scope="col" width="70px" class="manage-column">' . esc_html__( 'Edit', 'wp-tripadvisor-review-slider' ) . '</th>
					<th scope="col" width="50px" class="manage-column">' . esc_html__( 'Pic', 'wp-tripadvisor-review-slider' ) . '</th>
					<th scope="col" style="min-width:70px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'reviewer_name', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[1] . '" aria-hidden="true"></i> ' . esc_html__( 'Name', 'wp-tripadvisor-review-slider' ) . '</a></th>
					<th scope="col" width="85px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'rating', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[2] . '" aria-hidden="true"></i> ' . esc_html__( 'Rating', 'wp-tripadvisor-review-slider' ) . '</a></th>
					<th scope="col" class="manage-column">' . esc_html__( 'Review Text', 'wp-tripadvisor-review-slider' ) . '</th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'created_time_stamp', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[3] . '" aria-hidden="true"></i> ' . esc_html__( 'Date', 'wp-tripadvisor-review-slider' ) . '</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'pagename', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[5] . '" aria-hidden="true"></i> ' . esc_html__( 'Source Name', 'wp-tripadvisor-review-slider' ) . '</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'type', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[6] . '" aria-hidden="true"></i> ' . esc_html__( 'Type', 'wp-tripadvisor-review-slider' ) . '</a></th>
				</tr>
				</thead>
			<tbody id="review_list">';

$lowlimit      = ( $pagenum - 1 ) * $rowsperpage;
$tablelimit    = $lowlimit . ',' . $rowsperpage;
$reviewsrows   = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$table_name}
		WHERE id>%d
		ORDER BY {$sorttable} {$sortdir}
		LIMIT {$tablelimit}",
		0
	)
);
$reviewtotalcount = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
$totalpages       = (int) ceil( $reviewtotalcount / $rowsperpage );

if ( $reviewtotalcount > 0 ) {
	foreach ( $reviewsrows as $reviewsrow ) {
		$editicon   = '<i class="dashicons dashicons-admin-tools editrev" aria-hidden="true"></i>';
		$deleteicon = '<i class="dashicons dashicons-trash deleterev" aria-hidden="true"></i>';

		if ( $reviewsrow->hide === 'yes' ) {
			$hideicon      = '<i class="dashicons dashicons-hidden hiderev" aria-hidden="true"></i>';
			$hiddentrclass = 'hiddenrow';
		} else {
			$hideicon      = '<i class="dashicons dashicons-visibility hiderev" aria-hidden="true"></i>';
			$hiddentrclass = '';
		}

		$userpicsrc = $reviewsrow->userpic ? $reviewsrow->userpic : $default_avatar;
		$userpic    = '<img style="-webkit-user-select: none;width: 50px;" src="' . esc_url( $userpicsrc ) . '" alt="">';

		// Data for the JS-driven edit popup, so clicking "edit" never needs a page
		// reload (and never loses the user's scroll position in the list).
		$editdata = ' data-rid="' . esc_attr( $reviewsrow->id ) . '"'
			. ' data-rating="' . esc_attr( $reviewsrow->rating ) . '"'
			. ' data-title="' . esc_attr( $reviewsrow->review_title ) . '"'
			. ' data-text="' . esc_attr( $reviewsrow->review_text ) . '"'
			. ' data-name="' . esc_attr( $reviewsrow->reviewer_name ) . '"'
			. ' data-pagename="' . esc_attr( $reviewsrow->pagename ) . '"'
			. ' data-userpic="' . esc_attr( $userpicsrc ) . '"'
			. ' data-date="' . esc_attr( $reviewsrow->created_time ) . '"'
			. ' data-type="' . esc_attr( $reviewsrow->type ) . '"';

		$mediahtml = '';
		if ( $reviewsrow->mediaurlsarrayjson !== '' ) {
			$imagesarray = json_decode( $reviewsrow->mediaurlsarrayjson, true );
			if ( is_array( $imagesarray ) ) {
				$mediahtml = '<div class="mediaimgsdiv">';
				foreach ( $imagesarray as $imgurl ) {
					$mediahtml .= '<a href="' . esc_url( $imgurl ) . '" data-lity target="_blank"><img src="' . esc_url( $imgurl ) . '" height="50" alt=""></a> ';
				}
				$mediahtml .= '</div>';
			}
		}

		$revtitle = '';
		if ( $reviewsrow->review_title !== '' ) {
			$revtitle = '<b>' . esc_html( $reviewsrow->review_title ) . '</b></br>';
		}

		$typecolumn = esc_html( $reviewsrow->type );
		if ( ! empty( $reviewsrow->from_url ) ) {
			$typecolumn = '<a href="' . esc_url( $reviewsrow->from_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $reviewsrow->type ) . '</a>';
		}

		$sourcename = $reviewsrow->pagename !== '' ? $reviewsrow->pagename : '—';

		$html .= '<tr id="' . esc_attr( $reviewsrow->id ) . '" class="' . esc_attr( $hiddentrclass ) . '">
						<th scope="col" class="manage-column"><span title="edit" role="button" tabindex="0" class="wprevpro_editrev_link wprevpro_iconbtn"' . $editdata . '>' . $editicon . '</span><br><span title="delete" role="button" tabindex="0" class="wprevpro_deleterev_link wprevpro_iconbtn" data-rid="' . esc_attr( $reviewsrow->id ) . '">' . $deleteicon . '</span><br>
						<span title="hide/unhide" role="button" tabindex="0" class="wprevpro_hiderev_link wprevpro_iconbtn" data-rid="' . esc_attr( $reviewsrow->id ) . '">' . $hideicon . '</span>
						</th>
						<th scope="col" class="manage-column wprevpro_pic_cell">' . $userpic . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->reviewer_name ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->rating ) . '</th>
						<th scope="col" class="manage-column">' . $revtitle . '<span title="' . esc_attr( $reviewsrow->review_text ) . '">' . esc_html( $reviewsrow->review_text ) . '</span>' . $mediahtml . '</th>
						<th scope="col" class="manage-column wprevpro_date_cell">' . esc_html( $reviewsrow->created_time ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $sourcename ) . '</th>
						<th scope="col" class="manage-column">' . $typecolumn . '</th>
					</tr>';
	}
} else {
	$html .= '<tr>
						<th colspan="8" scope="col" class="manage-column">' . __( 'No reviews found. Please visit the <a href="?page=wp_tripadvisor-get_tripadvisor">Get TripAdvisor Reviews</a> page to retrieve reviews.', 'wp-tripadvisor-review-slider' ) . '<br><br>' . __( 'Want to put this on autopilot? <a href="https://wpreviewslider.com/" target="_blank">Upgrade to Pro</a> to automatically sync new reviews, pull from 100+ sites, and collect reviews on your site. Use code <strong>WPPRO15</strong> for 15% off.', 'wp-tripadvisor-review-slider' ) . '</th>
					</tr>';
}

$html .= '</tbody>
		</table>';

$html .= '<div id="wptripadvisor_review_list_pagination_bar">';
$currenturl = remove_query_arg( 'pnum' );
for ( $x = 1; $x <= $totalpages; $x++ ) {
	$blue_grey = ( $x == $pagenum ) ? 'blue_grey' : '';
	$html     .= '<a href="' . esc_url( add_query_arg( 'pnum', $x, $currenturl ) ) . '" class="button ' . $blue_grey . '">' . $x . '</a>';
}
$html .= '</div>';
$html .= '</div>';

echo $html;
?>
</div></div></div>

	<div id="popup_review_list" class="popup-wrapper wptripadvisor_hide">
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
