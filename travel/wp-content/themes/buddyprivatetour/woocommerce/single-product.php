<?php
/**
 * WooCommerce Single Product Template Override
 * Renders products using the Buddy Private Tour custom detail layout
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load custom Tour Detail layout
require get_template_directory() . '/page-tour-detail.php';
