<?php
/**
 * The template for displaying all pages by default
 */

get_header();
?>

<div class="default-page-container" style="background: #f8fafc; min-height: 70vh; padding: 3rem 0;">
    <div class="container">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: #fff; padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <?php 
                $is_woo_special = false;
                if (function_exists('is_cart') && is_cart()) $is_woo_special = true;
                if (function_exists('is_checkout') && is_checkout()) $is_woo_special = true;
                if (function_exists('is_account_page') && is_account_page()) $is_woo_special = true;
                if (isset($_GET['order-received']) || (isset($_GET['pagename']) && $_GET['pagename'] === 'checkout')) $is_woo_special = true;

                if ( ! is_front_page() && ! $is_woo_special ) : 
                ?>
                    <header class="entry-header" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                        <h1 class="entry-title" style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin: 0; text-transform: uppercase;"><?php the_title(); ?></h1>
                    </header>
                <?php endif; ?>

                <div class="entry-content" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
                    <?php
                    the_content();
                    ?>
                </div>
            </article>
            <?php
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();
