<?php
/**
 * The template for displaying 404 pages (Not Found)
 */

get_header();
?>
<div class="container section-padding" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="background:#fff; padding:3.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); text-align:center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; width: 100%;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3.5rem; color: var(--secondary-color); margin-bottom: 1.5rem;"></i>
        <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.75rem;">Không tìm thấy trang yêu cầu (404)</h2>
        <p style="color:var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">Trang này chưa được tạo trong Cơ sở dữ liệu hoặc đường dẫn không chính xác.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary" style="padding: 0.8rem 2rem; font-weight: 700;"><i class="fa-solid fa-house"></i> Quay về Trang chủ</a>
    </div>
</div>
<?php
get_footer();
