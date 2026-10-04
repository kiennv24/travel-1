<?php
/**
 * The base configuration for WordPress
 *
 * @package WordPress
 */

define( 'DB_NAME', 'db_travel' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );


define( 'DISALLOW_FILE_MODS', false );

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

define( 'AUTH_KEY',         'b!P.vB0rS9u#V@tK$yZ%qX^wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'SECURE_AUTH_KEY',  'c#Q/wC1sT:v$WAtL%yZ^qX&wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'LOGGED_IN_KEY',    'd$R/xD2tU;w%XBtM&yZ^qX*wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'NONCE_KEY',        'e%S/yE3tV<x^YBtN*yZ^qX(wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'AUTH_SALT',        'f^T/zF4tW>y_ZBtO+yZ^qX)wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'SECURE_AUTH_SALT', 'g_U/aG5tX~z_aBtP_yZ^qX-wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'LOGGED_IN_SALT',   'h-V/bH6tY`a-bBtQ=yZ^qX_wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );
define( 'NONCE_SALT',       'i=W/cI7tZ+b=cBtR+yZ^qX+wN*eM(rT)yU_iO+pP{aS}dD[fF]gG|hH;jJ:kK<lL>zZ' );

$table_prefix = 'wp_';

// Dynamic WP_HOME and WP_SITEURL to support accessing from subdirectories/different machines
if (isset($_SERVER['HTTP_HOST'])) {
    $http_protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $http_host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $subfolder = dirname($script_name);
    $subfolder = str_replace('\\', '/', $subfolder);
    $subfolder = preg_replace('/\/(wp-admin|wp-content|wp-includes).*$/i', '', $subfolder);
    $subfolder = rtrim($subfolder, '/');
    
    $dynamic_url = $http_protocol . '://' . $http_host . $subfolder;
    define('WP_HOME', $dynamic_url);
    define('WP_SITEURL', $dynamic_url);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
