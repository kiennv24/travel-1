<?php
/**
 * Template Name: Tour Detail
 * Buddy Private Tour - Luxury UI/UX Redesign
 */

get_header();

$productId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : (isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0));
$productSlug = isset($_GET['slug']) ? sanitize_title($_GET['slug']) : '';

if (empty($productId) && empty($productSlug) && (is_singular('product') || get_post_type() === 'product')) {
    $productId = get_the_ID();
}

$product = null;
if (!empty($productId)) {
    $product = BPT_ProductModel::findById($productId);
} elseif (!empty($productSlug)) {
    $product = BPT_ProductModel::findBySlug($productSlug);
}

if (!$product && (is_singular('product') || get_post_type() === 'product')) {
    $product = BPT_ProductModel::fromWcPost(get_the_ID());
}

if (!$product) {
    echo '<div class="container" style="padding: 5rem 0; text-align: center;"><h2 data-i18n="no_tours_found">Requested tour not found!</h2><a href="' . esc_url(home_url('/')) . '" class="tour-card-cta-btn" style="margin-top: 1.5rem; display: inline-flex;"><span data-i18n="nav_home">Return to Home</span></a></div>';
    get_footer();
    exit;
}

// Fetch related tours
$related_products = BPT_ProductModel::getRelated($product['category_id'], $product['id'], 6);

// Decode gallery images, highlights, inclusions, exclusions
$gallery_images = json_decode($product['images'] ?? '[]', true) ?: [];
$highlights = json_decode($product['highlights'] ?? '[]', true) ?: [];
$included = json_decode($product['included'] ?? '[]', true) ?: [];
$excluded = json_decode($product['excluded'] ?? '[]', true) ?: [];

// Fallbacks
if (empty($included) && !empty($product['included'])) {
    $included = array_filter(array_map('trim', explode("\n", $product['included'])));
}
if (empty($excluded) && !empty($product['excluded'])) {
    $excluded = array_filter(array_map('trim', explode("\n", $product['excluded'])));
}

if (empty($highlights)) {
    $highlights = [
        '100% private and customizable itinerary tailored to your preferences.',
        'Modern private air-conditioned vehicle with direct hotel/airport pickup.',
        'Licensed local English/Vietnamese speaking guide with deep cultural knowledge.',
        'Authentic local cuisine dining at handpicked reputable restaurants.'
    ];
}

// Ensure we capture all unique images for full gallery
$raw_gallery = array_values(array_unique(array_filter(array_merge([$product['image']], $gallery_images))));
if (empty($raw_gallery)) {
    $raw_gallery = [$product['image']];
}
$unique_gallery = array_values(array_map('bpt_resolve_image_url', $raw_gallery));

// Desktop mosaic grid requires at least 5 images to fill the 1 large + 4 small slots
$mosaic_gallery = $unique_gallery;
while (count($mosaic_gallery) < 5) {
    $mosaic_gallery[] = bpt_resolve_image_url($product['image']);
}

$duration = !empty($product['duration']) ? $product['duration'] : 'Full Day (8 Hours)';
$unitPrice = floatval($product['price']);
?>

<!-- Breadcrumb Navigation -->
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
            <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
        </a>
        <span class="crumb-separator">/</span>
        <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="crumb-link">
            <span data-i18n="crumb_tour">Tours</span>
        </a>
        <span class="crumb-separator">/</span>
        <a href="<?php echo esc_url(site_url('?pagename=tour&category=' . $product['category_slug'])); ?>" class="crumb-link crumb-category">
            <?php echo esc_html($product['category_name']); ?>
        </a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current"><?php echo esc_html($product['title']); ?></span>
    </nav>
</div>

