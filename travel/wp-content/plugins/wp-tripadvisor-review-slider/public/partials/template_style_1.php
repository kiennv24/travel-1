<?php

/**
 * Provide a public-facing view for the plugin
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/public/partials
 */
$plugin_dir = WP_PLUGIN_DIR;
$imgs_url   = esc_url( plugins_url( 'imgs/', __FILE__ ) );

require_once 'template_class.php';
$templateclass = new WP_Trip_Template_Functions();

if ( ! is_array( $template_misc_array ) ) {
	$template_misc_array = array();
}

for ( $x = 0; $x < count( $rowarray ); $x++ ) {
	if ( $currentform[0]->template_type == 'widget' ) {
		?>
		<div class="wptripadvisor_t1_outer_div_widget w3_wprs-row-padding-small">
		<?php
	} else {
		?>
		<div class="wptripadvisor_t1_outer_div w3_wprs-row-padding">
		<?php
	}

	foreach ( $rowarray[ $x ] as $review ) {
		$tempreviewername = $templateclass->wprevpro_get_reviewername( $review, $template_misc_array );

		if ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'mystery' ) {
			$userpic = $imgs_url . 'tripadvisor_mystery_man.png';
		} elseif ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'init' ) {
			$userpic = $templateclass->wprev_get_initials_avatar_url( $tempreviewername, 100 );
		} elseif ( $review->type == 'Facebook' ) {
			$userpic = 'https://graph.facebook.com/' . $review->reviewer_id . '/picture?width=60&height=60 ';
		} else {
			$userpic = $review->userpic;
		}

		if ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'hide' ) {
			$userpichtml = '';
		} elseif ( $userpic == '' || ! $userpic ) {
			$userpichtml = '';
		} else {
			$userpichtml = '<img src="' . $templateclass->wprev_esc_avatar_src( $userpic ) . '" alt="avatar thumb" class="wptripadvisor_t1_IMG_4" loading="lazy"/>';
		}

		if ( isset( $review->from_url ) && ! empty( $review->from_url ) ) {
			$burl = $review->from_url;
		} else {
			$options = get_option( 'wptripadvisor_tripadvisor_settings' );
			$burl    = isset( $options['tripadvisor_business_url'] ) ? $options['tripadvisor_business_url'] : '';
			if ( $burl == '' ) {
				$burl = 'https://www.tripadvisor.com';
			}
		}

		if ( $review->type == 'TripAdvisor' ) {
			$starfile = 'tripadvisor_stars_' . $review->rating . '.png';
			$logo     = '<a href="' . esc_url( $burl ) . '" target="_blank" rel="nofollow"><img src="' . $imgs_url . 'tripadvisor_outline.png" alt="tripadvisor logo" class="wptripadvisor_t1_tripadvisor_logo"></a>';
		} elseif ( $review->type == 'Facebook' && isset( $currentform[0]->facebook_icon ) && $currentform[0]->facebook_icon == 'yes' ) {
			$starfile = 'stars_' . $review->rating . '_yellow.png';
			$burl     = 'https://www.facebook.com/pg/' . $review->pageid . '/reviews/';
			$logo     = '<a href="' . esc_url( $burl ) . '" target="_blank" rel="nofollow"><img src="' . $imgs_url . 'fb_logo.png" alt="facebook logo" class="wptripadvisor_t1_tripadvisor_logo"></a>';
		} else {
			$starfile = 'tripadvisor_stars_' . $review->rating . '.png';
			$logo     = '<a href="' . esc_url( $burl ) . '" target="_blank" rel="nofollow"><img src="' . $imgs_url . 'tripadvisor_outline.png" alt="tripadvisor logo" class="wptripadvisor_t1_tripadvisor_logo"></a>';
		}

		if ( ! isset( $template_misc_array['showicon'] ) ) {
			$template_misc_array['showicon'] = '';
		}
		if ( $template_misc_array['showicon'] === 'no' ) {
			$logo = '';
		} elseif ( $template_misc_array['showicon'] === 'yes' ) {
			$logo = '<img src="' . $imgs_url . 'tripadvisor_small_icon.png" alt="tripadvisor logo" class="wptripadvisor_t1_tripadvisor_logo siteicon">';
		}

		$reviewtext = '';
		if ( $review->review_text != '' ) {
			$reviewtext = nl2br( $review->review_text );
			$morelink   = '<a href="' . esc_url( $burl ) . '" class="ta_morelink" target="_blank" rel="nofollow">' . __( '..More', 'wp-tripadvisor-review-slider' ) . '</a>';
			$reviewtext = str_replace( '..More', $morelink, $reviewtext );
		}

		if ( ! isset( $currentform[0]->read_more_text ) ) {
			$currentform[0]->read_more_text = '';
		}
		if ( $currentform[0]->read_more_text == '' ) {
			$currentform[0]->read_more_text = 'read more';
		}
		if ( $currentform[0]->read_more != 'no' ) {
			$readmorenum = 30;
			if ( isset( $template_misc_array['read_more_num'] ) && $template_misc_array['read_more_num'] !== '' ) {
				$readmorenum = intval( $template_misc_array['read_more_num'] );
			}
			$countwords = str_word_count( $reviewtext );

			if ( $countwords > $readmorenum ) {
				$pieces     = explode( ' ', $reviewtext );
				$part1      = array_slice( $pieces, 0, $readmorenum );
				$part2      = array_slice( $pieces, $readmorenum );
				$reviewtext = implode( ' ', $part1 ) . "<a class='wprs_rd_more'>... " . esc_html( $currentform[0]->read_more_text ) . "</a><span class='wprs_rd_more_text' style='display:none;'> " . implode( ' ', $part2 ) . '</span>';
			}
		}

		if ( $currentform[0]->display_num > 0 ) {
			$perrow = 12 / $currentform[0]->display_num;
		} else {
			$perrow = 4;
		}

		if ( $review->created_time_stamp == '' || $review->created_time_stamp < 1 ) {
			$temptime                    = $review->created_time;
			$review->created_time_stamp = strtotime( $temptime );
		}

		$date_format = get_option( 'date_format' );
		if ( isset( $date_format ) && $date_format != '' ) {
			$tempdate = date_i18n( $date_format, $review->created_time_stamp );
		} else {
			$tempdate = date( 'n/d/Y', $review->created_time_stamp );
		}

		$titlehtml = '';
		if ( $review->review_title != '' ) {
			$titlehtml = '<span class="wprevrevtitle">' . esc_html( $review->review_title ) . '</span>&nbsp;-&nbsp;';
		}
		$verifiedhtml = '&nbsp;&nbsp;';
		if ( isset( $template_misc_array['verified'] ) && $template_misc_array['verified'] == 'yes1' ) {
			$verifiedhtml = '<span class="verifiedloc1 wprevpro_verified_svg wprevtooltip" data-wprevtooltip="Verified on ' . esc_attr( $review->type ) . '"><span class="svgicons svg-wprsp-verified"></span></span>';
		}

		$media = $templateclass->wprevpro_get_media( $review, $template_misc_array );

		$star_span_class = 'wptripadvisor_star_imgs_T' . $currentform[0]->style;
		if ( $iswidget ) {
			$star_span_class .= '_widget';
		}
		// Also support Google-style class used by build_template_misc_style for showstars.
		$star_span_class .= ' wprevpro_star_imgs_T' . $currentform[0]->style;
		if ( $iswidget ) {
			$star_span_class .= '_widget';
		}
		?>
		<div class="wptripadvisor_t1_DIV_1<?php if ( $currentform[0]->template_type == 'widget' ) { echo ' marginb10'; } ?> w3_wprs-col l<?php echo esc_attr( $perrow ); ?> outerrevdiv">
			<div class="indrevdiv wptripadvisor_t1_DIV_2 wprev_preview_bg1_T<?php echo esc_attr( $currentform[0]->style ); ?><?php if ( $iswidget ) { echo '_widget'; } ?> wprev_preview_bradius_T<?php echo esc_attr( $currentform[0]->style ); ?><?php if ( $iswidget ) { echo '_widget'; } ?>">
				<p class="wptripadvisor_t1_P_3 wprev_preview_tcolor1_T<?php echo esc_attr( $currentform[0]->style ); ?><?php if ( $iswidget ) { echo '_widget'; } ?>">
					<span class="<?php echo esc_attr( $star_span_class ); ?>"><img src="<?php echo esc_url( $imgs_url . $starfile ); ?>" alt="star rating" class="wptripadvisor_t1_star_img_file"><?php echo $verifiedhtml; ?></span><?php echo $titlehtml; ?><?php echo wp_kses_post( stripslashes( $reviewtext ) ); ?>
				</p>
				<?php echo $media; ?>
				<?php echo $logo; ?>
			</div><span class="wptripadvisor_t1_A_8"><?php echo $userpichtml; ?></span> <span class="wptripadvisor_t1_SPAN_5 wprev_preview_tcolor2_T<?php echo esc_attr( $currentform[0]->style ); ?><?php if ( $iswidget ) { echo '_widget'; } ?>"><?php echo esc_html( $tempreviewername ); ?><br/><span class="wprev_showdate_T<?php echo esc_attr( $currentform[0]->style ); ?><?php if ( $iswidget ) { echo '_widget'; } ?>"><?php echo esc_html( $tempdate ); ?></span> </span>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
