<?php

/**
 * Template Style 6 — card layout with avatar header row.
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/public/partials
 */

$imgs_url = esc_url( plugins_url( 'imgs/', __FILE__ ) );
require_once 'template_class.php';
$templateclass = new WP_Trip_Template_Functions();

for ( $x = 0; $x < count( $rowarray ); $x++ ) {
	if ( $currentform[0]->template_type === 'widget' ) {
		$iswidget = true;
		?>
		<div class="wprevpro_t6_outer_div_widget w3_wprs-row wprevprodiv">
		<?php
	} else {
		$iswidget = false;
		?>
		<div class="wprevpro_t6_outer_div w3_wprs-row wprevprodiv">
		<?php
	}

	foreach ( $rowarray[ $x ] as $review ) {
		$typelower          = strtolower( $review->type );
		$tempreviewername   = $templateclass->wprevpro_get_reviewername( $review, $template_misc_array );

		if ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'mystery' ) {
			$userpic = $imgs_url . 'tripadvisor_mystery_man.png';
		} elseif ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'init' ) {
			$userpic = $templateclass->wprev_get_initials_avatar_url( $tempreviewername, 100 );
		} else {
			$userpic = $review->userpic;
		}

		$tempuserpicnone = '';
		if ( isset( $template_misc_array['avataropt'] ) && $template_misc_array['avataropt'] === 'hide' ) {
			$userpichtml     = '';
			$tempuserpicnone = 'style="display:none;"';
		} elseif ( $userpic === '' ) {
			$userpichtml     = '';
			$tempuserpicnone = 'style="display:none;"';
		} else {
			$userpichtml = '<img src="' . $templateclass->wprev_esc_avatar_src( $userpic ) . '" alt="' . esc_attr( wp_unslash( $review->reviewer_name ) ) . ' Avatar" class="wpproslider_t6_IMG_2 wprevpro_avatarimg" loading="lazy" />';
		}

		if ( ! isset( $template_misc_array['showicon'] ) ) {
			$template_misc_array['showicon'] = '';
		}

		if ( isset( $review->from_url ) && ! empty( $review->from_url ) ) {
			$burl = $review->from_url;
		} else {
			$options = get_option( 'wptripadvisor_tripadvisor_settings' );
			$burl    = isset( $options['tripadvisor_business_url'] ) ? $options['tripadvisor_business_url'] : 'https://www.tripadvisor.com';
			if ( $burl === '' ) {
				$burl = 'https://www.tripadvisor.com';
			}
		}

		$starfile  = 'tripadvisor_stars_' . $review->rating . '.png';
		$starhtml  = '<img src="' . esc_url( $imgs_url . $starfile ) . '" alt="' . esc_attr( $review->rating ) . ' star rating" class="wptripadvisor_t6_star_img_file">';
		$site_logo = '';

		if ( $template_misc_array['showicon'] === 'no' ) {
			$site_logo = '';
		} elseif ( $template_misc_array['showicon'] === 'yes' ) {
			$site_logo = '<img src="' . esc_url( $imgs_url . 'tripadvisor_small_icon.png' ) . '" alt="TripAdvisor Logo" class="wprevpro_t6_site_logo siteicon sitetype_TripAdvisor">';
		} else {
			// Default / linked icon (empty showicon = current TripAdvisor behavior).
			$site_logo = '<a href="' . esc_url( $burl ) . '" target="_blank" rel="nofollow"><img src="' . esc_url( $imgs_url . 'tripadvisor_outline.png' ) . '" alt="TripAdvisor logo" class="wprevpro_t6_site_logo siteicon sitetype_TripAdvisor"></a>';
		}

		$reviewtext = '';
		if ( $review->review_text !== '' ) {
			$reviewtext = nl2br( $review->review_text );
			$morelink   = '<a href="' . esc_url( $burl ) . '" class="ta_morelink" target="_blank" rel="nofollow">' . __( '..More', 'wp-tripadvisor-review-slider' ) . '</a>';
			$reviewtext = str_replace( '..More', $morelink, $reviewtext );
		}

		if ( ! isset( $currentform[0]->read_more_text ) ) {
			$currentform[0]->read_more_text = '';
		}
		if ( $currentform[0]->read_more_text === '' ) {
			$currentform[0]->read_more_text = 'read more';
		}
		if ( $currentform[0]->read_more !== 'no' ) {
			$readmorenum = 30;
			if ( isset( $template_misc_array['read_more_num'] ) && $template_misc_array['read_more_num'] !== '' ) {
				$readmorenum = intval( $template_misc_array['read_more_num'] );
			}
			$pieces     = explode( ' ', $reviewtext );
			$countwords = count( $pieces );
			if ( $countwords > $readmorenum ) {
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

		if ( isset( $template_misc_array['showdate'] ) && $template_misc_array['showdate'] === 'no' ) {
			$datehtml = '';
		} else {
			$date_format = get_option( 'date_format' );
			if ( isset( $date_format ) && $date_format !== '' ) {
				$datehtml = date_i18n( $date_format, $review->created_time_stamp );
			} else {
				$datehtml = date( 'n/d/Y', $review->created_time_stamp );
			}
		}

		$verifiedhtml = '';
		if ( isset( $template_misc_array['verified'] ) && $template_misc_array['verified'] === 'yes1' ) {
			$verifiedhtml = '<span class="verifiedloc1 wprevpro_verified_svg wprevtooltip" data-wprevtooltip="Verified on ' . esc_attr( $review->type ) . '"><span class="svgicons svg-wprsp-verified"></span></span>';
		}

		$media = $templateclass->wprevpro_get_media( $review, $template_misc_array );

		$widget_class = ( $currentform[0]->template_type === 'widget' ) ? ' marginb10' : '';
		?>
		<div class="wprevpro_t6_DIV_1<?php echo esc_attr( $widget_class ); ?> w3_wprs-col l<?php echo esc_attr( $perrow ); ?> outerrevdiv">
			<div class="wpproslider_t6_DIV_1a">
				<div class="indrevdiv wpproslider_t6_DIV_2 wprev_preview_bg1_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?> wprev_preview_bradius_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>">
					<div class="wpproslider_t6_DIV_2_top">
						<div class="wpproslider_t6_DIV_3L" <?php echo $tempuserpicnone; ?>><?php echo $userpichtml; ?></div>
						<div class="wpproslider_t6_DIV_3">
							<div class="t6displayname wpproslider_t6_STRONG_5 wprev_preview_tcolor2_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>"><?php echo esc_html( wp_unslash( $tempreviewername ) ); ?></div>
							<div class="wpproslider_t6_star_DIV"><span class="wprevpro_star_imgs_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>"><?php echo $starhtml; ?><?php echo $verifiedhtml; ?></span></div>
							<div class="wpproslider_t6_SPAN_6 wprev_preview_tcolor2_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>"><span class="wprev_showdate_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>"><?php echo esc_html( $datehtml ); ?></span></div>
						</div>
					</div>
					<div class="wpproslider_t6_DIV_4">
						<div class="indrevtxt wpproslider_t6_P_4 wprev_preview_tcolor1_T<?php echo esc_attr( $currentform[0]->style ); ?><?php echo $iswidget ? '_widget' : ''; ?>"><?php echo wp_kses_post( wp_unslash( $reviewtext ) ); ?></div>
						<?php echo $media; ?>
					</div>
					<div class="wpproslider_t6_DIV_3_logo"><?php echo $site_logo; ?></div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
