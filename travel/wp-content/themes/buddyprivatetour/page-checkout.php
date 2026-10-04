<?php
/**
 * Template Name: Checkout Page
 * 100% Native WooCommerce & VietQR Gateway Interface
 */

get_header();
?>

<!-- Breadcrumb Navigation -->
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
            <i class="fa-solid fa-house"></i>Home
        </a>
        <span class="crumb-separator">/</span>
        <?php if ( function_exists('is_order_received_page') && is_order_received_page() ) : ?>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="crumb-link">Checkout</a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current">Order Confirmation</span>
        <?php else : ?>
            <span class="crumb-current">Checkout</span>
        <?php endif; ?>
    </nav>
</div>

<!-- Main Native WooCommerce & VietQR Checkout Container -->
<div class="section-padding checkout-section" style="background: #f8fafc; padding: 2rem 0 4rem 0;">
    <div class="container">
        <div class="woocommerce checkout-native-wrapper" style="background: #ffffff; padding: 2.5rem 3rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04); max-width: 1050px; margin: 0 auto;">
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
