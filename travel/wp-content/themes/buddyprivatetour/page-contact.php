<?php
/**
 * Template Name: Contact Page
 * Buddy Private Tour - Modern Clean Contact Experience
 */
get_header();
?>

<div class="contact-page-wrapper">
    <!-- Breadcrumb Navigation -->
    <div class="site-breadcrumb-wrap container">
        <nav class="site-breadcrumb-box">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
            </a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current" data-i18n="nav_contact">Contact Us</span>
        </nav>
    </div>

    <div class="container contact-container">
        <div class="contact-card-box">
            <div class="contact-grid">
                <!-- Contact Form (Left Column) -->
                <div>
                    <h1 class="contact-heading" data-i18n="nav_contact">CONTACT US</h1>
                    <p class="contact-subheading-text" data-i18n="form_subtitle">Leave your contact details and our travel experts will respond within 15 minutes.</p>
                    
                    <form action="#" method="POST" onsubmit="alert('Thank you! Your inquiry has been sent successfully. Our team will contact you shortly.'); return false;">
                        <div class="contact-form-group">
                            <label for="c_name" class="contact-form-label" data-i18n="form_fullname_lbl">Full Name *</label>
                            <input type="text" id="c_name" required class="contact-input" placeholder="e.g. John Smith" data-i18n-placeholder="form_fullname_ph">
                        </div>
                        <div class="contact-form-group">
                            <label for="c_phone" class="contact-form-label" data-i18n="form_phone_lbl">Phone / WhatsApp *</label>
                            <input type="tel" id="c_phone" required class="contact-input" placeholder="e.g. +84 567 599 998" data-i18n-placeholder="form_phone_ph">
                        </div>
                        <div class="contact-form-group">
                            <label for="c_email" class="contact-form-label" data-i18n="form_email_lbl">Email Address</label>
                            <input type="email" id="c_email" class="contact-input" placeholder="e.g. john@example.com" data-i18n-placeholder="form_email_ph">
                        </div>
                        <div class="contact-form-group" style="margin-bottom: 1.75rem;">
                            <label for="c_msg" class="contact-form-label" data-i18n="form_notes_lbl">Special Requests / Preferred Dates</label>
                            <textarea id="c_msg" rows="4" class="contact-textarea" placeholder="Tell us about your preferred schedule, number of travelers, hotel preferences..." data-i18n-placeholder="form_notes_ph"></textarea>
                        </div>
                        <button type="submit" class="contact-submit-btn">
                            <i class="fa-solid fa-paper-plane"></i> <span data-i18n="form_btn_submit">SEND REQUEST NOW</span>
                        </button>
                    </form>
                </div>

                <!-- Contact Info (Right Column) -->
                <div class="contact-info-col">
                    <h3 class="contact-info-heading">
                        <i class="fa-solid fa-circle-info"></i> <span data-i18n="footer_col_support">CUSTOMER SUPPORT</span>
                    </h3>
                    
                    <div class="contact-info-details">
                        <div class="contact-item-row">
                            <div class="contact-item-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="contact-item-text">
                                <strong>Headquarters</strong>
                                <span data-i18n="footer_address">No. 24, Hoa Minh 15 Street, Hoa Minh Ward, Lien Chieu District, Da Nang City, Vietnam</span>
                            </div>
                        </div>

                        <div class="contact-item-row">
                            <div class="contact-item-icon"><i class="fa-solid fa-phone-volume"></i></div>
                            <div class="contact-item-text">
                                <strong>24/7 Hotline & WhatsApp</strong>
                                <a href="tel:+84567599998" style="color: #0284c7; font-weight: 700;">+84 567 599 998</a>
                            </div>
                        </div>

                        <div class="contact-item-row">
                            <div class="contact-item-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div class="contact-item-text">
                                <strong>Official Email</strong>
                                <a href="mailto:booking@buddyprivatetour.com" style="color: #0284c7;">booking@buddyprivatetour.com</a>
                            </div>
                        </div>

                        <div class="contact-item-row">
                            <div class="contact-item-icon"><i class="fa-solid fa-clock"></i></div>
                            <div class="contact-item-text">
                                <strong>Working Hours</strong>
                                24/7 Online Support & Concierge
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
