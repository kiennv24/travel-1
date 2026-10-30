<?php
/**
 * Theme functions and definitions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Enqueue styles and scripts
function bpt_enqueue_scripts() {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // Fonts & FontAwesome
    wp_enqueue_style('bpt-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Caveat:wght@700&display=swap', array(), null);
    wp_enqueue_style('bpt-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), null);
    
    // Main Theme base stylesheet
    $style_ver = file_exists($theme_dir . '/assets/css/style.css') ? filemtime($theme_dir . '/assets/css/style.css') : '2.0.0';
    wp_enqueue_style('bpt-style', $theme_uri . '/assets/css/style.css', array(), $style_ver);

    // Modular Page Stylesheets
    $checkout_ver = file_exists($theme_dir . '/assets/css/checkout.css') ? filemtime($theme_dir . '/assets/css/checkout.css') : '2.0.0';
    $cart_ver = file_exists($theme_dir . '/assets/css/cart.css') ? filemtime($theme_dir . '/assets/css/cart.css') : '2.0.0';
    $tour_ver = file_exists($theme_dir . '/assets/css/tour.css') ? filemtime($theme_dir . '/assets/css/tour.css') : '2.0.0';
    $td_ver = file_exists($theme_dir . '/assets/css/tour-detail.css') ? filemtime($theme_dir . '/assets/css/tour-detail.css') : '2.0.0';
    $os_ver = file_exists($theme_dir . '/assets/css/other-services.css') ? filemtime($theme_dir . '/assets/css/other-services.css') : '2.0.0';
    $pages_ver = file_exists($theme_dir . '/assets/css/pages.css') ? filemtime($theme_dir . '/assets/css/pages.css') : '2.0.0';
    $fb_ver = file_exists($theme_dir . '/assets/css/feedback.css') ? filemtime($theme_dir . '/assets/css/feedback.css') : '2.0.0';

    wp_enqueue_style('bpt-tour-detail', $theme_uri . '/assets/css/tour-detail.css', array('bpt-style'), $td_ver);
    wp_enqueue_style('bpt-tour', $theme_uri . '/assets/css/tour.css', array('bpt-style'), $tour_ver);
    wp_enqueue_style('bpt-cart', $theme_uri . '/assets/css/cart.css', array('bpt-style'), $cart_ver);
    wp_enqueue_style('bpt-checkout', $theme_uri . '/assets/css/checkout.css', array('bpt-style'), $checkout_ver);
    wp_enqueue_style('bpt-other-services', $theme_uri . '/assets/css/other-services.css', array('bpt-style'), $os_ver);
    wp_enqueue_style('bpt-pages', $theme_uri . '/assets/css/pages.css', array('bpt-style'), $pages_ver);
    $fb_deps = array('bpt-style');
    if (wp_style_is('wptripadvisor_allcss', 'registered') || wp_style_is('wptripadvisor_allcss', 'enqueued')) {
        $fb_deps[] = 'wptripadvisor_allcss';
    }
    wp_enqueue_style('bpt-feedback', $theme_uri . '/assets/css/feedback.css', $fb_deps, time());

    // Theme JS (Translations first, then Main Theme JS)
    $trans_ver = file_exists($theme_dir . '/assets/js/translations.js') ? filemtime($theme_dir . '/assets/js/translations.js') : '2.0.0';
    $main_ver = file_exists($theme_dir . '/assets/js/main.js') ? filemtime($theme_dir . '/assets/js/main.js') : '2.0.0';

    wp_enqueue_script('bpt-translations', $theme_uri . '/assets/js/translations.js', array(), $trans_ver, false);
    wp_enqueue_script('bpt-main', $theme_uri . '/assets/js/main.js', array('bpt-translations'), $main_ver, true);
}
add_action('wp_enqueue_scripts', 'bpt_enqueue_scripts', 99);

// Theme setup
function bpt_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');

    // Disable WooCommerce Coming Soon mode completely so cart & checkout always open
    if (get_option('woocommerce_coming_soon') === 'yes') {
        update_option('woocommerce_coming_soon', 'no');
    }
}
add_action('after_setup_theme', 'bpt_theme_setup');

// Ensure Coming Soon is never triggered on any pages
add_filter('woocommerce_is_coming_soon', '__return_false', 9999);
add_filter('woocommerce_coming_soon_exclude', '__return_true', 9999);

// Prevent WooCommerce from automatically redirecting checkout to cart
add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false', 9999);

// Completely disable WordPress Canonical Redirect (Tắt hoàn toàn tính năng tự động chuyển hướng / đoán URL của WordPress)
remove_action('template_redirect', 'redirect_canonical');
add_filter('redirect_canonical', '__return_false', 9999);

// Direct high-priority template loader for custom endpoints (Instant Loading)c
add_action('template_redirect', 'bpt_handle_custom_page_routing', 1);
function bpt_handle_custom_page_routing() {
    if (isset($_GET['pagename'])) {
        $p = sanitize_key($_GET['pagename']);
        if ($p === 'tour-detail') {
            $tmpl = get_template_directory() . '/page-tour-detail.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'other-services-detail') {
            $tmpl = get_template_directory() . '/page-other-services-detail.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'cart') {
            $tmpl = get_template_directory() . '/page-cart.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'checkout') {
            if (!isset($_GET['order-received']) && !isset($_GET['order_id'])) {
                bpt_sync_db_cart_to_wc();
            }
            $tmpl = get_template_directory() . '/page-checkout.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'tour') {
            $tmpl = get_template_directory() . '/page-tour.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'other-services') {
            $tmpl = get_template_directory() . '/page-other-services.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
        if ($p === 'feedback') {
            $tmpl = get_template_directory() . '/page-feedback.php';
            if (file_exists($tmpl)) {
                include $tmpl;
                exit;
            }
        }
    }
}

// Automatically redirect default WooCommerce /shop page to the custom Tour page
add_action('template_redirect', function() {
    if (function_exists('is_shop') && is_shop()) {
        wp_safe_redirect(site_url('?pagename=tour'), 301);
        exit;
    }
});

// Automatically sync DB cart items to WooCommerce cart before template execution
add_action('template_redirect', 'bpt_sync_db_cart_to_wc', 5);

function bpt_sync_db_cart_to_wc() {
    static $synced = false;
    if ($synced) return;

    if (wp_doing_ajax()) {
        return;
    }

    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return;
    }
    if (isset($_GET['order-received']) || isset($_GET['order_id'])) {
        return;
    }

    if (function_exists('WC') && WC()->cart && function_exists('bpt_db_cart_get_items')) {
        $db_items = bpt_db_cart_get_items();
        global $wpdb;

        $wc_cart_items = WC()->cart->get_cart();
        $db_count = count($db_items);
        $wc_count = count($wc_cart_items);

        if ($db_count === 0 && $wc_count === 0) {
            $synced = true;
            return;
        }

        // Clear WC cart and reload with active DB cart items
        WC()->cart->empty_cart(false);

        if (!empty($db_items)) {
            foreach ($db_items as $item) {
                $wc_prod_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_bpt_product_id' AND meta_value = %s LIMIT 1",
                    $item['id']
                ));
                if (!$wc_prod_id) {
                    $wc_prod_id = $item['id'];
                }
                if (wc_get_product($wc_prod_id)) {
                    $cart_item_data = [
                        'departure_date'  => $item['departure_date'] ?? date('Y-m-d'),
                        'adults'          => intval($item['adults'] ?? $item['quantity']),
                        'youths'          => intval($item['youths'] ?? 0),
                        'children'        => intval($item['children'] ?? 0),
                        'infants'         => intval($item['infants'] ?? 0),
                        'bpt_total_price' => floatval($item['total_price'] ?? ($item['price'] * $item['quantity'])),
                    ];
                    WC()->cart->add_to_cart($wc_prod_id, intval($item['quantity']), 0, [], $cart_item_data);
                }
            }
        }
        $synced = true;
    }
}

// Clear DB cart when WooCommerce order is placed/thank you page loaded
add_action('woocommerce_thankyou', 'bpt_clear_db_cart_on_order', 10, 1);
function bpt_clear_db_cart_on_order($order_id) {
    if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    $session_id = session_id();
    if (!empty($session_id)) {
        global $wpdb;
        $wpdb->delete('cart', ['session_id' => $session_id]);
    }
}

// Route WooCommerce Single Products to Tour Detail Template
add_filter('template_include', 'bpt_route_single_product_template', 99);
function bpt_route_single_product_template($template) {
    if (is_singular('product')) {
        $tour_detail_template = get_template_directory() . '/page-tour-detail.php';
        if (file_exists($tour_detail_template)) {
            return $tour_detail_template;
        }
    }
    return $template;
}

// Automatically sync WooCommerce Product updates into custom `products` database table
add_action('save_post_product', 'bpt_sync_wc_product_to_custom_table', 20, 2);
function bpt_sync_wc_product_to_custom_table($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!$post || $post->post_status !== 'publish') return;
    
    global $wpdb;
    $custom_id = get_post_meta($post_id, '_bpt_product_id', true);
    $item = BPT_ProductModel::fromWcPost($post);
    if (!$item) return;

    $cat_id = 1;
    $terms = wp_get_post_terms($post_id, 'product_cat');
    if (!empty($terms) && !is_wp_error($terms)) {
        $slug = strtolower($terms[0]->slug);
        if (strpos($slug, 'central') !== false || strpos($slug, 'da-nang') !== false) {
            $cat_id = 2;
        } elseif (strpos($slug, 'south') !== false || strpos($slug, 'ho-chi-minh') !== false) {
            $cat_id = 3;
        } else {
            $cat_id = 1;
        }
    }

    $data = [
        'category_id'     => $cat_id,
        'title'           => $item['title'],
        'slug'            => $item['slug'],
        'price'           => $item['price'],
        'formatted_price' => $item['formatted_price'] ?? '',
        'duration'        => $item['duration'] ?? 'Full Day',
        'image'           => $item['image'],
        'images'          => $item['images'],
        'summary'         => $item['summary'],
        'description'     => $item['description'],
        'highlights'      => $item['highlights'] ?? '[]',
        'included'        => $item['included'] ?? '[]',
        'excluded'        => $item['excluded'] ?? '[]',
    ];

    if ($custom_id) {
        $wpdb->update('products', $data, ['id' => $custom_id]);
    } else {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM products WHERE slug = %s LIMIT 1", $item['slug']));
        if ($exists) {
            update_post_meta($post_id, '_bpt_product_id', $exists);
            $wpdb->update('products', $data, ['id' => $exists]);
        } else {
            $wpdb->insert('products', $data);
            $new_id = $wpdb->insert_id;
            if ($new_id) {
                update_post_meta($post_id, '_bpt_product_id', $new_id);
            }
        }
    }
}

// Start PHP Session for guest carts
add_action('init', 'bpt_init_session', 1);
function bpt_init_session() {
    if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}

// Load MVC Model and Controller files
require_once get_template_directory() . '/models/class-bpt-product.php';
require_once get_template_directory() . '/models/class-bpt-cart.php';
require_once get_template_directory() . '/models/class-bpt-vietqr.php';
require_once get_template_directory() . '/controllers/class-bpt-ajax-controller.php';
require_once get_template_directory() . '/controllers/class-bpt-export-controller.php';

// Dynamic routing for custom templates
add_filter('template_include', function($template) {
    // 0. Tour Detail Page Routing
    if ((isset($_GET['pagename']) && $_GET['pagename'] === 'tour-detail') || is_page('tour-detail')) {
        $tour_detail = get_template_directory() . '/page-tour-detail.php';
        if (file_exists($tour_detail)) {
            return $tour_detail;
        }
    }

    if (isset($_GET['pagename']) && $_GET['pagename'] === 'other-services-detail') {
        $custom = get_template_directory() . '/page-other-services-detail.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }

    // 1. Checkout Page Routing (Must be checked first)
    if (
        (isset($_GET['pagename']) && $_GET['pagename'] === 'checkout')
        || (function_exists('is_checkout') && is_checkout())
        || is_page('checkout')
        || (function_exists('wc_get_page_id') && is_page(wc_get_page_id('checkout')))
        || isset($_GET['order_id'])
        || isset($_GET['order-received'])
    ) {
        $checkout_tmpl = get_template_directory() . '/page-checkout.php';
        if (file_exists($checkout_tmpl)) {
            return $checkout_tmpl;
        }
    }

    // 2. Cart Page Routing
    if (
        (isset($_GET['pagename']) && $_GET['pagename'] === 'cart')
        || (function_exists('is_cart') && is_cart())
        || is_page('cart')
        || (function_exists('wc_get_page_id') && is_page(wc_get_page_id('cart')))
    ) {
        $cart_tmpl = get_template_directory() . '/page-cart.php';
        if (file_exists($cart_tmpl)) {
            return $cart_tmpl;
        }
    }

    return $template;
}, 99);

// Format currency helper
if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return number_format((float)$amount, 0, ',', '.') . " VN\xc4\x90";
    }
}

if (!function_exists('bpt_resolve_image_url')) {
    function bpt_resolve_image_url($image_path) {
        if (!$image_path) {
            return 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
        }
        
        if (preg_match('/^https?:\/\//i', $image_path)) {
            return $image_path;
        }
        
        return get_template_directory_uri() . '/' . ltrim($image_path, './');
    }
}



// Ensure custom calculated tour prices are reflected in WooCommerce Cart
add_action('woocommerce_before_calculate_totals', 'bpt_wc_custom_cart_item_price', 10, 1);
function bpt_wc_custom_cart_item_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (did_action('woocommerce_before_calculate_totals') >= 2) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['bpt_total_price']) && floatval($cart_item['bpt_total_price']) > 0) {
            $qty = max(1, intval($cart_item['quantity']));
            $price_per_item = floatval($cart_item['bpt_total_price']) / $qty;
            $cart_item['data']->set_price($price_per_item);
        }
    }
}

// Display Departure Date and breakdown of ticket quantities per guest type
add_filter('woocommerce_get_item_data', 'bpt_wc_display_cart_item_meta', 10, 2);
function bpt_wc_display_cart_item_meta($item_data, $cart_item) {
    if (!empty($cart_item['departure_date'])) {
        $item_data[] = [
            'name'  => __('Departure Date', 'buddyprivatetour'),
            'value' => esc_html($cart_item['departure_date']),
        ];
    }
    if (isset($cart_item['adults']) && intval($cart_item['adults']) > 0) {
        $item_data[] = [
            'name'  => __('Adults', 'buddyprivatetour'),
            'value' => intval($cart_item['adults']) . ' ' . __('ticket(s)', 'buddyprivatetour'),
        ];
    }
    if (isset($cart_item['youths']) && intval($cart_item['youths']) > 0) {
        $item_data[] = [
            'name'  => __('Youths', 'buddyprivatetour'),
            'value' => intval($cart_item['youths']) . ' ' . __('ticket(s)', 'buddyprivatetour'),
        ];
    }
    if (isset($cart_item['children']) && intval($cart_item['children']) > 0) {
        $item_data[] = [
            'name'  => __('Children', 'buddyprivatetour'),
            'value' => intval($cart_item['children']) . ' ' . __('ticket(s)', 'buddyprivatetour'),
        ];
    }
    if (isset($cart_item['infants']) && intval($cart_item['infants']) > 0) {
        $item_data[] = [
            'name'  => __('Infants', 'buddyprivatetour'),
            'value' => intval($cart_item['infants']) . ' ' . __('ticket(s)', 'buddyprivatetour'),
        ];
    }
    return $item_data;
}

// Hide total quantity multiplier (e.g. "x 7") on Checkout Order Review table
add_filter('woocommerce_checkout_cart_item_quantity', function($qty_html, $cart_item, $cart_item_key) {
    return '';
}, 10, 3);

// Hide total quantity multiplier on Order Received & Thank you page order details
add_filter('woocommerce_order_item_quantity_html', function($qty_html, $item) {
    return '';
}, 10, 2);

// Save departure date and individual guest tickets to WooCommerce order line item
add_action('woocommerce_checkout_create_order_line_item', 'bpt_wc_save_order_item_meta', 10, 4);
function bpt_wc_save_order_item_meta($item, $cart_item_key, $values, $order) {
    if (!empty($values['departure_date'])) {
        $item->add_meta_data(__('Departure Date', 'buddyprivatetour'), $values['departure_date'], true);
    }
    if (isset($values['adults']) && intval($values['adults']) > 0) {
        $item->add_meta_data(__('Adults', 'buddyprivatetour'), intval($values['adults']) . ' ' . __('ticket(s)', 'buddyprivatetour'), true);
    }
    if (isset($values['youths']) && intval($values['youths']) > 0) {
        $item->add_meta_data(__('Youths', 'buddyprivatetour'), intval($values['youths']) . ' ' . __('ticket(s)', 'buddyprivatetour'), true);
    }
    if (isset($values['children']) && intval($values['children']) > 0) {
        $item->add_meta_data(__('Children', 'buddyprivatetour'), intval($values['children']) . ' ' . __('ticket(s)', 'buddyprivatetour'), true);
    }
    if (isset($values['infants']) && intval($values['infants']) > 0) {
        $item->add_meta_data(__('Infants', 'buddyprivatetour'), intval($values['infants']) . ' ' . __('ticket(s)', 'buddyprivatetour'), true);
    }
}
