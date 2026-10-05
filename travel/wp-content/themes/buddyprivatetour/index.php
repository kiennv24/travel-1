<?php
/**
 * Main template file / Modern Homepage
 * Buddy Private Tour - Luxury UI/UX Redesign
 */

get_header();

// Fetch categories and products
$categories = BPT_CategoryModel::getAll();
$products = BPT_ProductModel::getAll();

// Image helper function
if (!function_exists('bpt_get_main_image_url')) {
    function bpt_get_main_image_url($image_path) {
        return bpt_resolve_image_url($image_path);
    }
}
?>

<!-- Hero Slider Section -->
<section class="hero-slider" id="hero-slider">
    <div class="slider-wrapper">
        <!-- Slide 1 -->
        <div class="slide-item active">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn3.jpg'); ?>" alt="Buddy Private Tour - Exclusive Journeys" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-crown"></i> <span data-i18n="hero_tagline_1">Exclusive Private Tour Services</span></span>
                <h1 class="hero-title" data-i18n="hero_title_1">Explore Vietnam In Your Own Unique Way</h1>
                <p class="hero-subtitle" data-i18n="hero_sub_1">Tailor-made private journeys for families & friends featuring modern private cars and licensed local tour guides.</p>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="slide-item">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn2.jpg'); ?>" alt="Stimulate tourism with Buddy Private Tour" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-percent"></i> <span data-i18n="hero_tagline_2">Seasonal Tour Offers</span></span>
                <h2 class="hero-title" style="font-size: 2.85rem; font-weight: 800;" data-i18n="hero_title_2">Cherish Every Unforgettable Moment</h2>
                <p class="hero-subtitle" data-i18n="hero_sub_2">Indulge in 5-star standard private services with complete itinerary flexibility.</p>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="slide-item">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn1.jpg'); ?>" alt="Roaming Everywhere Together - Travel Vietnam" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-gem"></i> <span data-i18n="hero_tagline_3">Distinctive Travel Experiences</span></span>
                <h2 class="hero-title" style="font-size: 2.85rem; font-weight: 800;" data-i18n="hero_title_3">Hanoi • Da Nang • Phu Quoc • Saigon</h2>
                <p class="hero-subtitle" data-i18n="hero_sub_3">Easy online booking, instant payment via VietQR, and immediate electronic voucher delivery.</p>
            </div>
        </div>
    </div>

    <!-- Bullets Navigation -->
    <div class="slider-bullets">
        <span class="bullet active" onclick="setSlide(0)" aria-label="Slide 1"></span>
        <span class="bullet" onclick="setSlide(1)" aria-label="Slide 2"></span>
        <span class="bullet" onclick="setSlide(2)" aria-label="Slide 3"></span>
    </div>

    <!-- Navigation Arrows -->
    <button class="slider-nav prev" onclick="changeSlide(-1)" aria-label="Previous Slide">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="slider-nav next" onclick="changeSlide(1)" aria-label="Next Slide">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</section>

<!-- Floating Interactive Search Pill (Airbnb & Klook Style) -->
<div class="floating-search-pill-wrap">
    <form id="heroSearchForm" class="floating-search-pill" action="<?php echo esc_url(site_url('?pagename=tour')); ?>" method="GET">
        <input type="hidden" name="pagename" value="tour">
        
        <!-- Field 1: Destination -->
        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-solid fa-location-dot"></i> <span data-i18n="search_dest_label">Destination</span></span>
            <select name="category" id="searchDestination" class="search-field-select">
                <option value="" data-i18n="search_dest_placeholder">All Destinations</option>
                <option value="northern-vietnam-tours" data-i18n="search_dest_north">Hanoi & North Vietnam (Halong Bay, Ninh Binh)</option>
                <option value="central-vietnam-tours" data-i18n="search_dest_central">Da Nang & Central Vietnam (Hoi An, Hue, Ba Na)</option>
                <option value="southern-vietnam-tours" data-i18n="search_dest_south">Ho Chi Minh City & South (Mekong Delta, Phu Quoc)</option>
            </select>
        </div>

        <!-- Field 2: Travel Date -->
        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-regular fa-calendar-days"></i> <span data-i18n="search_date_label">Departure Date</span></span>
            <input type="date" name="travel_date" id="searchDate" class="search-field-input" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <!-- Field 3: Guests -->
        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-solid fa-user-group"></i> <span data-i18n="search_guest_label">Number of Guests</span></span>
            <select name="guests" id="searchGuests" class="search-field-select">
                <option value="1" data-i18n="search_guest_1">1 Guest (Solo Private)</option>
                <option value="2" selected data-i18n="search_guest_2">2 Guests (Couples / Friends)</option>
                <option value="4" data-i18n="search_guest_4">3 - 5 Guests (Family)</option>
                <option value="8" data-i18n="search_guest_8">6+ Guests (Private Group)</option>
            </select>
        </div>

        <!-- CTA Search Button -->
        <button type="submit" class="search-cta-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span data-i18n="btn_search">Search Tours</span>
        </button>
    </form>
