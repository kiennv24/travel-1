<?php
/**
 * Template Name: Checkout Page
 * Buddy Private Tour - Luxury Checkout & VietQR Confirmation
 */

get_header();
?>

<!-- Breadcrumb Navigation -->
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
            <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
        </a>
        <span class="crumb-separator">/</span>
        <?php if ( function_exists('is_order_received_page') && is_order_received_page() ) : ?>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="crumb-link"><span data-i18n="crumb_checkout">Checkout</span></a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current" data-i18n="crumb_order_received">Ticket Confirmation & VietQR</span>
        <?php else : ?>
            <span class="crumb-current" data-i18n="crumb_tour_payment">Tour Order Checkout</span>
        <?php endif; ?>
    </nav>
</div>

<!-- Main Checkout Container -->
<div class="section-padding checkout-section">
    <div class="container">
        <div class="woocommerce checkout-native-wrapper">
            <?php
            if ( function_exists('is_order_received_page') && is_order_received_page() ) {
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
            } else {
                echo do_shortcode('[woocommerce_checkout]');
            }
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
