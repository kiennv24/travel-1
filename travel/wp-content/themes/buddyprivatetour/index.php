<?php
/**
 * Main template file fallback / Homepage fallback
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

<!-- Hero Slider Section matching 3 banner designs -->
<section class="hero-slider" id="hero-slider">
    <div class="slider-wrapper">
        <!-- Slide 1: Buddy Private Tour Overview (in Hanoi, Da nang, Nha trang, Da lat, Ho chi minh, Phu quoc) -->
        <div class="slide-item active">
            <a href="#products-catalog" class="slide-link">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn3.jpg'); ?>" alt="Buddy Private Tour - in Hanoi, Da nang, Nha trang, Da lat, Ho chi minh, Phu quoc" class="slide-img" />
            </a>
        </div>
        <!-- Slide 2: Stimulate tourism with Buddy Private Tour - Discount up to 50% -->
        <div class="slide-item">
            <a href="#products-catalog" class="slide-link">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn2.jpg'); ?>" alt="Stimulate tourism with Buddy Private Tour - Discount up to 50%" class="slide-img" />
            </a>
        </div>
        <!-- Slide 3: Roaming Everywhere Together - BUDDY PRIVATE TOUR - Travel Phu Quoc Island -->
        <div class="slide-item">
            <a href="#products-catalog" class="slide-link">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn1.jpg'); ?>" alt="Roaming Everywhere Together - BUDDY PRIVATE TOUR - Travel Phu Quoc Island" class="slide-img" />
            </a>
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

<!-- Editable Content Area for Homepage (Elementor / Gutenberg) -->
<?php if ( have_posts() ) : ?>
    <div class="homepage-editable-content container" style="margin-top: 3rem; margin-bottom: 2rem;">
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

            <div id="<?php echo esc_attr($cat['slug']); ?>" style="margin-bottom: 4rem; scroll-margin-top: 100px;">
                <!-- Category Section Header with Ribbons -->
                <div class="cat-header-wrap">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/new-badge.png'); ?>" class="new-badge-icon" alt="New Badge">
                    <h2 class="category-section-title"><?php echo esc_html($cat['name']); ?></h2>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/new-badge.png'); ?>" class="new-badge-icon" alt="New Badge">
                </div>

                <div class="tours-grid">
                    <?php foreach ($catProducts as $product) : ?>
                        <div class="tour-card">
                            <div class="tour-card-image">
                                <a href="<?php echo esc_url(site_url('?pagename=tour-detail&id=' . $product['id'])); ?>">
                                    <img src="<?php echo esc_url(bpt_get_main_image_url($product['image'])); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                                </a>
                            </div>
                            
                            <div class="tour-card-body">
                                <h4 class="tour-card-title">
                                    <a href="<?php echo esc_url(site_url('?pagename=tour-detail&id=' . $product['id'])); ?>"><?php echo esc_html($product['title']); ?></a>
                                </h4>
                                
                                <a href="<?php echo esc_url(site_url('?pagename=tour-detail&id=' . $product['id'])); ?>" class="card-readmore-btn">
                                    <i class="fa-regular fa-circle-right"></i> Read more
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Home Featured Videos Section matching screenshot -->
        <div class="home-videos-section">
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

<!-- Full-width Middle Banner (bn3.jpg) stretched 100% across the screen -->
<div class="middle-banner-fullwidth" style="width: 100%; margin-top: 2rem; margin-bottom: 3.5rem; overflow: hidden; line-height: 0;">
    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/bn3.jpg'); ?>" alt="Buddy Private Tour Banner" style="width: 100%; height: auto; display: block; object-fit: cover;">
</div>

<div class="section-padding" style="padding-top: 0; background: #fff;">
    <div class="container">
        <!-- Other Services Section matching screenshot -->
        <?php
        $other_services_items = BPT_ProductModel::getByCategorySlug('other-services');
        if (empty($other_services_items)) {
            $other_services_items = [
                [
                    'slug' => 'car-rental',
                    'title' => 'CAR RENTAL FROM 4-45',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi.webp'
                ],
                [
                    'slug' => 'hotel',
                    'title' => 'HOTEL',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/hotel.webp'
                ],
                [
                    'slug' => 'airline-tickets',
                    'title' => 'CHEAP AIRLINE TICKETS',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/ve-7546-1571044895.webp'
                ]
            ];
        }
        ?>
        <div id="other-services" class="other-services-home-section" style="margin-bottom: 4rem; scroll-margin-top: 100px;">
            <div class="cat-header-wrap">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/new-badge.png'); ?>" class="new-badge-icon" alt="New Badge">
                <h2 class="category-section-title">OTHER SERVICES</h2>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/new-badge.png'); ?>" class="new-badge-icon" alt="New Badge">
            </div>

            <div class="tours-grid">
                <?php foreach ($other_services_items as $srv) : 
                    $detail_url = site_url('?pagename=other-services-detail&service=' . $srv['slug']);
                ?>
                    <div class="tour-card">
                        <div class="tour-card-image">
                            <a href="<?php echo esc_url($detail_url); ?>">
                                <img src="<?php echo esc_url(bpt_resolve_image_url($srv['image'])); ?>" alt="<?php echo esc_attr($srv['title']); ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="tour-card-body">
                            <h4 class="tour-card-title">
                                <a href="<?php echo esc_url($detail_url); ?>"><?php echo esc_html($srv['title']); ?></a>
                            </h4>
                            <a href="<?php echo esc_url($detail_url); ?>" class="card-readmore-btn">
                                <i class="fa-regular fa-circle-right"></i> Read more
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Consultation form section (Glassmorphism layout) -->
<section id="consultation" class="consultation-section" style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/halong-bg.jpg'); ?>');">
    <div class="container">
        <div class="glass-form-box">
            <h3 class="glass-form-title">ĐĂNG KÝ TƯ VẤN</h3>
            <form action="#" method="POST" onsubmit="alert('Cảm ơn bạn! Thông tin đăng ký đã được gửi đi.'); return false;">
                <div class="glass-form-group">
                    <label for="cust_name">Your Name:</label>
                    <input type="text" id="cust_name" class="glass-form-control" required placeholder="Họ và tên">
                </div>
                <div class="glass-form-group">
                    <label for="cust_phone">Your Phone:</label>
                    <input type="tel" id="cust_phone" class="glass-form-control" required placeholder="Số điện thoại">
                </div>
                <div class="glass-form-group">
                    <label for="cust_msg">Your Message:</label>
                    <textarea id="cust_msg" class="glass-form-control" rows="4" placeholder="Lời nhắn hoặc yêu cầu chi tiết..."></textarea>
                </div>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button type="submit" class="glass-btn-submit">BOOK NOW</button>
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
        slideInterval = setInterval(nextSlide, 5000);
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
