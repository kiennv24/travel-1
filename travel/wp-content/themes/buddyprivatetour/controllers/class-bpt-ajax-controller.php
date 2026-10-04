<?php
/**
 * AJAX Controller for custom theme actions (cart operations)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BPT_AJAXController {
    public static function init() {
        add_action('init', [__CLASS__, 'handleActions']);
        add_action('wp_ajax_bpt_add_to_cart', [__CLASS__, 'ajaxAddToCart']);
        add_action('wp_ajax_nopriv_bpt_add_to_cart', [__CLASS__, 'ajaxAddToCart']);
        add_action('wp_ajax_bpt_process_checkout', [__CLASS__, 'ajaxProcessCheckout']);
        add_action('wp_ajax_nopriv_bpt_process_checkout', [__CLASS__, 'ajaxProcessCheckout']);
    }

    public static function handleActions() {
        if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        $action = isset($_GET['bpt_action']) ? sanitize_key($_GET['bpt_action']) : (isset($_POST['bpt_action']) ? sanitize_key($_POST['bpt_action']) : '');
        
        if (!empty($action)) {
            global $wpdb;
            $session_id = session_id();
            $req = array_merge($_GET, $_POST);
            
            if ($action === 'add_to_cart') {
                $raw_id = isset($req['product_id']) ? intval($req['product_id']) : 0;
                $qty = isset($req['qty']) ? max(1, intval($req['qty'])) : 1;
                
                if ($raw_id > 0) {
                    $prod = BPT_ProductModel::findById($raw_id);
                    $product_id = $prod ? intval($prod['id']) : $raw_id;
                    
                    // Check if already in cart
                    $existing = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM cart WHERE session_id = %s AND (product_id = %d OR product_id = %d)",
                        $session_id, $product_id, $raw_id
                    ));
                    
                    if ($existing) {
                        $wpdb->update(
                            'cart',
                            ['quantity' => $existing->quantity + $qty, 'product_id' => $product_id],
                            ['id' => $existing->id]
                        );
                    } else {
                        $wpdb->insert(
                            'cart',
                            [
                                'session_id' => $session_id,
                                'product_id' => $product_id,
                                'quantity'   => $qty
                            ]
                        );
                    }

                    // Sync to WooCommerce Cart
                    if (function_exists('WC') && WC()->cart) {
                        $wc_prod_id = $wpdb->get_var($wpdb->prepare(
                            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_bpt_product_id' AND meta_value = %s LIMIT 1",
                            $product_id
                        ));
                        if (!$wc_prod_id) {
                            $wc_prod_id = $product_id;
                        }
                        if (wc_get_product($wc_prod_id)) {
                            WC()->cart->add_to_cart($wc_prod_id, $qty);
                        }
                    }

                    $is_ajax = wp_doing_ajax() 
                        || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                        || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                        || isset($req['ajax']);

                    if ($is_ajax) {
                        wp_send_json_success(['count' => bpt_db_cart_count()]);
                    } else {
                        wp_safe_redirect(site_url('?pagename=cart'));
                        exit;
                    }
                } else {
                    wp_send_json_error(['message' => 'Invalid product ID']);
                }
            }
            
            if ($action === 'update_cart') {
                $raw_id = isset($req['product_id']) ? intval($req['product_id']) : 0;
                $qty = isset($req['qty']) ? intval($req['qty']) : 1;
                $prod = BPT_ProductModel::findById($raw_id);
                $product_id = $prod ? intval($prod['id']) : $raw_id;

                if ($qty <= 0) {
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM cart WHERE session_id = %s AND (product_id = %d OR product_id = %d)",
                        $session_id, $product_id, $raw_id
                    ));
                } else {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE cart SET quantity = %d, product_id = %d WHERE session_id = %s AND (product_id = %d OR product_id = %d)",
                        $qty, $product_id, $session_id, $product_id, $raw_id
                    ));
                }

                $is_ajax = wp_doing_ajax() 
                    || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                    || isset($req['ajax']);

                if ($is_ajax) {
                    wp_send_json_success(['count' => bpt_db_cart_count()]);
                } else {
                    wp_safe_redirect(site_url('?pagename=cart'));
                    exit;
                }
            }
            
            if ($action === 'remove_from_cart') {
                $raw_id = isset($req['product_id']) ? intval($req['product_id']) : 0;
                $prod = BPT_ProductModel::findById($raw_id);
                $product_id = $prod ? intval($prod['id']) : $raw_id;

                $wpdb->query($wpdb->prepare(
                    "DELETE FROM cart WHERE session_id = %s AND (product_id = %d OR product_id = %d)",
                    $session_id, $product_id, $raw_id
                ));

                $is_ajax = wp_doing_ajax() 
                    || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                    || isset($req['ajax']);

                if ($is_ajax) {
                    wp_send_json_success(['count' => bpt_db_cart_count()]);
                } else {
                    wp_safe_redirect(site_url('?pagename=cart'));
                    exit;
                }
            }
            if ($action === 'process_checkout') {
                $name = isset($req['name']) ? sanitize_text_field($req['name']) : '';
                $phone = isset($req['phone']) ? sanitize_text_field($req['phone']) : '';
                $notes = isset($req['notes']) ? sanitize_textarea_field($req['notes']) : '';
                $payment_method = isset($req['payment_method']) && $req['payment_method'] === 'cod' ? 'cod' : 'vietqr';

                $cart_items = bpt_db_cart_get_items();
                if (empty($cart_items)) {
                    wp_send_json_error(['message' => 'Giỏ hàng của bạn đang trống!']);
                    exit;
                }

                $total_amount = 0;
                foreach ($cart_items as $item) {
                    $total_amount += floatval($item['price']) * intval($item['quantity']);
                }

                $order_code = 'BPT-' . strtoupper(wp_generate_password(6, false, false));

                // Insert into orders table
                $wpdb->insert('orders', [
                    'order_code' => $order_code,
                    'customer_name' => $name,
                    'customer_phone' => $phone,
                    'notes' => $notes,
                    'total_amount' => $total_amount,
                    'status' => 'pending',
                    'created_at' => current_time('mysql')
                ]);
                $order_id = $wpdb->insert_id;

                // Insert order items
                foreach ($cart_items as $it) {
                    $subtotal = floatval($it['price']) * intval($it['quantity']);
                    $wpdb->insert('order_items', [
                        'order_id' => $order_id,
                        'product_id' => intval($it['id']),
                        'product_title' => $it['title'],
                        'price' => floatval($it['price']),
                        'quantity' => intval($it['quantity']),
                        'subtotal' => $subtotal
                    ]);
                }

                // Store recent order in session
                if (!isset($_SESSION['bpt_recent_orders'])) {
                    $_SESSION['bpt_recent_orders'] = [];
                }
                $_SESSION['bpt_recent_orders'][] = $order_code;

                // Clear DB cart
                $wpdb->delete('cart', ['session_id' => $session_id]);

                // Clear WC cart if active
                if (function_exists('WC') && WC()->cart) {
                    WC()->cart->empty_cart(true);
                }

                // Generate VietQR URL if vietqr
                $vietqr_url = '';
                if ($payment_method === 'vietqr') {
                    $vietqr_url = 'https://img.vietqr.io/image/970436-9782794280-compact2.png?amount=' . (int)round($total_amount) . '&addInfo=' . urlencode($order_code) . '&accountName=' . urlencode('NGUYEN VAN KIEN');
                }

                wp_send_json_success([
                    'order_code' => $order_code,
                    'total_amount' => $total_amount,
                    'total_formatted' => number_format($total_amount, 0, ',', '.') . ' VNĐ',
                    'payment_method' => $payment_method,
                    'vietqr_url' => $vietqr_url,
                    'account_name' => 'NGUYEN VAN KIEN',
                    'account_number' => '9782794280',
                    'bank_name' => 'Vietcombank'
                ]);
                exit;
            }
        }
    }

    public static function ajaxAddToCart() {
        self::handleActions();
    }

    public static function ajaxProcessCheckout() {
        self::handleActions();
    }
}

// Instantiate controller
BPT_AJAXController::init();
