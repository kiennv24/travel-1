<?php
/**
 * Pro feature teaser: Submission Forms.
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
			<h2><?php esc_html_e( 'Submission Forms - Pro Feature', 'wp-tripadvisor-review-slider' ); ?></h2>
			<p><?php esc_html_e( 'Submission Forms let you create custom front-end forms for collecting reviews and testimonials directly from your website visitors. Customize form fields, styling, validation, and submission behavior. Forms can be displayed via shortcodes or widgets, and submissions can send email notifications.', 'wp-tripadvisor-review-slider' ); ?></p>
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/form_example.png' ); ?>" alt="<?php esc_attr_e( 'Forms', 'wp-tripadvisor-review-slider' ); ?>" style="max-width: 50%; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
			<p>
				<a href="https://wpreviewslider.com/" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro Today!', 'wp-tripadvisor-review-slider' ); ?></a>
				<span style="margin-left:10px;"><?php esc_html_e( 'Use code', 'wp-tripadvisor-review-slider' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-tripadvisor-review-slider' ); ?></span>
			</p>
		</div>
	</div>
</div>
