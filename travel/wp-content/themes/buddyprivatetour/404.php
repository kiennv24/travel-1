<?php
/**
 * The template for displaying 404 pages (Not Found)
 */

get_header();
?>
<div class="container section-padding" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="background:#fff; padding:3.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); text-align:center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3.5rem; color: #f97316; margin-bottom: 1.5rem;"></i>
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem;" data-i18n="error_404_title">Page Not Found (404)</h2>
        <p style="color:#64748b; margin-bottom: 2rem; line-height: 1.6;" data-i18n="error_404_desc">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="tour-card-cta-btn" style="padding: 0.8rem 2rem; font-weight: 700; display: inline-flex;"><i class="fa-solid fa-house" style="margin-right: 0.4rem;"></i> <span data-i18n="nav_home">Back to Homepage</span></a>
    </div>
</div>
<?php
get_footer();
