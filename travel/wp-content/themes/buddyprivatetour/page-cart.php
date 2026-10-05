<?php
/**
 * Template Name: Cart Page
 * Buddy Private Tour - Luxury Shopping Cart
 */

get_header();

global $wpdb;

// Handle remove action
if (isset($_GET['action'])) {
    $action = sanitize_key($_GET['action']);
    $product_id = intval($_GET['product_id'] ?? 0);
    $session_id = session_id();
    
    if ($action === 'remove' && $product_id > 0) {
        $prod = BPT_ProductModel::findById($product_id);
        $norm_id = $prod ? intval($prod['id']) : $product_id;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM cart WHERE session_id = %s AND (product_id = %d OR product_id = %d)",
            $session_id, $norm_id, $product_id
        ));
        wp_safe_redirect(site_url('?pagename=cart'));
        exit;
    }
}

$cart_items = bpt_db_cart_get_items();
$cart_total = 0;
$total_qty = 0;

foreach ($cart_items as $it) {
    $cart_total += $it['price'] * $it['quantity'];
    $total_qty += $it['quantity'];
}
?>

<div class="cart-page-wrapper">
    <!-- Breadcrumb Navigation -->
    <div class="site-breadcrumb-wrap container">
        <nav class="site-breadcrumb-box">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
            </a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current" data-i18n="nav_cart">Shopping Cart</span>
        </nav>
    </div>

    <div class="container">
        <!-- Title Section -->
        <div class="cart-title-section">
            <h1 class="cart-page-heading" data-i18n="cart_page_heading">YOUR SHOPPING CART</h1>
            <p class="cart-page-desc" data-i18n="cart_page_desc">Review your selected private tour packages before proceeding to checkout and generating your VietQR electronic voucher.</p>
        </div>

        <?php if (empty($cart_items)) : ?>
            <!-- Empty Cart Screen -->
            <div class="cart-empty-wrapper">
                <div class="cart-empty-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h2 class="cart-empty-title" data-i18n="cart_empty_title">Your cart is currently empty!</h2>
                <p class="cart-empty-desc" data-i18n="cart_empty_desc">
                    Explore our exclusive private tours across North, Central, and South Vietnam.
                </p>
                <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="btn-cart-empty-cta">
                    <i class="fa-solid fa-compass"></i> <span data-i18n="cart_btn_explore">Explore Tours Now</span>
                </a>
            </div>

        <?php else : ?>
            <!-- 2-Column Cart Layout -->
            <div class="cart-layout-grid">
                
                <!-- Left: Products List Card -->
                <div class="cart-items-card">
                    <div class="cart-card-header">
                        <h2>
                            <i class="fa-solid fa-clipboard-list"></i> <span data-i18n="cart_list_heading">Selected Tour Packages</span>
                        </h2>
                        <span class="cart-count-badge"><?php echo $total_qty; ?> <span data-i18n="cart_badge_tickets">tickets selected</span></span>
                    </div>

                    <table class="cart-modern-table">
                        <thead>
                            <tr>
                                <th style="width: 48%;" data-i18n="cart_th_info">Tour Details</th>
                                <th style="width: 22%; text-align: center;" data-i18n="cart_th_price">Unit Price</th>
                                <th style="width: 20%; text-align: center;" data-i18n="cart_th_qty">Guests</th>
                                <th style="width: 10%; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item) : 
                                $img_url = !empty($item['image']) ? bpt_resolve_image_url($item['image']) : '';
                                $tour_url = site_url('?pagename=tour-detail&id=' . $item['id']);
                            ?>
                                <tr>
                                    <td class="cart-col-details">
                                        <div class="cart-product-cell">
                                            <a href="<?php echo esc_url($tour_url); ?>" class="cart-item-thumb">
                                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                            </a>
                                            <div class="cart-item-info">
                                                <?php if (!empty($item['category_name'])) : ?>
                                                    <span class="cart-item-cat"><?php echo esc_html($item['category_name']); ?></span>
                                                <?php endif; ?>
                                                <a href="<?php echo esc_url($tour_url); ?>" class="cart-item-name">
                                                    <?php echo esc_html($item['title']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="cart-unit-price"><?php echo esc_html(format_currency($item['price'])); ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="cart-stepper">
                                            <button type="button" class="cart-stepper-btn" onclick="adjustCartQty(<?php echo $item['id']; ?>, -1)">−</button>
                                            <input type="number" id="qty-input-<?php echo $item['id']; ?>" value="<?php echo esc_attr($item['quantity']); ?>" min="1" max="50" class="cart-stepper-input" onchange="updateDbCartQty(<?php echo $item['id']; ?>, this.value)">
                                            <button type="button" class="cart-stepper-btn" onclick="adjustCartQty(<?php echo $item['id']; ?>, 1)">+</button>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?php echo esc_url(site_url('?pagename=cart&action=remove&product_id=' . $item['id'])); ?>" onclick="return confirm('Are you sure you want to remove this tour?')" class="cart-delete-action" title="Remove tour">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-card-footer">
                        <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="cart-continue-link">
                            <i class="fa-solid fa-arrow-left"></i> <span data-i18n="cart_btn_continue">Choose More Tours</span>
                        </a>
                        <span style="font-size: 0.85rem; color: #64748b;">
                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> <span data-i18n="inc_item_1">Modern air-conditioned private vehicle throughout</span>
                        </span>
                    </div>
                </div>

                <!-- Right: Sticky Order Summary Card -->
                <div class="cart-summary-sidebar">
                    <div class="cart-summary-box">
                        <h3 class="cart-summary-title">
                            <i class="fa-solid fa-receipt"></i> <span data-i18n="cart_summary_heading">Order Summary</span>
                        </h3>

                        <div class="cart-summary-row">
                            <span data-i18n="cart_summary_subtotal">Tours Subtotal:</span>
                            <span class="val"><?php echo esc_html(format_currency($cart_total)); ?></span>
                        </div>

                        <div class="cart-summary-row">
                            <span data-i18n="cart_summary_guide">Private Driver & Guide:</span>
                            <span class="free-tag" data-i18n="cart_summary_free">Included</span>
                        </div>

                        <div class="cart-summary-divider"></div>

                        <div class="cart-grand-total-row">
                            <span class="label" data-i18n="cart_summary_total">Total Payment:</span>
                            <span class="amount"><?php echo esc_html(format_currency($cart_total)); ?></span>
                        </div>

                        <a href="<?php echo esc_url(site_url('/?pagename=checkout')); ?>" class="btn-cart-checkout">
                            <span data-i18n="cart_btn_checkout">PROCEED TO SECURE CHECKOUT</span> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="cart-trust-box">
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span data-i18n="cart_trust_1">Instant reservation confirmation & VietQR E-Ticket</span>
                        </div>
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-car-side"></i>
                            <span data-i18n="cart_trust_2">Dedicated private English-speaking tour guide</span>
                        </div>
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-headset"></i>
                            <span data-i18n="cart_trust_3">Full-coverage licensed travel insurance included</span>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function adjustCartQty(productId, delta) {
    const input = document.getElementById('qty-input-' + productId);
    if (!input) return;
    let val = parseInt(input.value) || 1;
    val += delta;
    if (val < 1) val = 1;
    if (val > 50) val = 50;
    input.value = val;
    updateDbCartQty(productId, val);
}

function updateDbCartQty(productId, qty) {
    const baseUrl = '<?php echo esc_url(site_url('/')); ?>';
    fetch(baseUrl + '?bpt_action=update_cart&product_id=' + productId + '&qty=' + qty)
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                window.location.reload();
            } else {
                window.location.href = baseUrl + '?bpt_action=update_cart&product_id=' + productId + '&qty=' + qty;
            }
        })
        .catch(err => {
            window.location.href = baseUrl + '?bpt_action=update_cart&product_id=' + productId + '&qty=' + qty;
        });
}
</script>

<?php get_footer(); ?>
