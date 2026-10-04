<?php
/**
 * VietQR Payment Gateway Integration Model for Buddy Private Tour
 * Connects with WooCommerce VietQR Plugin & VietQR QuickPay API
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BPT_VietQR {
    /**
     * Get VietQR Account and Gateway Settings
     */
    public static function getConfig() {
        $settings = get_option('woocommerce_vietqr_settings', []);
        $accounts = get_option('woocommerce_vietqr_accounts', []);
        
        $primary_account = !empty($accounts) && is_array($accounts) ? $accounts[0] : [];
        
        $bank_id = $primary_account['bank_id'] ?? '970436'; // Default Vietcombank
        $bank_name = $primary_account['bank_name'] ?? 'Vietcombank';
        $account_number = $primary_account['account_number'] ?? '9782794280';
        $account_name = $primary_account['account_name'] ?? 'NGUYEN VAN KIEN';
        $prefix = $settings['prefix'] ?? 'BPT';

        return [
            'enabled'        => ($settings['enabled'] ?? 'yes') === 'yes',
            'title'          => $settings['title'] ?? 'Chuyển khoản ngân hàng (Quét mã VietQR)',
            'description'    => $settings['description'] ?? 'Quét mã VietQR bằng ứng dụng mọi ngân hàng (Vietcombank, MBBank, Techcombank, BIDV, ACB, VietinBank...) để thanh toán tự động.',
            'bank_id'        => $bank_id,
            'bank_name'      => $bank_name,
            'account_number' => $account_number,
            'account_name'   => $account_name,
            'prefix'         => $prefix,
        ];
    }

    /**
     * Generate VietQR Image URL
     * Template: compact2, compact, qr_only, print
     */
    public static function generateQrUrl($order_code, $amount, $template = 'compact2') {
        $config = self::getConfig();
        $bank_id = urlencode($config['bank_id']);
        $account_number = urlencode($config['account_number']);
        $account_name = urlencode($config['account_name']);
        $add_info = urlencode($order_code);
        $amount_int = (int)round($amount);

        return "https://img.vietqr.io/image/{$bank_id}-{$account_number}-{$template}.png?amount={$amount_int}&addInfo={$add_info}&accountName={$account_name}";
    }

    /**
     * Ensure database columns exist in orders table
     */
    public static function ensureTableColumns() {
        global $wpdb;
        $table_name = 'orders';
        $columns = $wpdb->get_results("SHOW COLUMNS FROM `$table_name`", ARRAY_A);
        if (empty($columns)) return;

        $existing_cols = array_column($columns, 'Field');
        if (!in_array('payment_method', $existing_cols)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `payment_method` varchar(50) DEFAULT 'vietqr'");
        }
        if (!in_array('payment_status', $existing_cols)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `payment_status` varchar(50) DEFAULT 'pending'");
        }
        if (!in_array('transaction_id', $existing_cols)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `transaction_id` varchar(100) DEFAULT ''");
        }
    }
}