<!-- Tour Detail Main Wrapper -->
<div class="section-padding" style="padding-top: 1.5rem; padding-bottom: 5rem; background: #ffffff;">
    <div class="container">
        
        <!-- Header Title & Rating -->
        <div class="tour-detail-header-wrap">
            <h1 class="tour-detail-main-title"><?php echo esc_html($product['title']); ?></h1>
            <div class="tour-detail-sub-meta">
                <span class="tour-detail-badge-pill"><i class="fa-solid fa-gem"></i> <span data-i18n="detail_badge_private">Private Tour</span></span>
                <span class="tour-detail-rating"><i class="fa-solid fa-star"></i> 4.9 <span style="color: #64748b; font-weight: normal;" data-i18n="detail_rating_reviews">(86 international traveler reviews)</span></span>
                <span class="tour-detail-loc"><i class="fa-solid fa-location-dot"></i> <?php echo esc_html($product['category_name']); ?></span>
            </div>
        </div>

        <!-- 1. RESPONSIVE TOUR IMAGE GALLERY -->
        <div class="tour-gallery-component">
            <div class="tour-mosaic-gallery" id="bpt-gallery-main-frame">
                <!-- Main Large Image (Left 60% on desktop, 100% on mobile) -->
                <div class="mosaic-item mosaic-main" onclick="openBptGalleryModal(window.currentGalleryIdx || 0)">
                    <img id="bpt-main-img" src="<?php echo esc_url($unique_gallery[0]); ?>" alt="<?php echo esc_attr($product['title']); ?>" data-idx="0">
                    
                    <!-- Mobile Expand Icon -->
                    <button type="button" class="mobile-gallery-expand-btn" onclick="event.stopPropagation(); openBptGalleryModal(window.currentGalleryIdx || 0)" aria-label="Fullscreen view">
                        <i class="fa-solid fa-expand"></i>
                    </button>

                    <!-- Mobile Prev / Next Navigation Arrows -->
                    <button type="button" class="mobile-gallery-arrow prev" onclick="event.stopPropagation(); navigateBptGallery(-1)" aria-label="Previous photo">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="mobile-gallery-arrow next" onclick="event.stopPropagation(); navigateBptGallery(1)" aria-label="Next photo">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    
                    <!-- Photo Counter Badge (e.g. 1 / 8) -->
                    <div class="mobile-gallery-counter-pill">
                        <i class="fa-solid fa-camera"></i> <span id="gallery-current-num">1</span> / <span id="gallery-total-num"><?php echo count($unique_gallery); ?></span>
                    </div>
                </div>

                <!-- 4 Thumbnail Images (Right 40% in 2x2 Grid) -->
                <?php for ($i = 1; $i <= 4; $i++) : 
                    $thumb_url = $mosaic_gallery[$i] ?? $mosaic_gallery[0];
                ?>
                    <div class="mosaic-item" onclick="switchBptMosaicImage('<?php echo esc_js($thumb_url); ?>', this)">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="Gallery <?php echo $i; ?>">
                    </div>
                <?php endfor; ?>

                <button type="button" class="mosaic-view-all-btn" onclick="openBptGalleryModal(0)">
                    <i class="fa-solid fa-grip-vertical"></i> <span data-i18n="gallery_btn_view_all">View all photos</span> (<?php echo count($unique_gallery); ?>)
                </button>
            </div>

            <!-- Horizontal Scrollable Thumbnail Strip for Mobile & Quick Access -->
            <?php if (count($unique_gallery) > 1) : ?>
                <div class="mobile-gallery-strip-wrap">
                    <div class="mobile-gallery-strip" id="bpt-mobile-gallery-strip">
                        <?php foreach ($unique_gallery as $gIdx => $gUrl) : ?>
                            <div class="mobile-gallery-thumb <?php echo $gIdx === 0 ? 'active' : ''; ?>" data-idx="<?php echo $gIdx; ?>" onclick="selectBptGalleryIndex(<?php echo $gIdx; ?>)">
                                <img src="<?php echo esc_url($gUrl); ?>" alt="<?php echo esc_attr($product['title']); ?> photo <?php echo $gIdx + 1; ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. MAIN 2-COLUMN LAYOUT (65% Content / 35% Sticky Booking Box) -->
        <div class="bpt-tour-detail-grid">
            
            <!-- LEFT COLUMN (65%) -->
            <div class="tour-main-content-col">
                
                <!-- Quick Summary Bar with 5 Icon Badges -->
                <div class="tour-quick-info-bar">
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-car-side"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_vehicle_lbl">Transportation</span>
                            <span class="quick-info-val" data-i18n="info_vehicle_val">Modern Private Car</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-language"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_guide_lbl">Tour Guide</span>
                            <span class="quick-info-val" data-i18n="info_guide_val">Private English/Vietnamese</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_type_lbl">Tour Type</span>
                            <span class="quick-info-val" data-i18n="info_type_val">100% Private Tour</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-hotel"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_pickup_lbl">Pickup & Dropoff</span>
                            <span class="quick-info-val" data-i18n="info_pickup_val">Complimentary Hotel Pickup</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-clock"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_duration_lbl">Duration</span>
                            <span class="quick-info-val"><?php echo esc_html($duration); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Tour Highlights Card -->
                <div class="tour-highlights-card">
                    <h3 class="tour-highlights-title"><i class="fa-solid fa-star"></i> <span data-i18n="sec_highlights_title">TOUR HIGHLIGHTS</span></h3>
                    <ul class="tour-highlights-list">
                        <?php foreach ($highlights as $hl) : ?>
                            <li class="tour-highlight-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span><?php echo esc_html($hl); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Tour Day-by-Day Timeline / Accordion -->
                <div class="tour-timeline-section">
                    <h3 class="tour-section-heading"><i class="fa-solid fa-route" style="color: #0284c7;"></i> <span data-i18n="sec_itinerary_title">DETAILED ITINERARY</span></h3>
                    <div class="tour-timeline-container">
                        <!-- Day 1 -->
                        <div class="timeline-day-card active">
                            <div class="timeline-node">1</div>
                            <div class="timeline-day-header" onclick="toggleTimelineDay(this)">
                                <span class="timeline-day-title" data-i18n="timeline_day_1_title">Morning: Hotel Pickup & Signature Sightseeing</span>
                                <span class="timeline-day-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <div class="timeline-day-body">
                                <p data-i18n="timeline_day_1_body">Private car and guide pick you up promptly at your hotel or meeting point. Begin your journey visiting world-famous landmarks while enjoying insightful cultural stories from your dedicated local guide.</p>
                            </div>
                        </div>

                        <!-- Day 2 / Afternoon -->
                        <div class="timeline-day-card active">
                            <div class="timeline-node">2</div>
                            <div class="timeline-day-header" onclick="toggleTimelineDay(this)">
                                <span class="timeline-day-title" data-i18n="timeline_day_2_title">Afternoon: Authentic Local Cuisine & Cultural Discovery</span>
                                <span class="timeline-day-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <div class="timeline-day-body">
                                <p data-i18n="timeline_day_2_body">Savor a delicious traditional lunch at handpicked local restaurants. Continue exploring scenic attractions, capturing photos, and shopping for specialties before a safe private drop-off at your hotel.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rich Description & Notes -->
                <div>
                    <h3 class="tour-section-heading"><i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> <span data-i18n="sec_overview_title">ABOUT THIS TRIP</span></h3>
                    <?php if (!empty($product['summary'])) : ?>
                        <p style="font-size: 1.1rem; color: #1e293b; line-height: 1.8; margin-bottom: 1.5rem; font-weight: 500; font-style: italic; background: #f8fafc; padding: 1.25rem; border-left: 4px solid #0284c7; border-radius: 4px;">
                            <?php echo esc_html($product['summary']); ?>
                        </p>
                    <?php endif; ?>

                    <div class="bpt-tour-rich-description">
                        <?php echo wpautop(wp_kses_post($product['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: STICKY BOOKING BOX (35%) -->
            <div class="tour-sidebar-col">
                <div class="sticky-booking-card" id="tour-booking-card" data-unit-price="<?php echo floatval($unitPrice); ?>">
                    <!-- Price Header -->
                    <div class="booking-card-header">
                        <div class="booking-price-wrap">
                            <span class="booking-price-label" data-i18n="booking_price_lbl">Special private tour price</span>
                            <div class="booking-price-value">
                                <?php echo esc_html(format_currency($unitPrice)); ?>
                                <span class="booking-price-unit" data-i18n="booking_price_unit">/ guest</span>
                            </div>
                        </div>
                        <span style="background: #ffedd5; color: #c2410c; font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 9999px;" data-i18n="booking_discount_badge">
                            Save 15%
                        </span>
                    </div>

                    <!-- 1. PARTICIPANTS (SCREENSHOT 1) -->
                    <div class="booking-participants-section">
                        <div class="booking-section-header">
                            <h4 class="booking-section-title" data-i18n="booking_participants_title">Participants</h4>
                            <span class="booking-blue-bar"></span>
                        </div>

                        <div class="booking-participants-list">
                            <!-- Adult -->
                            <div class="booking-participant-row" data-type="adult">
                                <div class="participant-info">
                                    <span class="participant-name" data-i18n="participant_adult">Adult</span>
                                    <span class="participant-age" data-i18n="participant_adult_age">Age 15 - 85</span>
                                </div>
                                <div class="participant-stepper">
                                    <button type="button" class="participant-btn btn-minus disabled" onclick="changeParticipantQty('adult', -1)" aria-label="Decrease Adult">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <span class="participant-qty" id="qty-adult">1</span>
                                    <button type="button" class="participant-btn btn-plus" onclick="changeParticipantQty('adult', 1)" aria-label="Increase Adult">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Youth -->
                            <div class="booking-participant-row" data-type="youth">
                                <div class="participant-info">
                                    <span class="participant-name" data-i18n="participant_youth">Youth</span>
                                    <span class="participant-age" data-i18n="participant_youth_age">Age 10 - 14</span>
                                </div>
                                <div class="participant-stepper">
                                    <button type="button" class="participant-btn btn-minus disabled" onclick="changeParticipantQty('youth', -1)" aria-label="Decrease Youth">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <span class="participant-qty" id="qty-youth">0</span>
                                    <button type="button" class="participant-btn btn-plus" onclick="changeParticipantQty('youth', 1)" aria-label="Increase Youth">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Child -->
                            <div class="booking-participant-row" data-type="child">
                                <div class="participant-info">
                                    <span class="participant-name" data-i18n="participant_child">Child</span>
                                    <span class="participant-age" data-i18n="participant_child_age">Age 4 - 9</span>
                                </div>
                                <div class="participant-stepper">
                                    <button type="button" class="participant-btn btn-minus disabled" onclick="changeParticipantQty('child', -1)" aria-label="Decrease Child">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <span class="participant-qty" id="qty-child">0</span>
                                    <button type="button" class="participant-btn btn-plus" onclick="changeParticipantQty('child', 1)" aria-label="Increase Child">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Infant -->
                            <div class="booking-participant-row" data-type="infant">
                                <div class="participant-info">
                                    <span class="participant-name" data-i18n="participant_infant">Infant</span>
                                    <span class="participant-age" data-i18n="participant_infant_age">Age 0 - 3</span>
                                </div>
                                <div class="participant-stepper">
                                    <button type="button" class="participant-btn btn-minus disabled" onclick="changeParticipantQty('infant', -1)" aria-label="Decrease Infant">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <span class="participant-qty" id="qty-infant">0</span>
                                    <button type="button" class="participant-btn btn-plus" onclick="changeParticipantQty('infant', 1)" aria-label="Increase Infant">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. CHOOSE A DATE (SCREENSHOT 2) -->
                    <?php
                    $today_ts = current_time('timestamp');
                    $today_date = date('Y-m-d', $today_ts);
                    $today_year = intval(date('Y', $today_ts));
                    $today_month = intval(date('n', $today_ts));
                    $today_day = intval(date('j', $today_ts));
                    $today_formatted = date('D, M j, Y', $today_ts);
                    $month_label = date('F Y', $today_ts);

                    $first_day_ts = strtotime("$today_year-$today_month-01");
                    $start_weekday = intval(date('N', $first_day_ts)) - 1; // 0=Mon, ..., 6=Sun
                    $days_in_current_month = intval(date('t', $first_day_ts));
                    ?>
                    <div class="booking-date-section">
                        <div class="booking-section-header">
                            <h4 class="booking-section-title" data-i18n="booking_date_title">Choose a date</h4>
                            <span class="booking-blue-bar"></span>
                        </div>

                        <div class="custom-calendar-card" id="bpt-calendar">
                            <div class="calendar-nav-header">
                                <button type="button" class="cal-nav-btn disabled" id="cal-prev-month" onclick="changeCalendarMonth(-1)" aria-label="Previous Month">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                
                                <div class="cal-month-display" id="cal-month-display" onclick="toggleMonthPicker()">
                                    <span id="cal-month-label"><?php echo esc_html($month_label); ?></span>
                                    <i class="fa-solid fa-chevron-down cal-month-chevron"></i>
                                </div>
                                
                                <button type="button" class="cal-nav-btn" id="cal-next-month" onclick="changeCalendarMonth(1)" aria-label="Next Month">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>

                                <div class="cal-month-dropdown" id="cal-month-dropdown" style="display: none;"></div>
                            </div>

                            <div class="calendar-weekdays">
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                                <span>Sun</span>
                            </div>

                            <div class="calendar-days-grid" id="cal-days-grid">
                                <?php for ($i = 0; $i < $start_weekday; $i++) : ?>
                                    <div class="cal-day-cell empty"></div>
                                <?php endfor; ?>
                                <?php for ($d = 1; $d <= $days_in_current_month; $d++) : 
                                    $is_past = $d < $today_day;
                                    $is_today = $d === $today_day;
                                    $classes = ['cal-day-cell'];
                                    if ($is_past) $classes[] = 'past';
                                    if ($is_today) {
                                        $classes[] = 'today';
                                        $classes[] = 'selected';
                                    }
                                    $onclick = $is_past ? '' : " onclick=\"selectCalendarDate({$today_year}, " . ($today_month - 1) . ", {$d})\"";
                                ?>
                                    <div class="<?php echo implode(' ', $classes); ?>"<?php echo $onclick; ?>><?php echo $d; ?></div>
                                <?php endfor; ?>
                            </div>

                            <div class="cal-selected-badge" id="selected-date-badge">
                                <i class="fa-regular fa-calendar-check"></i>
                                <span><span data-i18n="selected_date_label">Selected Date:</span> <strong id="selected-date-text"><?php echo esc_html($today_formatted); ?></strong></span>
                            </div>

                            <input type="hidden" id="detail-tour-date" name="departure_date" value="<?php echo esc_attr($today_date); ?>">
                            <input type="hidden" id="detail-guest-count" name="quantity" value="1">
                        </div>
                    </div>

                    <!-- 3. DETAILED PRICE BREAKDOWN -->
                    <div class="booking-calc-summary">
                        <div class="booking-calc-row" id="row-calc-adult">
                            <span><span data-i18n="booking_calc_adults">Adults</span> (<span id="calc-adult-count">1</span> &times; 100%):</span>
                            <span class="val" id="calc-adult-total"><?php echo esc_html(format_currency($unitPrice)); ?></span>
                        </div>
                        <div class="booking-calc-row" id="row-calc-youth" style="display: none;">
                            <span><span data-i18n="booking_calc_youths">Youths</span> (<span id="calc-youth-count">0</span> &times; 85%):</span>
                            <span class="val" id="calc-youth-total"><?php echo esc_html(format_currency(0)); ?></span>
                        </div>
                        <div class="booking-calc-row" id="row-calc-child" style="display: none;">
                            <span><span data-i18n="booking_calc_children">Children</span> (<span id="calc-child-count">0</span> &times; 50%):</span>
                            <span class="val" id="calc-child-total"><?php echo esc_html(format_currency(0)); ?></span>
                        </div>
                        <div class="booking-calc-row" id="row-calc-infant" style="display: none;">
                            <span><span data-i18n="booking_calc_infants">Infants</span> (<span id="calc-infant-count">0</span>):</span>
                            <span class="free-tag" data-i18n="booking_calc_free">Free</span>
                        </div>
                        <div class="booking-calc-row">
                            <span data-i18n="booking_calc_service_lbl">Private guide & service fees:</span>
                            <span style="color: #15803d; font-weight: 700;" data-i18n="booking_calc_free">Free</span>
                        </div>
                        <div class="booking-calc-total">
                            <span data-i18n="booking_calc_subtotal_lbl">Subtotal:</span>
                            <span class="val" id="calc-grand-total"><?php echo esc_html(format_currency($unitPrice)); ?></span>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <button type="button" onclick="submitTourBooking(<?php echo $product['id']; ?>)" class="booking-cta-add-cart">
                        <i class="fa-solid fa-cart-shopping"></i> <span data-i18n="btn_book_now">BOOK THIS TOUR</span>
                    </button>

                    <!-- Trust Badges -->
                    <div class="booking-trust-guarantees">
                        <div class="booking-trust-item"><i class="fa-solid fa-circle-check"></i> <span data-i18n="guarantee_free_cancel">Free cancellation up to 24 hours in advance</span></div>
                        <div class="booking-trust-item"><i class="fa-solid fa-bolt"></i> <span data-i18n="guarantee_instant_confirm">Instant confirmation & VietQR E-voucher</span></div>
                        <div class="booking-trust-item"><i class="fa-solid fa-shield-halved"></i> <span data-i18n="guarantee_quality">Guaranteed 5-star service excellence</span></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. RELATED TOURS SECTION -->
        <?php if (!empty($related_products)) : ?>
            <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid #e2e8f0;">
                <div class="cat-header-wrap" style="margin-bottom: 2rem;">
                    <span class="category-sub-badge"><i class="fa-solid fa-compass"></i> <span data-i18n="sec_related_badge">More Recommendations</span></span>
                    <h2 class="category-section-title" data-i18n="sec_related_title">OTHER POPULAR PRIVATE TOURS</h2>
                </div>

                <div class="tours-grid">
                    <?php foreach (array_slice($related_products, 0, 3) as $rel) : 
                        $rel_url = site_url('?pagename=tour-detail&id=' . $rel['id']);
                        $rel_img = bpt_resolve_image_url($rel['image']);
                    ?>
                        <div class="tour-card">
                            <div class="tour-card-image">
                                <a href="<?php echo esc_url($rel_url); ?>">
                                    <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel['title']); ?>" loading="lazy">
                                </a>
                                <span class="tour-badge-private"><i class="fa-solid fa-gem"></i> <span data-i18n="card_badge_private">Private Tour</span></span>
                            </div>
                            <div class="tour-card-body">
                                <h3 class="tour-card-title">
                                    <a href="<?php echo esc_url($rel_url); ?>"><?php echo esc_html($rel['title']); ?></a>
                                </h3>
                                <div class="tour-card-footer">
                                    <div class="tour-price-wrap">
                                        <span class="tour-price-label" data-i18n="card_price_from">From</span>
                                        <span class="tour-price-value"><?php echo esc_html(format_currency($rel['price'])); ?></span>
                                    </div>
                                    <a href="<?php echo esc_url($rel_url); ?>" class="tour-card-cta-btn">
                                        <span data-i18n="card_btn_view">View Tour</span> <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- JavaScript Logic for Cart Addition & Booking State -->
<script>
window.tourPrice = <?php echo floatval($unitPrice); ?>;
window.BPT_BASE_URL = '<?php echo esc_url(site_url('/')); ?>';
window.bptGalleryPhotos = <?php echo json_encode($unique_gallery); ?>;
function tryInitBptBooking() {
    if (typeof window.initBptTourGallery === 'function') {
        window.initBptTourGallery(window.bptGalleryPhotos);
    }
    if (typeof window.initBptTourBooking === 'function') {
        window.initBptTourBooking(<?php echo floatval($unitPrice); ?>);
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInitBptBooking);
} else {
    tryInitBptBooking();
}
window.addEventListener('load', tryInitBptBooking);
</script>

<?php get_footer(); ?>