</div>

<!-- Editable Content Area for Homepage (Elementor / Gutenberg) -->
<?php if ( have_posts() ) : ?>
    <div class="homepage-editable-content container" style="margin-top: 3.5rem; margin-bottom: 2rem;">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
<?php endif; ?>

<!-- Product Catalog -->
<div id="products-catalog" class="section-padding">
    <div class="container">
        
        <?php foreach ($categories as $cat) : 
            if (($cat['slug'] ?? '') === 'other-services' || (int)($cat['id'] ?? 0) === 4 || (int)($cat['id'] ?? 0) === 21) continue;
            $catProducts = array_filter($products, function($p) use ($cat) {
                return (int)$p['category_id'] === (int)$cat['id'];
            });
            if (empty($catProducts)) continue;
            
            $cat_str = strtolower(($cat['slug'] ?? '') . ' ' . ($cat['name'] ?? ''));
            $isNorth = (strpos($cat_str, 'north') !== false || strpos($cat_str, 'bac') !== false || (int)($cat['id'] ?? 0) === 1 || (int)($cat['id'] ?? 0) === 18);
            $isCentral = (strpos($cat_str, 'central') !== false || strpos($cat_str, 'trung') !== false || (int)($cat['id'] ?? 0) === 2 || (int)($cat['id'] ?? 0) === 19);
            $isSouth = (strpos($cat_str, 'south') !== false || strpos($cat_str, 'nam') !== false || (int)($cat['id'] ?? 0) === 3 || (int)($cat['id'] ?? 0) === 20);

            // Show Quang Binh banner right before the Central Vietnam Tours category
            if ($isCentral) :
        ?>
            <!-- Quảng Bình middle banner -->
            <div class="middle-banner-section">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/quangbinh-banner.jpg'); ?>" alt="Quang Binh Banner">
            </div>
        <?php endif; ?>

            <?php if ($isNorth) : ?>
                <div id="northern-vietnam-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
                <div id="ha-noi-and-nearby-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
            <?php elseif ($isCentral) : ?>
                <div id="central-vietnam-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
                <div id="danang-and-nearby-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
            <?php elseif ($isSouth) : ?>
                <div id="southern-vietnam-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
                <div id="ho-chi-minh-city-and-nearby-tours" style="position:relative; top:-90px; visibility:hidden;"></div>
            <?php endif; ?>

            <div id="<?php echo esc_attr($cat['slug']); ?>" style="margin-bottom: 4.5rem; scroll-margin-top: 100px;">
                <!-- Modern Category Section Header (Clean Airbnb/Klook Style) -->
                <div class="cat-header-wrap">
                    <span class="category-sub-badge"><i class="fa-solid fa-sparkles"></i> <span data-i18n="cat_badge_selected">Hand-Picked Itineraries</span></span>
                    <h2 class="category-section-title"><?php echo esc_html($cat['name']); ?></h2>
                    <p class="category-section-desc" data-i18n="cat_desc_common">Experience luxury private journeys with modern vehicles and fully customizable itineraries.</p>
                </div>

                <div class="tours-grid">
                    <?php foreach ($catProducts as $product) : 
                        $tour_url = site_url('?pagename=tour-detail&id=' . $product['id']);
                        $duration = !empty($product['duration']) ? $product['duration'] : 'Full Day';
                    ?>
                        <div class="tour-card">
                            <!-- Image Top Container -->
                            <div class="tour-card-image">
                                <a href="<?php echo esc_url($tour_url); ?>">
                                    <img src="<?php echo esc_url(bpt_get_main_image_url($product['image'])); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                                </a>
                                
                                <span class="tour-badge-private">
                                    <i class="fa-solid fa-gem"></i> <span data-i18n="card_badge_private">Private Tour</span>
                                </span>
                                
                                <span class="tour-badge-duration">
                                    <i class="fa-regular fa-clock"></i> <?php echo esc_html($duration); ?>
                                </span>

                                <button type="button" class="tour-wishlist-btn" title="Save to wishlist">
                                    <i class="fa-regular fa-heart"></i>
                                </button>
                            </div>
                            
                            <!-- Card Body -->
                            <div class="tour-card-body">
                                <div class="tour-card-meta">
                                    <span class="tour-location-tag">
                                        <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($cat['name']); ?>
                                    </span>
                                    <div class="tour-rating-stars">
                                        <i class="fa-solid fa-star"></i> 4.9 <span style="color: #64748b; font-weight: normal;" data-i18n="card_reviews_suffix">(86 reviews)</span>
                                    </div>
                                </div>

                                <h3 class="tour-card-title">
                                    <a href="<?php echo esc_url($tour_url); ?>"><?php echo esc_html($product['title']); ?></a>
                                </h3>

                                <div class="tour-card-perks">
                                    <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_car">Private car</span></span>
                                    <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_guide">Expert guide</span></span>
                                    <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_ticket">Entry tickets</span></span>
                                </div>
                                
                                <div class="tour-card-footer">
                                    <div class="tour-price-wrap">
                                        <span class="tour-price-label" data-i18n="card_price_from">From</span>
                                        <span class="tour-price-value"><?php echo esc_html(format_currency($product['price'])); ?></span>
                                    </div>

                                    <a href="<?php echo esc_url($tour_url); ?>" class="tour-card-cta-btn">
                                        <span data-i18n="card_btn_view">View Tour</span> <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Home Featured Videos Section -->
        <div class="home-videos-section">
            <div class="cat-header-wrap" style="margin-bottom: 2rem;">
                <span class="category-sub-badge"><i class="fa-solid fa-video"></i> <span data-i18n="sec_videos_badge">Real Journey Clips</span></span>
                <h2 class="category-section-title" data-i18n="sec_videos_title">JOURNEYS WITH BUDDY PRIVATE TOUR</h2>
            </div>

            <div class="home-videos-grid">
                <!-- Video 1 -->
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/AAeb7JPwJfw?si=w0Z43dropCnsjNTq" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Video 2 -->
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/STt213h-_hM?si=FOz-tYlcH1LChGxP" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full-width Middle Banner -->
<div class="middle-banner-fullwidth">
    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn3.jpg'); ?>" alt="Buddy Private Tour Banner">
