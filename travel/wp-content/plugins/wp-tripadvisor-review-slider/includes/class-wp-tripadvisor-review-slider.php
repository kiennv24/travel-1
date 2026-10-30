<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://ljapps.com
 * @since      1.0.0
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/includes
 * @author     Josh <landjllc@gmail.com>
 */
class WP_TripAdvisor_Review {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_TripAdvisor_Review_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugintoken    The string used to uniquely identify this plugin.
	 */
	protected $plugintoken;
	protected $_token;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->_token = 'wp-tripadvisor-review-slider';
		// 15.0: Badge uses the source average, stars, and total saved per location.
		$this->version = '15.2';
		//using this for development
		//$this->version = time();

		$this->load_dependencies();
		$this->set_locale();
		
		if (is_admin()) {
			$this->define_admin_hooks();
		}
		$this->define_public_hooks();
		//save version number to db
		$this->_log_version_number();

	}
	
	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		
		$current_version = get_option($this->_token . '_current_db_version', 0);
		
		if($current_version!=$this->version){
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
			// Create new tables as utf8mb4 so emoji in review text can be stored.
			$has_utf8mb4 = $wpdb->get_var( "SHOW CHARACTER SET LIKE 'utf8mb4'" ) !== null;
			if ( $has_utf8mb4 ) {
				$charset_collate = 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
			} else {
				$charset_collate = $wpdb->get_charset_collate();
			}
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				pageid varchar(250) DEFAULT '' NOT NULL,
				pagename tinytext NOT NULL,
				created_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				created_time_stamp int(12) NOT NULL,
				reviewer_name tinytext NOT NULL,
				reviewer_id varchar(50) DEFAULT '' NOT NULL,
				rating int(2) NOT NULL,
				review_text text NOT NULL,
				hide varchar(3) DEFAULT '' NOT NULL,
				review_length int(5) NOT NULL,
				type varchar(12) DEFAULT '' NOT NULL,
				userpic varchar(250) DEFAULT '' NOT NULL,
				review_title varchar(500) DEFAULT '' NOT NULL,
				mediaurlsarrayjson text NOT NULL,
				mediathumburlsarrayjson text NOT NULL,
				from_url varchar(500) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id),
				PRIMARY KEY (id)
			) $charset_collate;";
			dbDelta( $sql );

			// Convert a legacy 3-byte reviews table to utf8mb4 so emoji can be stored.
			self::wptripadvisor_maybe_convert_table_to_utf8mb4( $this->version );
			
			//create template posts table in dbDelta 
			$table_name = $wpdb->prefix . 'wptripadvisor_post_templates';
			
			$sql_template = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				template_type varchar(7) DEFAULT '' NOT NULL,
				style int(2) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				display_num int(2) NOT NULL,
				display_num_rows int(3) NOT NULL,
				display_order varchar(6) DEFAULT '' NOT NULL,
				hide_no_text varchar(3) DEFAULT '' NOT NULL,
				template_css text NOT NULL,
				min_rating int(2) NOT NULL,
				min_words int(4) NOT NULL,
				max_words int(4) NOT NULL,
				rtype varchar(25) DEFAULT '' NOT NULL,
				rpage varchar(200) DEFAULT '' NOT NULL,
				createslider varchar(3) DEFAULT '' NOT NULL,
				numslides int(2) NOT NULL,
				sliderautoplay varchar(3) DEFAULT '' NOT NULL,
				sliderdirection varchar(12) DEFAULT '' NOT NULL,
				sliderarrows varchar(3) DEFAULT '' NOT NULL,
				sliderdots varchar(3) DEFAULT '' NOT NULL,
				sliderdelay int(2) NOT NULL,
				sliderheight varchar(3) DEFAULT '' NOT NULL,
				slidermobileview varchar(5) DEFAULT '' NOT NULL,
				showreviewsbyid varchar(600) DEFAULT '' NOT NULL,
				template_misc text NOT NULL,
				read_more varchar(3) DEFAULT '' NOT NULL,
				read_more_num int(4) NOT NULL,
				read_more_text varchar(50) DEFAULT '' NOT NULL,
				facebook_icon varchar(3) DEFAULT '' NOT NULL,
				review_same_height varchar(3) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id),
				PRIMARY KEY (id)
			) $charset_collate;";
			
			dbDelta( $sql_template );

			$table_name_totalavg = $wpdb->prefix . 'wptripadvisor_total_averages';
			$sql_totalavg = "CREATE TABLE $table_name_totalavg (
				btp_id varchar(150) DEFAULT '' NOT NULL,
				btp_name varchar(150) DEFAULT '' NOT NULL,
				btp_type varchar(10) DEFAULT '' NOT NULL,
				pagetype varchar(100) DEFAULT '' NOT NULL,
				pagetypedetails text NOT NULL,
				total_indb varchar(10) DEFAULT '' NOT NULL,
				total varchar(10) DEFAULT '' NOT NULL,
				avg_indb varchar(10) DEFAULT '' NOT NULL,
				avg varchar(10) DEFAULT '' NOT NULL,
				numr1 varchar(10) DEFAULT '' NOT NULL,
				numr2 varchar(10) DEFAULT '' NOT NULL,
				numr3 varchar(10) DEFAULT '' NOT NULL,
				numr4 varchar(10) DEFAULT '' NOT NULL,
				numr5 varchar(10) DEFAULT '' NOT NULL,
				UNIQUE KEY id (btp_id),
				PRIMARY KEY (btp_id)
			) $charset_collate;";
			dbDelta( $sql_totalavg );

			// Backfill pageid/pagename on legacy reviews that only have from_url.
			$reviews_table = $wpdb->prefix . 'wptripadvisor_reviews';
			$legacy_rows = $wpdb->get_results( "SELECT id, from_url FROM {$reviews_table} WHERE (pageid = '' OR pageid IS NULL) AND from_url != '' AND type = 'TripAdvisor' LIMIT 500" );
			if ( is_array( $legacy_rows ) ) {
				foreach ( $legacy_rows as $legacy_row ) {
					$pid   = '';
					$pname = '';
					if ( preg_match( '/-d(\d+)/i', $legacy_row->from_url, $m ) ) {
						$pid = 'd' . $m[1];
					}
					if ( preg_match( '/-Reviews-([A-Za-z0-9_]+)/', $legacy_row->from_url, $m2 ) ) {
						$pname = str_replace( '_', ' ', $m2[1] );
					}
					if ( $pid !== '' ) {
						$wpdb->update(
							$reviews_table,
							array(
								'pageid'   => $pid,
								'pagename' => $pname,
							),
							array( 'id' => $legacy_row->id ),
							array( '%s', '%s' ),
							array( '%d' )
						);
					}
				}
			}
			
		}

		update_option( $this->_token . '_current_db_version', $this->version );
		update_option( $this->_token . '_version', $this->version );
		
	} // End _log_version_number ()

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_TripAdvisor_Review_Loader. Orchestrates the hooks of the plugin.
	 * - WP_TripAdvisor_Review_i18n. Defines internationalization functionality.
	 * - WP_TripAdvisor_Review_Admin. Defines all hooks for the admin area.
	 * - WP_TripAdvisor_Review_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-tripadvisor-review-slider-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-tripadvisor-review-slider-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-tripadvisor-review-slider-admin.php';

		/**
		 * Analytics AJAX handlers.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-tripadvisor-review-slider-analytics.php';

		/**
		 * Sample AI Analysis AJAX handlers (Pro teaser).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-tripadvisor-review-slider-ai.php';

		/**
		 * Shared sanitization helpers for template CSS output.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-tripadvisor-review-slider-sanitize.php';
		
		/**
		 * The class responsible for parsing yelp and tripadvisor pages
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wptrip_simple_html_dom.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-tripadvisor-review-slider-public.php';
		
		/**
		 * The class responsible for the widget admin and public
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-tripadvisor-review-slider-widget.php';
		
		/**
		 * The class responsible for displaying review template via do_action in template file
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-tripadvisor-review-slider-template_action.php';

		//register the loader
		$this->loader = new WP_TripAdvisor_Review_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_TripAdvisor_Review_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_TripAdvisor_Review_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_TripAdvisor_Review_Admin( $this->get_token(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// register our wptripadvisor_settings_init to the admin_init action hook, add setting inputs
		//$this->loader->add_action('admin_init', $plugin_admin, 'wptripadvisor_settings_init');
		
		// register our wptripadvisor_tripadvisor_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wptripadvisor_tripadvisor_settings_init');

		//add menu page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_pages' );
		
		//add ajax for adding feedback to table
		$this->loader->add_action( 'wp_ajax_tripadvisor_get_results', $plugin_admin, 'wptripadvisor_process_ajax' );

		//add ajax for hiding and deleting reviews in table
		$this->loader->add_action( 'wp_ajax_tripadvisor_hide_review', $plugin_admin, 'wptripadvisor_hidereview_ajax' ); 

		// Review List edit popup: save avatar URL + date without reloading the page.
		$this->loader->add_action( 'wp_ajax_wptripadvisor_save_review', $plugin_admin, 'wptripadvisor_savereview_ajax' );

		//add ajax for hiding and deleting reviews in table
		$this->loader->add_action( 'wp_ajax_tripadvisor_find_reviews', $plugin_admin, 'wptripadvisor_getreviews_ajax' );

		// Template editor AJAX: save + live preview.
		$this->loader->add_action( 'wp_ajax_wptripadvisor_save_template', $plugin_admin, 'wptripadvisor_savetemplate_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_get_preview', $plugin_admin, 'wptripadvisor_previewtemplate_ajax' );

		// Get TripAdvisor Reviews: multi-source add + per-source download.
		$this->loader->add_action( 'wp_ajax_wptripadvisor_add_source', $plugin_admin, 'wptripadvisor_ajax_add_source' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_download_source', $plugin_admin, 'wptripadvisor_ajax_download_source' );

		//add download csv file function wptripadvisor_download_csv
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'wptripadvisor_download_csv' ); 
		
	
		//replaces insert into post text on media uploader when uploading reviewer avatar
		$this->loader->add_action( 'admin_init', $plugin_admin, 'wptripadvisor_media_text' );
		
		//add download csv file function wptripadvisor_download_csv
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'wptripadvisor_download_tripadvisor' ); 
		
		//cron for refreshing tripadvisor
		//$this->loader->add_action('wptripadvisor_daily_event', $plugin_admin, 'wptripadvisor_do_this_daily');
		
		//for displaying leave review admin notice
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'wprp_admin_notice__success' ); 
		
		//dashboard widget to show newest reviews
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'wprevtrip_dashboard_widget' );
		
		//add custom link to menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wprev_trip_add_external_link_admin_submenu' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'wpse_66023_add_jquery' );

		// Analytics page AJAX
		$plugin_analytics = new WP_TripAdvisor_Review_Analytics();
		$this->loader->add_action( 'wp_ajax_wppro_get_overall_chart_data', $plugin_analytics, 'wppro_get_overall_chart_data' );
		$this->loader->add_action( 'wp_ajax_wprevpro_analytics_volume', $plugin_analytics, 'wprevpro_ajax_analytics_volume' );
		$this->loader->add_action( 'wp_ajax_wprevpro_analytics_platform', $plugin_analytics, 'wprevpro_ajax_analytics_platform' );
		$this->loader->add_action( 'wp_ajax_wprevpro_analytics_platform_volume', $plugin_analytics, 'wprevpro_ajax_analytics_platform_volume' );
		$this->loader->add_action( 'wp_ajax_wprevpro_analytics_rating_trends', $plugin_analytics, 'wprevpro_ajax_analytics_rating_trends' );

		// Sample AI Analysis AJAX
		$plugin_ai = new WP_TripAdvisor_Review_AI();
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_get_latest_report', $plugin_ai, 'wprevpro_ai_get_latest_report_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_get_report', $plugin_ai, 'wprevpro_ai_get_report_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_list_reports', $plugin_ai, 'wprevpro_ai_list_reports_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_filters_options', $plugin_ai, 'wprevpro_ai_filters_options_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_delete_report', $plugin_ai, 'wprevpro_ai_delete_report_ajax' );
		$this->loader->add_action( 'wp_ajax_wptripadvisor_ai_reviews_by_date', $plugin_ai, 'wprevpro_ai_reviews_by_date_ajax' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_TripAdvisor_Review_Public( $this->get_token(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//add shortcode shortcode_wptripadvisor_usetemplate
		$plugin_public->shortcode_wptripadvisor_usetemplate();
		
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	/**
	 * Convert an existing 3-byte (utf8/utf8mb3) reviews table to utf8mb4 so emoji can be saved.
	 *
	 * dbDelta() never changes the charset of an existing table, so sites whose table was
	 * created as utf8 reject inserts containing 4-byte characters such as emoji.
	 * Skips tables with non-utf8 columns, very large tables, or sites whose database
	 * connection is not utf8mb4. When a version is passed (plugin update path) the
	 * conversion is attempted at most once per version; activation passes nothing so a
	 * manual deactivate/reactivate always retries.
	 *
	 * @param string $version Optional. Current plugin version, used to limit attempts.
	 * @return bool True if the table is utf8mb4 after this call.
	 */
	public static function wptripadvisor_maybe_convert_table_to_utf8mb4( $version = '' ) {
		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			return false;
		}

		$table = $wpdb->prefix . 'wptripadvisor_reviews';

		$suppress = $wpdb->suppress_errors();
		$columns  = $wpdb->get_results( "SHOW FULL COLUMNS FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->suppress_errors( $suppress );
		$wpdb->last_error = '';

		if ( empty( $columns ) ) {
			return false;
		}

		$three_byte    = array( 'utf8', 'utf8mb3' );
		$needs_convert = false;
		foreach ( $columns as $column ) {
			if ( empty( $column->Collation ) ) {
				continue; // numeric / blob column
			}
			list( $charset ) = explode( '_', $column->Collation );
			$charset = strtolower( $charset );
			if ( 'utf8mb4' === $charset ) {
				continue;
			}
			if ( ! in_array( $charset, $three_byte, true ) ) {
				// latin1 or other: converting could scramble stored text.
				return false;
			}
			$needs_convert = true;
		}

		if ( ! $needs_convert ) {
			return true;
		}

		// Skip very large tables; the rebuild could outlast the request. Handle those manually.
		$status = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) );
		if ( $status ) {
			$bytes = (int) $status->Data_length + (int) $status->Index_length;
			if ( $bytes > 256 * 1024 * 1024 ) {
				return false;
			}
		}

		// Only try once per plugin version so a killed request does not retry on every admin load.
		if ( '' !== (string) $version ) {
			if ( get_option( 'wptripadvisor_reviews_utf8mb4_attempted', '' ) === (string) $version ) {
				return false;
			}
			update_option( 'wptripadvisor_reviews_utf8mb4_attempted', (string) $version, false );
		}

		$suppress = $wpdb->suppress_errors();
		$result   = $wpdb->query( "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->suppress_errors( $suppress );
		$wpdb->last_error = '';

		return false !== $result;
	}

	public function run() {
	
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_token() {
		return $this->_token;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_TripAdvisor_Review_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
