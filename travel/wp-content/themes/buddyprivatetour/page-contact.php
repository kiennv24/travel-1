<?php
/**
 * Template Name: Contact Page
 */
get_header();
?>

<div class="section-padding contact-page-wrapper">
    <div class="container contact-container">
        <div class="contact-grid">
            <!-- Contact Form (Left Column) -->
            <div>
                <h1 class="contact-heading">CONTACT</h1>
                
                <form action="#" method="POST" onsubmit="alert('Cảm ơn bạn! Thông tin liên hệ đã được gửi đi.'); return false;">
                    <div class="contact-form-group">
                        <label for="c_name" class="contact-form-label">Your Name:</label>
                        <input type="text" id="c_name" required class="contact-input">
                    </div>
                    <div class="contact-form-group">
                        <label for="c_phone" class="contact-form-label">Your Phone:</label>
                        <input type="tel" id="c_phone" required class="contact-input">
                    </div>
                    <div class="contact-form-group" style="margin-bottom: 1.5rem;">
                        <label for="c_msg" class="contact-form-label">Your Message:</label>
                        <textarea id="c_msg" rows="5" class="contact-textarea"></textarea>
                    </div>
                    <button type="submit" class="contact-submit-btn">BOOK NOW</button>
                </form>
            </div>

            <!-- Contact Info (Right Column) -->
            <div class="contact-info-col">
                <h3 class="contact-info-heading">CONTACT INFOMATION</h3>
                <h4 class="contact-info-subheading">BUDDY PRIVATE TOUR</h4>
                
                <div class="contact-info-details">
                    <p style="margin: 0;">Address: 24/9 Nguyen Luong Bang street, Hoa Khanh Bac, Lien Chieu, Da Nang.</p>
                    <p style="margin: 0;">Zalo: +84567599998 – Hotline: +84567599998</p>
                    <p style="margin: 0;">Email: buddyprivatetour@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
