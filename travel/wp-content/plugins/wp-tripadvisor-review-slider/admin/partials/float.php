<?php
/**
 * Pro feature teaser: Floats.
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="">
	<h1></h1>
	<div class="wrap" id="wp_rev_maindiv">
		<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>">
		<?php include 'tabmenu.php'; ?>

		<div class="wprevpro_margin10 wpfbr-pro-feature-panel">
			<h2><?php esc_html_e( 'Floats - Pro Feature', 'wp-tripadvisor-review-slider' ); ?></h2>
			<p><?php esc_html_e( 'Floats let you create floating badges or review sliders that stick to the bottom or top of your pages. Display review badges, review sliders, or pop-in/pop-out review windows when visitors interact with your site. Configure which pages they appear on, add animations, auto-close after a set time, and even slide out or pop open reviews when a Float is clicked.', 'wp-tripadvisor-review-slider' ); ?></p>
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/float_sample.png' ); ?>" alt="<?php esc_attr_e( 'Floats', 'wp-tripadvisor-review-slider' ); ?>" style="max-width: 100%; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
			<p>
				<a href="https://wpreviewslider.com/" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro!', 'wp-tripadvisor-review-slider' ); ?></a>
				<span style="margin-left:10px;"><?php esc_html_e( 'Use code', 'wp-tripadvisor-review-slider' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-tripadvisor-review-slider' ); ?></span>
			</p>
		</div>
	</div>
</div>
