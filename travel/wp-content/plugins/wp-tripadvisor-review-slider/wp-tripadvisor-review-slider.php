<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://ljapps.com
 * @since             1.0
 * @package           WP_TripAdvisor_Review_Slider
 *
 * @wordpress-plugin
 * Plugin Name: 	  WP TripAdvisor Review Slider
 * Plugin URI:        https://wpreviewslider.com/
 * Description:       Display TripAdvisor reviews in a slider or grid. Download multiple locations, store reviews locally, and show badges, photos, and templates.
 * Version:           15.2
 * Author:            LJ Apps
 * Author URI:        http://ljapps.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-tripadvisor-review-slider
 * Domain Path:       /languages
 */

if ( ! function_exists( 'wtrs_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wtrs_fs() {
        global $wtrs_fs;

        if ( ! isset( $wtrs_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wtrs_fs = fs_dynamic_init( array(
                'id'                  => '11163',
                'slug'                => 'wp-tripadvisor-review-slider',
                'type'                => 'plugin',
                'public_key'          => 'pk_36d3e7ca1577de4f9b28d1f0ca72b',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'wp_tripadvisor-welcome',
                ),
            ) );
        }

        return $wtrs_fs;
    }

    // Init Freemius.
    wtrs_fs();
    // Signal that SDK was initiated.
    do_action( 'wtrs_fs_loaded' );
}


// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-tripadvisor-review-slider-activator.php
 */
function activate_WP_TripAdvisor_Review( $networkwide )
{
//save time activated
	$newtime=time();
	update_option( 'wprev_activated_time_trip', $newtime );
	
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-tripadvisor-review-slider-activator.php';
    WP_TripAdvisor_Review_Activator::activate_all( $networkwide );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-tripadvisor-review-slider-deactivator.php
 */
function deactivate_WP_TripAdvisor_Review()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-tripadvisor-review-slider-deactivator.php';
    WP_TripAdvisor_Review_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WP_TripAdvisor_Review' );
register_deactivation_hook( __FILE__, 'deactivate_WP_TripAdvisor_Review' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-tripadvisor-review-slider.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_WP_TripAdvisor_Review()
{
    //define plugin location constant

    define( 'wprev_trip_plugin_dir', plugin_dir_path( __FILE__ ) );
    define( 'wprev_trip_plugin_url', plugins_url( '', __FILE__ ) );


    $plugin = new WP_TripAdvisor_Review();
    $plugin->run();
}

//for running the cron job
//add_action('wptripadvisor_daily_event', 'wptripadvisor_do_this_daily');
/*
function wptripadvisor_do_this_daily() {
		
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-wp-tripadvisor-review-slider-admin.php';
	$plugin_admin = new WP_TripAdvisor_Review_Admin( 'wp-tripadvisor-review-slider', '10.9' );
	$plugin_admin->wptripadvisor_download_tripadvisor_master();
	
}
*/

//add link togo pro on plugins menu
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wprevtrip_action_links' );
function wprevtrip_action_links( $links )
{
    $links[] = '<a href="https://wpreviewslider.com/" target="_blank"><strong style="color: #009040; display: inline;">Go Pro!</strong></a>';
    return $links;
}

//start the plugin-------------
run_WP_TripAdvisor_Review();