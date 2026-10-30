<?php
$urltrimmedtab = remove_query_arg( array(
	'page',
	'deleterev',
	'editrev',
	'hiderev',
	'newvalue',
	'opt_type',
	'_wpnonce',
	'taction',
	'tid',
	'sortby',
	'sortdir',
	'opt',
	'settings-updated',
	'pnum',
) );

$urlreviewlist    = esc_url( add_query_arg( 'page', 'wp_tripadvisor-reviews', $urltrimmedtab ) );
$urltemplateposts = esc_url( add_query_arg( 'page', 'wp_tripadvisor-templates_posts', $urltrimmedtab ) );
$urlgetpro        = esc_url( add_query_arg( 'page', 'wp_tripadvisor-get_tripadvisor', $urltrimmedtab ) );
$urlwelcome       = esc_url( add_query_arg( 'page', 'wp_tripadvisor-welcome', $urltrimmedtab ) );
$urlanalytics     = esc_url( add_query_arg( 'page', 'wp_tripadvisor-analytics', $urltrimmedtab ) );
$urlbadges        = esc_url( add_query_arg( 'page', 'wp_tripadvisor-badges', $urltrimmedtab ) );
$urlforms         = esc_url( add_query_arg( 'page', 'wp_tripadvisor-forms', $urltrimmedtab ) );
$urlfloat         = esc_url( add_query_arg( 'page', 'wp_tripadvisor-float', $urltrimmedtab ) );
$urlai            = esc_url( add_query_arg( 'page', 'wp_tripadvisor-ai_analysis', $urltrimmedtab ) );
$probadge         = ' <span style="background: #ff6b35; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-left: 4px;">PRO</span>';
$current_page     = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>
	<div class="w3-bar w3-border w3-white">
	<a href="<?php echo $urlwelcome; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-welcome' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-home"></i> <?php _e( 'Welcome', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="<?php echo $urlgetpro; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-get_tripadvisor' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-search"></i> <?php _e( 'Get TripAdvisor Reviews', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="<?php echo $urlreviewlist; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-reviews' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-list"></i> <?php _e( 'Review List', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="<?php echo $urltemplateposts; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-templates_posts' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-commenting-o"></i> <?php _e( 'Templates', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="<?php echo $urlanalytics; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-analytics' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-bar-chart"></i> <?php _e( 'Analytics', 'wp-tripadvisor-review-slider' ); ?></a>
	<a href="<?php echo $urlbadges; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-badges' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-certificate"></i> <?php _e( 'Badges', 'wp-tripadvisor-review-slider' ); ?><?php echo $probadge; ?></a>
	<a href="<?php echo $urlforms; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-forms' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-wpforms"></i> <?php _e( 'Forms', 'wp-tripadvisor-review-slider' ); ?><?php echo $probadge; ?></a>
	<a href="<?php echo $urlfloat; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-float' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-arrows-alt"></i> <?php _e( 'Floats', 'wp-tripadvisor-review-slider' ); ?><?php echo $probadge; ?></a>
	<a href="<?php echo $urlai; ?>" class="w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-ai_analysis' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-cogs"></i> <?php _e( 'AI Analysis', 'wp-tripadvisor-review-slider' ); ?><?php echo $probadge; ?></a>
	<a href="https://wpreviewslider.com/" target="_blank" class="goprohbtntrip w3-bar-item w3-button <?php if ( $current_page === 'wp_tripadvisor-get_pro' ) { echo 'w3-greentrip'; } ?>"><i class="fa fa-external-link-square" aria-hidden="true"></i> <?php _e( 'Get Pro Version!', 'wp-tripadvisor-review-slider' ); ?></a>

	</div>
