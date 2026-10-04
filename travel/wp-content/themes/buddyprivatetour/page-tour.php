<?php
/**
 * Template Name: Tour Page
 */
get_header();

// Fetch products & categories
$all_products = BPT_ProductModel::getAll();
$categories = BPT_CategoryModel::getAll();

$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

$filter_key = '';
$current_cat_name = 'TOUR';

if (!empty($selected_category)) {
    if (strpos($selected_category, 'north') !== false || strpos($selected_category, 'ha-noi') !== false || $selected_category == '1') {
        $filter_key = 'north';
        $current_cat_name = 'HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)';
    } elseif (strpos($selected_category, 'central') !== false || strpos($selected_category, 'da-nang') !== false || strpos($selected_category, 'danang') !== false || $selected_category == '2') {
        $filter_key = 'central';
        $current_cat_name = 'DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)';
    } elseif (strpos($selected_category, 'south') !== false || strpos($selected_category, 'ho-chi-minh') !== false || $selected_category == '3') {
        $filter_key = 'south';
        $current_cat_name = 'HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)';
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
        $current_cat_name = 'HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)';
    } elseif ($cat_id == 2) {
        $filter_key = 'central';
        $current_cat_name = 'DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)';
    } elseif ($cat_id == 3) {
        $filter_key = 'south';
        $current_cat_name = 'HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)';
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
        <!-- Breadcrumb bar matching user screenshot -->
        <div class="site-breadcrumb-wrap">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <?php if ($cat_id > 0) : ?>
                    <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>" class="crumb-link">
                        Tour
                    </a>
                    <span class="crumb-separator">/</span>
                    <span class="crumb-current crumb-category"><?php echo esc_html($current_cat_name); ?></span>
                <?php else : ?>
                    <span class="crumb-current">Tour</span>
                <?php endif; ?>
            </nav>
        </div>

        <h1 class="tour-page-heading">
            <?php echo esc_html($current_cat_name); ?>
        </h1>

        <!-- Tour list grid with add to cart buttons -->
        <div class="tours-grid">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <div class="tour-card">
                        <!-- Floating category badge -->
                        <span class="tour-card-badge">
                            <?php echo esc_html($product['category_name']); ?>
                        </span>

                        <div class="tour-card-image">
                            <a href="<?php echo esc_url(site_url('?pagename=tour-detail&id=' . $product['id'])); ?>">
                                <img src="<?php echo esc_url(bpt_tour_image_url($product['image'])); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                            </a>
                        </div>
                        
                        <div class="tour-card-body">
                            <h4 class="tour-card-title">
                                <a href="<?php echo esc_url(site_url('?pagename=tour-detail&id=' . $product['id'])); ?>"><?php echo esc_html($product['title']); ?></a>
                            </h4>
                            
                            <div class="tour-card-price">
                                <?php echo esc_html(format_currency($product['price'])); ?>
                            </div>

                            <!-- Add to cart button utilizing Database Cart -->
                            <button onclick="addToCartDb(<?php echo $product['id']; ?>)" class="tour-card-btn" style="background: #22c55e; border: none; cursor: pointer;">
                                <i class="fa-solid fa-cart-plus"></i> Add to shopping cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div style="grid-column: 1 / -1; background: #fff; padding: 3rem; text-align: center; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <p style="color: #64748b; font-size: 1.1rem; margin: 0;">No tours found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Database Cart Integration Helper -->
<script>
function addToCartDb(id) {
    const baseUrl = '<?php echo esc_url(site_url('/')); ?>';
    window.location.href = baseUrl + '?bpt_action=add_to_cart&product_id=' + id + '&qty=1';
}
</script>

<?php get_footer(); ?>
