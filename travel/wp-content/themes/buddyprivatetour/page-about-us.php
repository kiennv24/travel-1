<?php
/**
 * Template Name: About Us Page
 */
get_header();
?>

<div class="section-padding" style="background: #f8fafc; min-height: 70vh; padding: 2rem 0 5rem 0;">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current" data-i18n="nav_about">About Us</span>
            </nav>
        </div>

        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 2.5rem; text-transform: uppercase;" data-i18n="nav_about">ABOUT US</h1>
        <div style="background: #fff; padding: 3rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); max-width: 960px; margin: 0 auto;">
            <div class="about-content" style="font-size: 1.05rem; line-height: 1.85; color: #334155;">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
