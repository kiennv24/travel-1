<?php
/**
 * Template Name: Cart Page
 * Buddy Private Tour - Premium Shopping Cart
 */

get_header();

global $wpdb;

// Actions check (fallback direct query actions)
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
                <i class="fa-solid fa-house"></i>Home
            </a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current">Shopping Cart</span>
        </nav>
    </div>

    <div class="container">
        <!-- Title Section -->
        <div class="cart-title-section">
            <h1 class="cart-page-heading">YOUR SHOPPING CART</h1>
            <p class="cart-page-desc">Review and manage your selected tours and experiences before proceeding to checkout.</p>
        </div>

        <?php if (empty($cart_items)) : ?>
            <!-- Empty Cart Screen -->
            <div class="cart-empty-wrapper">
                <div class="cart-empty-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h2 class="cart-empty-title">Your cart is currently empty!</h2>
                <p class="cart-empty-desc">
                    Explore our private tours across Northern, Central & Southern Vietnam and add them to your cart.
                </p>
                <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="btn-cart-empty-cta">
                    <i class="fa-solid fa-compass"></i> Explore Tours Now
                </a>
            </div>

        <?php else : ?>
            <!-- 2-Column Cart Layout -->
            <div class="cart-layout-grid">
                
                <!-- Left: Products List Card -->
                <div class="cart-items-card">
                    <div class="cart-card-header">
                        <h2>
                            <i class="fa-solid fa-clipboard-list" style="color: #004b99;"></i> Selected Tour Packages
                        </h2>
                        <span class="cart-count-badge"><?php echo $total_qty; ?> ticket(s)</span>
                    </div>

                    <table class="cart-modern-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Tour Details</th>
                                <th style="width: 25%; text-align: center;">Tour Price</th>
                                <th style="width: 25%; text-align: center;">Quantity</th>
                                <th style="width: 40px;"></th>
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
                                    <td class="cart-col-price">
                                        <span class="cart-unit-price"><?php echo esc_html(format_currency($item['price'])); ?></span>
                                    </td>
                                    <td class="cart-col-qty">
                                        <div class="cart-stepper">
                                            <button type="button" class="cart-stepper-btn" onclick="adjustCartQty(<?php echo $item['id']; ?>, -1)">−</button>
                                            <input type="number" id="qty-input-<?php echo $item['id']; ?>" value="<?php echo esc_attr($item['quantity']); ?>" min="1" max="50" class="cart-stepper-input" onchange="updateDbCartQty(<?php echo $item['id']; ?>, this.value)">
                                            <button type="button" class="cart-stepper-btn" onclick="adjustCartQty(<?php echo $item['id']; ?>, 1)">+</button>
                                        </div>
                                    </td>
                                    <td class="cart-col-action">
                                        <a href="<?php echo esc_url(site_url('?pagename=cart&action=remove&product_id=' . $item['id'])); ?>" onclick="return confirm('Are you sure you want to remove this tour from your cart?')" class="cart-delete-action" title="Remove tour">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-card-footer">
                        <a href="<?php echo esc_url(site_url('?pagename=tour')); ?>" class="cart-continue-link">
                            <i class="fa-solid fa-arrow-left"></i> Continue Browsing Tours
                        </a>
                        <span style="font-size: 0.85rem; color: #64748b;">
                            <i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> All rates include taxes and private guide services.
                        </span>
                    </div>
                </div>

                <!-- Right: Sticky Order Summary Card -->
                <div class="cart-summary-sidebar">
                    <div class="cart-summary-box">
                        <h3 class="cart-summary-title">
                            <i class="fa-solid fa-receipt" style="color: #f7941d;"></i> Order Summary
                        </h3>

                        <div class="cart-summary-row">
                            <span>Total Tickets:</span>
                            <span class="val"><?php echo $total_qty; ?> ticket(s)</span>
                        </div>

                        <div class="cart-summary-row">
                            <span>Subtotal:</span>
                            <span class="val"><?php echo esc_html(format_currency($cart_total)); ?></span>
                        </div>

                        <div class="cart-summary-row">
                            <span>Payment Processing:</span>
                            <span class="free-tag">Free</span>
                        </div>

                        <div class="cart-summary-divider"></div>

                        <div class="cart-grand-total-row">
                            <span class="label">Total Amount:</span>
                            <span class="amount"><?php echo esc_html(format_currency($cart_total)); ?></span>
                        </div>

                        <a href="<?php echo esc_url(site_url('/?pagename=checkout')); ?>" class="btn-cart-checkout">
                            PROCEED TO CHECKOUT <i class="fa-solid fa-arrow-right"></i>
                        </a>
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
