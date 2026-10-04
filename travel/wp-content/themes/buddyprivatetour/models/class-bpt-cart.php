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
        foreach ($cart_rows as $row) {
            $product = BPT_ProductModel::findById($row['product_id']);
            if ($product) {
                $item = $product;
                $item['quantity'] = intval($row['quantity']);
                $item['cart_row_id'] = $row['id'];
                $item['cart_product_id'] = $row['product_id'];
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
