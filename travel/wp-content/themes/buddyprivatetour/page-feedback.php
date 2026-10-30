<?php
/**
 * Template Name: Feedback Page
 * Description: Real reviews & testimonials from TripAdvisor
 */
get_header();
?>

<div class="feedback-page-wrapper">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <div class="site-breadcrumb-wrap feedback-breadcrumb-wrap">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current" data-i18n="nav_feedback">Feedback</span>
            </nav>
        </div>

        <!-- Top Header: Reviews & Action Links -->
        <div class="feedback-top-header">
            <h1 class="feedback-heading-title" data-i18n="reviews_heading">Reviews</h1>
            <div class="feedback-header-actions">
                <a href="https://www.tripadvisor.com/UserReview-g298085-d17546791-Buddy_Private_Tour-Da_Nang.html" target="_blank" rel="noopener noreferrer" class="write-review-btn">
                    <i class="fa-solid fa-pen"></i>
                    <span data-i18n="reviews_write_btn">Write a review</span>
                </a>
            </div>
        </div>

        <!-- 5-Star Rating Breakdown Card -->
        <div class="rating-summary-card">
            <div class="rating-score-box">
                <div class="rating-score-value">5.0</div>
                <div class="rating-score-label" data-i18n="reviews_label_excellent">Excellent</div>
                <div class="ta-bubbles-row">
                    <span class="ta-dot"></span>
                    <span class="ta-dot"></span>
                    <span class="ta-dot"></span>
                    <span class="ta-dot"></span>
                    <span class="ta-dot"></span>
                    <span class="rating-total-count">(233)</span>
                </div>
            </div>

            <div class="rating-breakdown-list">
                <div class="rating-row">
                    <span class="rating-label" data-i18n="reviews_label_excellent">Excellent</span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill fill-100"></div>
                    </div>
                    <span class="rating-count">233</span>
                </div>
                <div class="rating-row">
                    <span class="rating-label" data-i18n="reviews_label_good">Good</span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill fill-0"></div>
                    </div>
                    <span class="rating-count">0</span>
                </div>
                <div class="rating-row">
                    <span class="rating-label" data-i18n="reviews_label_average">Average</span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill fill-0"></div>
                    </div>
                    <span class="rating-count">0</span>
                </div>
                <div class="rating-row">
                    <span class="rating-label" data-i18n="reviews_label_poor">Poor</span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill fill-0"></div>
                    </div>
                    <span class="rating-count">0</span>
                </div>
                <div class="rating-row">
                    <span class="rating-label" data-i18n="reviews_label_terrible">Terrible</span>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill fill-0"></div>
                    </div>
                    <span class="rating-count">0</span>
                </div>
            </div>
        </div>

        <!-- Reviews Widget Section -->
        <div class="feedback-content-card">
            <style>
            /* Bulletproof TripAdvisor layout: Stack badge vertically above 100% full-width review cards */
            html body .feedback-content-card .wprev_outer_wb,
            html body .wprev_outer_wb.wprev_badge_style6_side,
            html body .wprev_outer_wb,
            .wprev_outer_wb {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                float: none !important;
                gap: 1.5rem !important;
            }
            html body .feedback-content-card .wprev_badge_div,
            html body .wprev_outer_wb .wprev_badge_div.badgeleft,
            html body .wprev_outer_wb .wprev_badge_div.badgeright,
            html body .wprev_badge_div,
            .wprev_badge_div.badgeleft,
            .wprev_badge_div.badgeright,
            .wprev_badge_div {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 0 1.5rem 0 !important;
                padding: 0 !important;
                float: none !important;
                flex: none !important;
                display: block !important;
            }
            html body .feedback-content-card .wprev-tripadvisor-place,
            html body .wprev_outer_wb .wprev-tripadvisor-place,
            .wprev-tripadvisor-place {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                flex-wrap: wrap !important;
            }
            html body .feedback-content-card .wprev-no-slider,
            html body .feedback-content-card .wprev-slider,
            html body .wprev_outer_wb .wprev-no-slider,
            html body .wprev_outer_wb .wprev-slider,
            html body .wprev_outer_wb .wprs_unslider,
            .wprev-no-slider,
            .wprev-slider {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                float: none !important;
                flex: 1 1 100% !important;
                display: block !important;
            }
            html body .feedback-content-card .wprevpro_t6_outer_div,
            html body .wprev_outer_wb .wprevpro_t6_outer_div,
            .wprevpro_t6_outer_div {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                margin: 0 0 1.25rem 0 !important;
                padding: 0 !important;
                float: none !important;
                display: block !important;
            }
            html body .feedback-content-card .wprevpro_t6_DIV_1,
            html body .wprev_outer_wb .wprevpro_t6_DIV_1,
            .wprevpro_t6_DIV_1 {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                padding: 0 !important;
                margin: 0 !important;
                float: none !important;
                display: block !important;
            }
            html body .feedback-content-card .wpproslider_t6_DIV_2,
            html body .wprev_outer_wb .wpproslider_t6_DIV_2 {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            </style>
            <?php
            // Execute TripAdvisor Review Slider Plugin Action
            ob_start();
            do_action( 'wprev_tripadvisor_plugin_action', 1 );
            $widget_output = ob_get_clean();

            // Strip conflicting inline <style> tags output by the plugin so our styles have 100% clean control
            $widget_output = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $widget_output);

            echo $widget_output;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
