<?php
/**
 * Plugin Name:       Imagero - Auto Image Optimizer & Watermark with R2 Offload
 * Plugin URI:        https://wpnovate.com/imagero
 * Description:       Automatically resize, convert to WebP, and watermark uploaded images. Optionally offload to Cloudflare R2.
 * Version:           1.0.0
 * Author:            WPNovate
 * Author URI:        https://wpnovate.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       imagero
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IMAGERO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMAGERO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IMAGERO_VERSION', '1.0.0' );

// Include necessary files
require_once IMAGERO_PLUGIN_DIR . 'includes/admin-page.php';
require_once IMAGERO_PLUGIN_DIR . 'includes/image-processor.php';
require_once IMAGERO_PLUGIN_DIR . 'includes/r2-uploader.php';

// Set default options on activation
function imagero_activate_plugin() {
    if ( false === get_option( 'imagero_max_width' ) ) {
        update_option( 'imagero_max_width', 1920 );
    }
    if ( false === get_option( 'imagero_max_height' ) ) {
        update_option( 'imagero_max_height', 1080 );
    }
    if ( false === get_option( 'imagero_quality' ) ) {
        update_option( 'imagero_quality', 80 );
    }
    if ( false === get_option( 'imagero_watermark_opacity' ) ) {
        update_option( 'imagero_watermark_opacity', 50 );
    }
    if ( false === get_option( 'imagero_watermark_color' ) ) {
        update_option( 'imagero_watermark_color', '#ffffff' );
    }
    add_option( 'imagero_activation_redirect', true );
}
register_activation_hook( __FILE__, 'imagero_activate_plugin' );

function imagero_redirect_on_activation() {
    if ( get_option( 'imagero_activation_redirect', false ) ) {
        delete_option( 'imagero_activation_redirect' );
        if ( ! headers_sent() ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=imagero-optimizer' ) );
            exit;
        }
    }
}
add_action( 'admin_init', 'imagero_redirect_on_activation' );

function imagero_enqueue_admin_assets( $hook ) {
    if ( 'settings_page_imagero-optimizer' !== $hook ) {
        return;
    }

    // Enqueue Tailwind CSS (Local)
    wp_enqueue_script( 'tailwindcss', IMAGERO_PLUGIN_URL . 'assets/js/tailwind.js', [], IMAGERO_VERSION, false );
    
    // Configure Tailwind Theme
    wp_add_inline_script( 'tailwindcss', "
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2271b1',
                        danger: '#d63638',
                    }
                }
            }
        }
    " );

    // Enqueue Custom Assets
    wp_enqueue_style( 'imagero-admin-css', IMAGERO_PLUGIN_URL . 'assets/css/admin.css', [], IMAGERO_VERSION );
    wp_enqueue_script( 'imagero-admin-js', IMAGERO_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'wp-color-picker'], IMAGERO_VERSION, true );
    
    // Enqueue WP Color Picker
    wp_enqueue_style( 'wp-color-picker' );
    
    // Localize Script for AJAX
    wp_localize_script( 'imagero-admin-js', 'imagero_ajax', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'imagero_save_settings_nonce' )
    ]);
}
add_action( 'admin_enqueue_scripts', 'imagero_enqueue_admin_assets' );

// AJAX Handler for Saving Settings
function imagero_save_settings_ajax() {
    check_ajax_referer( 'imagero_save_settings_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }

    $fields = [
        'imagero_max_width', 'imagero_max_height', 'imagero_webp_enabled', 'imagero_quality',
        'imagero_watermark_enabled', 'imagero_watermark_type', 'imagero_watermark_content',
        'imagero_watermark_position', 'imagero_watermark_opacity', 'imagero_watermark_color',
        'imagero_r2_enabled', 'imagero_r2_endpoint', 'imagero_r2_access_key', 
        'imagero_r2_secret_key', 'imagero_r2_bucket', 'imagero_r2_public_domain'
    ];

    foreach ( $fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            $value = sanitize_text_field( wp_unslash( $_POST[$field] ) );
            update_option( $field, $value );
        } else {
            // Handle checkboxes that are unchecked
            if ( in_array( $field, ['imagero_webp_enabled', 'imagero_watermark_enabled', 'imagero_r2_enabled'] ) ) {
                update_option( $field, 0 );
            }
        }
    }

    wp_send_json_success( 'Settings saved successfully!' );
}
add_action( 'wp_ajax_imagero_save_settings', 'imagero_save_settings_ajax' );
