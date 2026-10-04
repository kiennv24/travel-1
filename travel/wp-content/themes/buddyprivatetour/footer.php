<!-- Contact Information section -->
<footer id="contact" class="contact-info-section">
    <div class="container">
        <h3 class="contact-info-title">CONTACT INFOMATION</h3>
        <h4 class="contact-info-brand">BUDDY PRIVATE TOUR</h4>
        
        <div class="contact-info-details">
            <p>Address: 24/9 Nguyen Luong Bang street, Hoa Khanh Bac, Lien Chieu, Da Nang.</p>
            <p>Zalo: +84567599998 – Hotline: +84567599998</p>
            <p>Email: buddyprivatetour@gmail.com</p>
        </div>

        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/dathongbao.png'); ?>" class="mit-badge" alt="Đã Thông Báo Bộ Công Thương">
    </div>

    <!-- Bottom Bar and Social Media Links -->
    <div class="bottom-bar">
        <div class="container">
            <div class="bottom-bar-socials">
                <a href="mailto:buddyprivatetour@gmail.com" class="social-icon-link" title="Email"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-email.png'); ?>" alt="Email"></a>
                <a href="#" class="social-icon-link" title="TikTok"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-tiktok.png'); ?>" alt="TikTok"></a>
                <a href="#" class="social-icon-link" title="YouTube"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-youtube.png'); ?>" alt="YouTube"></a>
                <a href="#" class="social-icon-link" title="Facebook"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-facebook.png'); ?>" alt="Facebook"></a>
                <a href="#" class="social-icon-link" title="Viber"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-viber.png'); ?>" alt="Viber"></a>
                <a href="#" class="social-icon-link" title="WeChat"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-wechat.png'); ?>" alt="WeChat"></a>
                <a href="https://zalo.me/84567599998" target="_blank" class="social-icon-link" title="Zalo"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-zalo.png'); ?>" alt="Zalo"></a>
                <a href="#" class="social-icon-link" title="TripAdvisor"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icon-tripadvisor.png'); ?>" alt="TripAdvisor"></a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating actions in bottom-left corner exactly like screenshots -->
<div class="floating-left-actions">
    <a href="tel:+84567599998" class="bpt-hotline-pill" title="Call Hotline: +84567599998">
        <div class="bpt-hotline-circle">
            <div class="bpt-hotline-red">
                <i class="fa-solid fa-phone"></i>
            </div>
        </div>
        <span class="bpt-hotline-number">+84567599998</span>
    </a>
    <a href="https://zalo.me/84567599998" target="_blank" rel="noopener noreferrer" class="bpt-zalo-bubble" title="Chat Zalo / WhatsApp: +84567599998">
        <div class="bpt-zalo-inner">
            <span class="bpt-zalo-text">Zalo</span>
        </div>
    </a>
</div>

<!-- Floating Back to Top Button on bottom right -->
<a href="#" class="floating-back-to-top" title="Back to Top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
    <i class="fa-solid fa-arrow-up"></i>
</a>

<?php wp_footer(); ?>
</body>
</html>
