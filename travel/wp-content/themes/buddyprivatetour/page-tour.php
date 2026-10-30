<?php
/**
 * Template Name: Tour Page
 * Buddy Private Tour - Modern Tour Listing Page
 */
get_header();

// Fetch products & categories
$all_products = BPT_ProductModel::getAll();
$categories = BPT_CategoryModel::getAll();

$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

$filter_key = '';
$current_cat_name = 'ALL PRIVATE TOURS';

if (!empty($selected_category)) {
    if (strpos($selected_category, 'north') !== false || strpos($selected_category, 'ha-noi') !== false || $selected_category == '1') {
        $filter_key = 'north';
        $current_cat_name = 'HANOI & NORTHERN VIETNAM TOURS';
    } elseif (strpos($selected_category, 'central') !== false || strpos($selected_category, 'da-nang') !== false || strpos($selected_category, 'danang') !== false || $selected_category == '2') {
        $filter_key = 'central';
        $current_cat_name = 'DA NANG & CENTRAL VIETNAM TOURS';
    } elseif (strpos($selected_category, 'south') !== false || strpos($selected_category, 'ho-chi-minh') !== false || $selected_category == '3') {
        $filter_key = 'south';
        $current_cat_name = 'HO CHI MINH CITY & SOUTHERN VIETNAM TOURS';
    } else {
        $filter_key = $selected_category;
        foreach ($categories as $c) {
            if ($c['slug'] === $selected_category || $c['id'] == $selected_category) {
                $current_cat_name = $c['name'];
                break;
            }
        }
    }
} elseif ($cat_id > 0) {
    if ($cat_id == 1) {
        $filter_key = 'north';
        $current_cat_name = 'HANOI & NORTHERN VIETNAM TOURS';
    } elseif ($cat_id == 2) {
        $filter_key = 'central';
        $current_cat_name = 'DA NANG & CENTRAL VIETNAM TOURS';
    } elseif ($cat_id == 3) {
        $filter_key = 'south';
        $current_cat_name = 'HO CHI MINH CITY & SOUTHERN VIETNAM TOURS';
    }
}

if (!empty($filter_key)) {
    $products = array_filter($all_products, function($p) use ($filter_key, $cat_id) {
        $cat_slug = strtolower($p['category_slug'] ?? '');
        $cat_name = strtolower($p['category_name'] ?? '');
        $p_cat_id = intval($p['category_id'] ?? 0);
        
        if ($filter_key === 'north') {
            return strpos($cat_slug, 'north') !== false || strpos($cat_name, 'north') !== false || strpos($cat_slug, 'ha-noi') !== false || strpos($cat_name, 'ha noi') !== false || strpos($cat_name, 'bắc') !== false || $p_cat_id === 1;
        }
        if ($filter_key === 'central') {
            return strpos($cat_slug, 'central') !== false || strpos($cat_name, 'central') !== false || strpos($cat_slug, 'da-nang') !== false || strpos($cat_name, 'da nang') !== false || strpos($cat_name, 'trung') !== false || $p_cat_id === 2;
        }
        if ($filter_key === 'south') {
            return strpos($cat_slug, 'south') !== false || strpos($cat_name, 'south') !== false || strpos($cat_slug, 'ho-chi-minh') !== false || strpos($cat_name, 'ho chi minh') !== false || strpos($cat_name, 'nam') !== false || $p_cat_id === 3;
        }
        return $cat_slug === strtolower($filter_key) || $p_cat_id === $cat_id;
    });
} else {
    $products = $all_products;
}

function bpt_tour_image_url($image_path) {
    return bpt_resolve_image_url($image_path);
}
?>

<div class="tour-page-wrapper">
    <div class="container">
        <!-- Breadcrumb navigation -->
        <div class="site-breadcrumb-wrap">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
                </a>
                <span class="crumb-separator">/</span>
                <?php if ($cat_id > 0 || !empty($selected_category)) : ?>
                    <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>" class="crumb-link">
                        <span data-i18n="crumb_tour">Tours</span>
                    </a>
                    <span class="crumb-separator">/</span>
                    <span class="crumb-current crumb-category"><?php echo esc_html($current_cat_name); ?></span>
                <?php else : ?>
                    <span class="crumb-current" data-i18n="crumb_tour">Tours</span>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Regional Filter Category Pills -->
        <div class="tour-category-pills-bar">
            <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="cat-pill-btn <?php echo empty($filter_key) ? 'active' : ''; ?>">
                <i class="fa-solid fa-globe"></i> <span data-i18n="filter_all_tours">All Tours</span>
            </a>
            <a href="<?php echo esc_url(site_url('?pagename=tour&category=northern-vietnam-tours')); ?>" class="cat-pill-btn <?php echo ($filter_key === 'north') ? 'active' : ''; ?>">
                <i class="fa-solid fa-mountain-sun"></i> <span data-i18n="filter_north">Hanoi & North Vietnam</span>
            </a>
            <a href="<?php echo esc_url(site_url('?pagename=tour&category=central-vietnam-tours')); ?>" class="cat-pill-btn <?php echo ($filter_key === 'central') ? 'active' : ''; ?>">
                <i class="fa-solid fa-umbrella-beach"></i> <span data-i18n="filter_central">Da Nang & Central Vietnam</span>
            </a>
            <a href="<?php echo esc_url(site_url('?pagename=tour&category=southern-vietnam-tours')); ?>" class="cat-pill-btn <?php echo ($filter_key === 'south') ? 'active' : ''; ?>">
                <i class="fa-solid fa-city"></i> <span data-i18n="filter_south">Ho Chi Minh City & South</span>
            </a>
        </div>

        <!-- Tour List Grid -->
        <div class="tours-grid">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : 
                    $tour_url = site_url('?pagename=tour-detail&id=' . $product['id']);
                    $duration = !empty($product['duration']) ? $product['duration'] : 'Full Day';
                ?>
                    <div class="tour-card">
                        <div class="tour-card-image">
                            <a href="<?php echo esc_url($tour_url); ?>">
                                <img src="<?php echo esc_url(bpt_tour_image_url($product['image'])); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                            </a>
                            
                            <span class="tour-badge-private">
                                <i class="fa-solid fa-gem"></i> <span data-i18n="card_badge_private">Private Tour</span>
                            </span>
                            
                            <span class="tour-badge-duration">
                                <i class="fa-regular fa-clock"></i> <?php echo esc_html($duration); ?>
                            </span>

                            <button type="button" class="tour-wishlist-btn" title="Wishlist">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="tour-card-body">
                            <div class="tour-card-meta">
                                <span class="tour-location-tag">
                                    <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($product['category_name']); ?>
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
                                <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_pickup">Hotel pickup</span></span>
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
            <?php else : ?>
                <div style="grid-column: 1 / -1; background: #fff; padding: 4rem; text-align: center; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-compass" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;" data-i18n="no_tours_found">No tours found</h3>
                    <p style="color: #64748b; font-size: 1rem; margin-bottom: 1.5rem;" data-i18n="no_tours_desc">Please select another category or contact our hotline for a customized itinerary.</p>
                    <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="cat-pill-btn active" data-i18n="btn_view_all_tours">View All Tours</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
