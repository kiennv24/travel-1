<?php
/**
 * Template Name: Tour Detail
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
    echo '<div class="container" style="padding: 5rem 0; text-align: center;"><h2>Không tìm thấy sản phẩm/tour yêu cầu!</h2><a href="' . esc_url(home_url('/')) . '" class="btn-primary" style="margin-top: 1.5rem;">Quay về Trang chủ</a></div>';
    get_footer();
    exit;
}

// Fetch related tours for top sidebar card & bottom related section
$related_products = BPT_ProductModel::getRelated($product['category_id'], $product['id'], 6);

// Decode gallery images, highlights, inclusions, and exclusions
$gallery_images = json_decode($product['images'] ?? '[]', true) ?: [];
$highlights = json_decode($product['highlights'] ?? '[]', true) ?: [];
$included = json_decode($product['included'] ?? '[]', true) ?: [];
$excluded = json_decode($product['excluded'] ?? '[]', true) ?: [];

// Fallback to newline parsing if not stored as JSON
if (empty($included) && !empty($product['included'])) {
    $included = array_filter(array_map('trim', explode("\n", $product['included'])));
}
if (empty($excluded) && !empty($product['excluded'])) {
    $excluded = array_filter(array_map('trim', explode("\n", $product['excluded'])));
}

// Image helper function
function bpt_get_detail_image_url($image_path) {
    return bpt_resolve_image_url($image_path);
}
?>

<!-- Breadcrumbs Section matching user screenshot -->
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
            <i class="fa-solid fa-house"></i>Home
        </a>
        <span class="crumb-separator">/</span>
        <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="crumb-link">
            Tour
        </a>
        <span class="crumb-separator">/</span>
        <a href="<?php echo esc_url(site_url('?pagename=tour&category=' . $product['category_slug'])); ?>" class="crumb-link crumb-category">
            <?php echo esc_html($product['category_name']); ?>
        </a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current"><?php echo esc_html($product['title']); ?></span>
    </nav>
</div>

<div class="section-padding" style="padding-top: 1.5rem; padding-bottom: 4rem; background: #fff;">
    <div class="container">
        <!-- Main Product 3-Column Section matching screenshot -->
        <div class="bpt-tour-top-grid">
            
            <!-- Column 1: Main Image & Thumbnails -->
            <div class="bpt-gallery-col">
                <div class="bpt-main-image-wrap">
                    <img id="bpt-main-img" src="<?php echo esc_url(bpt_get_detail_image_url($product['image'])); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                </div>
                
                <!-- Thumbnails Row -->
                <?php 
                $all_gallery = array_merge([$product['image']], $gallery_images);
                $all_gallery = array_unique(array_filter($all_gallery));
                if (count($all_gallery) > 1) : 
                ?>
                    <div class="bpt-thumbnails-grid" style="display: flex; gap: 0.5rem; margin-top: 0.85rem; flex-wrap: wrap;">
                        <?php foreach ($all_gallery as $index => $img_path) : 
                            $img_url = bpt_get_detail_image_url($img_path);
                        ?>
                            <div class="bpt-thumb-item <?php echo $index === 0 ? 'active' : ''; ?>" onclick="switchBptImage('<?php echo esc_js($img_url); ?>', this)" style="width: 70px; height: 55px; border: 1px solid <?php echo $index === 0 ? '#0c4a85' : '#e2e8f0'; ?>; overflow: hidden; cursor: pointer;">
                                <img src="<?php echo esc_url($img_url); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Column 2: Product Title, Price, Quantity & Add to Cart Button -->
            <div class="bpt-tour-summary-col">
                <h1 style="font-size: 1.6rem; font-weight: 700; color: #1e293b; line-height: 1.25; margin-top: 0; margin-bottom: 0.5rem; text-transform: uppercase;">
                    <?php echo esc_html($product['title']); ?>
                </h1>
                
                <!-- Price Tag -->
                <div style="font-size: 1.35rem; font-weight: 800; color: #cc0000; margin-bottom: 2rem;">
                    <?php echo esc_html(format_currency($product['price'])); ?>
                </div>
                
                <!-- Amount Stepper Row -->
                <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.75rem;">
                    <span style="font-weight: 500; font-size: 0.82rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">AMOUNT:</span>
                    
                    <div class="bpt-qty-stepper">
                        <button type="button" onclick="changeQty(-1)" class="bpt-qty-btn">−</button>
                        <input type="number" id="detail-qty" value="1" min="1" max="50" class="bpt-qty-input" readonly>
                        <button type="button" onclick="changeQty(1)" class="bpt-qty-btn">+</button>
                    </div>
                </div>
                
                <!-- Orange Add to Cart Button -->
                <button onclick="addToCartDbDetail(<?php echo $product['id']; ?>)" class="bpt-add-cart-orange-btn">
                    ADD TO SHOPPING<br>CART
                </button>
            </div>

            <!-- Column 3: Related Tour Carousel Card matching screenshot -->
            <div class="bpt-tour-sidebar-col">
                <?php 
                $first_rel = !empty($related_products) ? $related_products[0] : null;
                if ($first_rel) :
                    $rel_img = bpt_get_detail_image_url($first_rel['image']);
                    $rel_url = site_url('?pagename=tour-detail&id=' . $first_rel['id']);
                ?>
                    <div class="bpt-side-related-card">
                        <!-- Top Header with Navigation Arrows -->
                        <div class="bpt-side-related-header">
                            <button type="button" onclick="prevRelated()" class="bpt-rel-nav-btn" aria-label="Previous">&lt;</button>
                            <button type="button" onclick="nextRelated()" class="bpt-rel-nav-btn" aria-label="Next">&gt;</button>
                        </div>
                        
                        <!-- Related Image -->
                        <div style="height: 175px; overflow: hidden; background: #fff;">
                            <a id="side-rel-img-link" href="<?php echo esc_url($rel_url); ?>">
                                <img id="side-rel-img" src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($first_rel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </a>
                        </div>
                        
                        <!-- Related Body -->
                        <div style="padding: 0.75rem 0.5rem 1.25rem; text-align: center;">
                            <a id="side-rel-title" href="<?php echo esc_url($rel_url); ?>" style="color: #333333; font-weight: 600; font-size: 0.88rem; line-height: 1.35; text-decoration: none; display: block; margin-bottom: 0.5rem; text-transform: uppercase;">
                                <?php echo esc_html($first_rel['title']); ?>
                            </a>
                            <div id="side-rel-price" style="font-size: 0.95rem; font-weight: 800; color: #cc0000;">
                                <?php echo esc_html(format_currency($first_rel['price'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tabs / Content Section matching screenshot -->
        <div class="bpt-tour-tabs-wrapper">
            <!-- Tab Navigation matching screenshot with Blue active block and bullets -->
            <div class="bpt-tab-nav-bar">
                <button onclick="switchTab('description')" id="tab-btn-description" class="bpt-tab-btn-active">DESCRIPTION</button>
                <button onclick="switchTab('reviews')" id="tab-btn-reviews" class="bpt-tab-btn-inactive"><span>•</span> REVIEWS (0)</button>
                <button onclick="switchTab('tags')" id="tab-btn-tags" class="bpt-tab-btn-inactive"><span>•</span> TAGS</button>
            </div>
            
            <!-- Tab Content: Description -->
            <div id="tab-content-description" class="bpt-tab-content" style="display: block;">
                <div style="max-width: 900px; margin: 0 auto;">
                    <!-- Summary Paragraph -->
                    <?php if (!empty($product['summary'])) : ?>
                        <p style="font-size: 1.1rem; color: #334155; line-height: 1.8; margin-bottom: 1.5rem; font-weight: 500; font-style: italic;">
                            <?php echo esc_html($product['summary']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Main Description Text with rich HTML & embedded images -->
                    <div class="bpt-tour-rich-description" style="font-size: 1.05rem; color: #1e293b; line-height: 1.8; margin-bottom: 2.5rem;">
                        <?php echo wpautop(wp_kses_post($product['description'])); ?>
                    </div>
                    
                  
                    
                    <!-- What's Included & What's Excluded Section -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #cbd5e1; margin-bottom: 2rem;">
                        <!-- Included -->
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; text-transform: none;">
                                What's included:
                            </h4>
                            <?php if (!empty($included)) : ?>
                                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; padding-left: 0; margin-bottom: 0;">
                                    <?php foreach ($included as $inc_item) : ?>
                                        <li style="display: flex; align-items: flex-start; gap: 0.5rem; color: #1e293b; font-size: 0.95rem; line-height: 1.5;">
                                            <span style="color: #1e293b; font-weight: bold; margin-top: -0.1rem; font-size: 0.8rem;">♦</span>
                                            <span><?php echo esc_html($inc_item); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p style="font-size: 0.95rem; color: #64748b; font-style: italic;">No specifications.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Excluded -->
                        <div style="margin-top: 0.5rem;">
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; text-transform: none;">
                                What's excluded:
                            </h4>
                            <?php if (!empty($excluded)) : ?>
                                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; padding-left: 0; margin-bottom: 0;">
                                    <?php foreach ($excluded as $exc_item) : ?>
                                        <li style="display: flex; align-items: flex-start; gap: 0.5rem; color: #1e293b; font-size: 0.95rem; line-height: 1.5;">
                                            <span style="color: #1e293b; font-weight: bold; margin-top: -0.1rem; font-size: 0.8rem;">♦</span>
                                            <span><?php echo esc_html($exc_item); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p style="font-size: 0.95rem; color: #64748b; font-style: italic;">No specifications.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Reviews -->
            <div id="tab-content-reviews" class="bpt-tab-content" style="display: none;">
                <div style="max-width: 900px; margin: 0 auto; padding: 2.5rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                    <i class="fa-solid fa-comments" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">REVIEWS (0)</h3>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">There are no reviews for this tour yet. Be the first to share your experience!</p>
                    <button class="btn-primary" style="background: #0c4a85;" onclick="alert('Review system is coming soon!')">WRITE A REVIEW</button>
                </div>
            </div>
            
            <!-- Tab Content: Tags -->
            <div id="tab-content-tags" class="bpt-tab-content" style="display: none;">
                <div style="max-width: 900px; margin: 0 auto; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> <?php echo esc_html($product['category_slug']); ?>
                    </span>
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> travel
                    </span>
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> private-tour
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<?php
$related_products = BPT_ProductModel::getRelated($product['category_id'], $product['id'], 5);
if (!empty($related_products)) :
?>
    <div style="background: #fff; padding-bottom: 5rem;">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0c4a85; margin-bottom: 2rem; position: relative;">
                <span style="background: #0c4a85; color: #fff; padding: 0.6rem 1.5rem; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    RELATED PRODUCTS
                </span>
                <div style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: #64748b; font-weight: 700; margin-right: 0.5rem;">
                    <span style="cursor: pointer; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 2px; background: #fff;" onclick="alert('Use swipe or scroll to see more products.')">&lt;</span>
                    <span style="cursor: pointer; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 2px; background: #fff;" onclick="alert('Use swipe or scroll to see more products.')">&gt;</span>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem;">
                <?php foreach ($related_products as $rel) : 
                    $rel_img = bpt_get_detail_image_url($rel['image']);
                    $rel_url = site_url('?pagename=tour-detail&id=' . $rel['id']);
                ?>
                    <div class="bpt-related-card" style="background: #fff; border: 1px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align: center; min-width: 180px;">
                        <div style="height: 160px; overflow: hidden;">
                            <a href="<?php echo esc_url($rel_url); ?>">
                                <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" loading="lazy">
                            </a>
                        </div>
                        <div style="padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1; align-items: center;">
                            <h5 style="margin: 0 0 0.75rem; font-size: 0.9rem; line-height: 1.4; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                <a href="<?php echo esc_url($rel_url); ?>" style="color: #1e293b; font-weight: 700;"><?php echo esc_html($rel['title']); ?></a>
                            </h5>
                            <div style="font-size: 1.05rem; font-weight: 800; color: #ef4444;">
                                <?php echo esc_html(format_currency($rel['price'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript Helpers -->
<script>
// Side Related Tours Carousel
let relatedIndex = 0;
const relatedTours = <?php echo json_encode(array_values(array_map(function($r) {
    return [
        'id' => $r['id'],
        'title' => $r['title'],
        'price' => format_currency($r['price']),
        'image' => bpt_get_detail_image_url($r['image']),
        'url' => site_url('?pagename=tour-detail&id=' . $r['id'])
    ];
}, $related_products))); ?>;

function updateRelatedTour() {
    if (!relatedTours || relatedTours.length === 0) return;
    const current = relatedTours[relatedIndex];
    const imgElem = document.getElementById('side-rel-img');
    const titleElem = document.getElementById('side-rel-title');
    const linkElem = document.getElementById('side-rel-img-link');
    const priceElem = document.getElementById('side-rel-price');
    if (imgElem) imgElem.src = current.image;
    if (titleElem) {
        titleElem.textContent = current.title;
        titleElem.href = current.url;
    }
    if (linkElem) linkElem.href = current.url;
    if (priceElem) priceElem.textContent = current.price;
}

function prevRelated() {
    if (!relatedTours || relatedTours.length === 0) return;
    relatedIndex = (relatedIndex - 1 + relatedTours.length) % relatedTours.length;
    updateRelatedTour();
}

function nextRelated() {
    if (!relatedTours || relatedTours.length === 0) return;
    relatedIndex = (relatedIndex + 1) % relatedTours.length;
    updateRelatedTour();
}

function switchBptImage(imgUrl, thumb) {
    document.getElementById('bpt-main-img').src = imgUrl;
    
    // Remove active class styling from all thumbnail borders
    const thumbs = document.querySelectorAll('.bpt-thumb-item');
    thumbs.forEach(t => {
        t.style.borderColor = '#e2e8f0';
    });
    
    // Apply active border color
    thumb.style.borderColor = '#0c4a85';
}

function changeQty(amount) {
    const qtyInput = document.getElementById('detail-qty');
    let currentVal = parseInt(qtyInput.value) || 1;
    currentVal += amount;
    if (currentVal < 1) currentVal = 1;
    if (currentVal > 50) currentVal = 50;
    qtyInput.value = currentVal;
}

function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.bpt-tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById('tab-content-' + tabId);
    if (selectedContent) selectedContent.style.display = 'block';
    
    // Reset all tab button styles
    const tabs = ['description', 'reviews', 'tags'];
    tabs.forEach(t => {
        const btn = document.getElementById('tab-btn-' + t);
        if (btn) {
            btn.className = (t === tabId) ? 'bpt-tab-btn-active' : 'bpt-tab-btn-inactive';
        }
    });
}

function addToCartDbDetail(id) {
    const qty = parseInt(document.getElementById('detail-qty').value) || 1;
    const baseUrl = '<?php echo esc_url(site_url('/')); ?>';
    window.location.href = baseUrl + '?bpt_action=add_to_cart&product_id=' + id + '&qty=' + qty;
}
</script>

<?php get_footer(); ?>