</div>

<!-- Consultation form section (Glassmorphism layout) -->
<section id="consultation" class="consultation-section" style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/halong-bg.jpg'); ?>');">
    <div class="container">
        <div class="glass-form-box">
            <h3 class="glass-form-title" data-i18n="form_title">CUSTOMIZE YOUR PRIVATE TOUR</h3>
            <p class="glass-form-subtitle" data-i18n="form_subtitle">Leave your contact details and our travel experts will respond within 15 minutes</p>
            
            <form action="#" method="POST" onsubmit="alert('Thank you! Your inquiry has been submitted. Our travel expert will contact you shortly.'); return false;">
                <div class="glass-form-group">
                    <label for="cust_name" data-i18n="form_fullname_lbl">FULL NAME *</label>
                    <input type="text" id="cust_name" class="glass-form-control" required placeholder="e.g. John Smith" data-i18n-placeholder="form_fullname_ph">
                </div>
                <div class="glass-form-group">
                    <label for="cust_phone" data-i18n="form_phone_lbl">PHONE / WHATSAPP *</label>
                    <input type="tel" id="cust_phone" class="glass-form-control" required placeholder="e.g. +84 567 599 998" data-i18n-placeholder="form_phone_ph">
                </div>
                <div class="glass-form-group">
                    <label for="cust_msg" data-i18n="form_notes_lbl">SPECIAL REQUESTS / DATES</label>
                    <textarea id="cust_msg" class="glass-form-control" rows="3" placeholder="Tell us about your preferred schedule, number of travelers, hotel preferences..." data-i18n-placeholder="form_notes_ph"></textarea>
                </div>
                <div style="text-align: center; margin-top: 1.75rem;">
                    <button type="submit" class="glass-btn-submit">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 0.4rem;"></i> <span data-i18n="form_btn_submit">SEND REQUEST NOW</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Slider Script -->
<script>
(function() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slider .slide-item');
    const bullets = document.querySelectorAll('.hero-slider .bullet');
    let slideInterval = null;

    function showSlide(index) {
        if (!slides || slides.length === 0) return;
        slides.forEach(s => s.classList.remove('active'));
        bullets.forEach(b => b.classList.remove('active'));

        currentSlide = (index + slides.length) % slides.length;
        if (slides[currentSlide]) {
            slides[currentSlide].classList.add('active');
        }
        if (bullets[currentSlide]) {
            bullets[currentSlide].classList.add('active');
        }
    }

    window.setSlide = function(index) {
        showSlide(index);
        resetTimer();
    };

    window.changeSlide = function(direction) {
        showSlide(currentSlide + direction);
        resetTimer();
    };

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function resetTimer() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        resetTimer();
        const slider = document.getElementById('hero-slider');
        if (slider) {
            slider.addEventListener('mouseenter', function() {
                if (slideInterval) clearInterval(slideInterval);
            });
            slider.addEventListener('mouseleave', function() {
                resetTimer();
            });
        }
    });
})();
</script>

<?php get_footer(); ?>
