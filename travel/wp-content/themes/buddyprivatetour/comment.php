<?php
/**
 * Template Name: Comment
 */
get_header();
?>

<div class="section-padding" style="background: #f8fafc; min-height: 60vh;">
    <div class="container">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin-bottom: 2.5rem; text-transform: uppercase;">ABOUT US</h1>
        <div style="background: #fff; padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto;">
           
            <div class="about-content" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
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
