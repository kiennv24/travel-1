<?php
/**
 * Cart Database Model & Helper functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BPT_CartModel {
    public static function count() {
        $items = self::getItems();
        return count($items);
    }

    public static function getItems() {
        global $wpdb;
        if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $session_id = session_id();
        
        $cart_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM cart WHERE session_id = %s ORDER BY created_at ASC",
            $session_id
        ), ARRAY_A);

        if (empty($cart_rows)) {
            return [];
        }

        $items = [];
        $meta_data = isset($_SESSION['bpt_cart_meta']) ? $_SESSION['bpt_cart_meta'] : [];
        foreach ($cart_rows as $row) {
            $product = BPT_ProductModel::findById($row['product_id']);
            if ($product) {
                $item = $product;
                $item['quantity'] = intval($row['quantity']);
                $item['cart_row_id'] = $row['id'];
                $item['cart_product_id'] = $row['product_id'];

                $pMeta = isset($meta_data[$product['id']]) ? $meta_data[$product['id']] : [];
                $item['departure_date'] = !empty($row['departure_date']) ? $row['departure_date'] : ($pMeta['departure_date'] ?? date('Y-m-d'));
                $item['adults'] = isset($row['adults']) && intval($row['adults']) > 0 ? intval($row['adults']) : ($pMeta['adults'] ?? intval($row['quantity']));
                $item['youths'] = isset($row['youths']) ? intval($row['youths']) : ($pMeta['youths'] ?? 0);
                $item['children'] = isset($row['children']) ? intval($row['children']) : ($pMeta['children'] ?? 0);
                $item['infants'] = isset($row['infants']) ? intval($row['infants']) : ($pMeta['infants'] ?? 0);
                $item['total_price'] = isset($row['total_price']) && floatval($row['total_price']) > 0 ? floatval($row['total_price']) : ($pMeta['total_price'] ?? (floatval($product['price']) * intval($row['quantity'])));

                $items[] = $item;
            }
        }
        return $items;
    }
}

// Global helper wrappers for Views
if (!function_exists('bpt_db_cart_count')) {
    function bpt_db_cart_count() {
        return BPT_CartModel::count();
    }
}

if (!function_exists('bpt_db_cart_get_items')) {
    function bpt_db_cart_get_items() {
        return BPT_CartModel::getItems();
    }
}
