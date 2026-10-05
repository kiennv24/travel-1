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

// Ensure at least 5 images for mosaic grid
$all_gallery = array_values(array_unique(array_filter(array_merge([$product['image']], $gallery_images))));
while (count($all_gallery) < 5) {
    $all_gallery[] = $product['image'];
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

        <!-- 1. MOSAIC IMAGE GALLERY (Airbnb Style 1 Large + 4 Small) -->
        <div class="tour-mosaic-gallery">
            <!-- Main Large Image (Left 60%) -->
            <div class="mosaic-item mosaic-main" onclick="switchBptMosaicImage('<?php echo esc_js(bpt_resolve_image_url($all_gallery[0])); ?>', this)">
                <img id="bpt-main-img" src="<?php echo esc_url(bpt_resolve_image_url($all_gallery[0])); ?>" alt="<?php echo esc_attr($product['title']); ?>">
            </div>

            <!-- 4 Thumbnail Images (Right 40% in 2x2 Grid) -->
            <?php for ($i = 1; $i <= 4; $i++) : 
                $img_url = bpt_resolve_image_url($all_gallery[$i] ?? $all_gallery[0]);
            ?>
                <div class="mosaic-item" onclick="switchBptMosaicImage('<?php echo esc_js($img_url); ?>', this)">
                    <img src="<?php echo esc_url($img_url); ?>" alt="Gallery <?php echo $i; ?>">
                </div>
            <?php endfor; ?>

            <button type="button" class="mosaic-view-all-btn" onclick="alert('Viewing full photo collection!')">
                <i class="fa-solid fa-grip-vertical"></i> <span data-i18n="gallery_btn_view_all">View all photos</span> (<?php echo count($all_gallery); ?>)
            </button>
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

                <!-- Included & Excluded Section -->
                <div class="tour-inclusions-grid">
                    <!-- Included -->
                    <div class="inclusions-card included">
                        <h4 class="inclusions-card-title"><i class="fa-solid fa-circle-check"></i> <span data-i18n="sec_included_title">INCLUDED IN TOUR</span></h4>
                        <ul class="inclusions-list">
                            <?php if (!empty($included)) : ?>
                                <?php foreach ($included as $inc) : ?>
                                    <li><i class="fa-solid fa-check"></i> <span><?php echo esc_html($inc); ?></span></li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_1">Modern air-conditioned private vehicle throughout</span></li>
                                <li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_2">Professional English / Vietnamese speaking private guide</span></li>
                                <li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_3">All entrance fees and sightseeing tickets</span></li>
                                <li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_4">Complimentary bottled water and cold towels</span></li>
                                <li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_5">Full-coverage travel insurance</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Excluded -->
                    <div class="inclusions-card excluded">
                        <h4 class="inclusions-card-title"><i class="fa-solid fa-circle-xmark"></i> <span data-i18n="sec_excluded_title">EXCLUDED FROM TOUR</span></h4>
                        <ul class="inclusions-list">
                            <?php if (!empty($excluded)) : ?>
                                <?php foreach ($excluded as $exc) : ?>
                                    <li><i class="fa-solid fa-xmark"></i> <span><?php echo esc_html($exc); ?></span></li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li><i class="fa-solid fa-xmark"></i> <span data-i18n="exc_item_1">Personal expenses outside program (shopping, laundry)</span></li>
                                <li><i class="fa-solid fa-xmark"></i> <span data-i18n="exc_item_2">Gratuities & tips for driver and tour guide</span></li>
                                <li><i class="fa-solid fa-xmark"></i> <span data-i18n="exc_item_3">VAT invoice (if tax receipt requested)</span></li>
                            <?php endif; ?>
                        </ul>
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
                <div class="sticky-booking-card">
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

                    <!-- Date Selection -->
                    <div class="booking-form-group">
                        <label class="booking-input-label" for="detail-tour-date"><i class="fa-regular fa-calendar"></i> <span data-i18n="booking_date_lbl">Select Departure Date:</span></label>
                        <input type="date" id="detail-tour-date" class="booking-input-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <!-- Guest Counter Stepper -->
                    <div class="booking-form-group">
                        <label class="booking-input-label"><i class="fa-solid fa-user-group"></i> <span data-i18n="booking_guests_lbl">Number of Guests:</span></label>
                        <div class="booking-guest-stepper">
                            <button type="button" class="guest-stepper-btn" onclick="updateTourDetailGuests(-1, <?php echo $unitPrice; ?>)">−</button>
                            <input type="number" id="detail-guest-count" class="guest-stepper-input" value="2" min="1" max="50" readonly>
                            <button type="button" class="guest-stepper-btn" onclick="updateTourDetailGuests(1, <?php echo $unitPrice; ?>)">+</button>
                        </div>
                    </div>

                    <!-- Price Subtotal Breakdown -->
                    <div class="booking-calc-summary">
                        <div class="booking-calc-row">
                            <span><span data-i18n="booking_calc_label">Tour price x</span> <strong id="calc-guest-display">2</strong> <span data-i18n="booking_calc_guest_unit">guests:</span></span>
                            <span class="val" id="calc-subtotal-display"><?php echo format_currency($unitPrice * 2); ?></span>
                        </div>
                        <div class="booking-calc-row">
                            <span data-i18n="booking_calc_service_lbl">Private guide & service fees:</span>
                            <span style="color: #15803d; font-weight: 700;" data-i18n="booking_calc_free">Free</span>
                        </div>
                        <div class="booking-calc-total">
                            <span data-i18n="booking_calc_subtotal_lbl">Subtotal:</span>
                            <span class="val" id="calc-grand-total"><?php echo format_currency($unitPrice * 2); ?></span>
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

<!-- Mobile Sticky Bottom Booking Bar -->
<div class="mobile-bottom-booking-bar">
    <div class="mobile-bottom-bar-price">
        <span class="label" data-i18n="mobile_price_label">From</span>
        <span class="val"><?php echo esc_html(format_currency($unitPrice)); ?></span>
    </div>
    <button type="button" onclick="submitTourBooking(<?php echo $product['id']; ?>)" class="mobile-bottom-bar-cta">
        <i class="fa-solid fa-cart-shopping"></i> <span data-i18n="mobile_btn_book">Book Now</span>
    </button>
</div>

<!-- JavaScript Logic for Cart Addition -->
<script>
function submitTourBooking(id) {
    const qty = parseInt(document.getElementById('detail-guest-count').value) || 1;
    const baseUrl = '<?php echo esc_url(site_url('/')); ?>';
    window.location.href = baseUrl + '?bpt_action=add_to_cart&product_id=' + id + '&qty=' + qty;
}
</script>

<?php get_footer(); ?>
