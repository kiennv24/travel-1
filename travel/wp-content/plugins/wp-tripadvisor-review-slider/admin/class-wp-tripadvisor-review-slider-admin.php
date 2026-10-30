<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hookfs for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/admin
 * @author     Your Name <email@example.com>
 */
class WP_TripAdvisor_Review_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;
	private $_token;
	public $errormsg;
	/**
	 * Last crawl server payload (temporary debug for Download Reviews).
	 *
	 * @var array|null
	 */
	public $last_crawl_debug = null;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		//$this->version = $version;
		//for testing==============
		$this->version = time();
		//===================
				

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_TripAdvisor_Review_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_TripAdvisor_Review_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//only load for this plugin pages (wp_tripadvisor-*)
		if(isset($_GET['page'])){
			if( strpos( $_GET['page'], 'wp_tripadvisor-' ) !== false ){

			wp_register_style( 'Font_Awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
			wp_enqueue_style('Font_Awesome');
				
				wp_enqueue_style( $this->_token."_wprev_w3", plugin_dir_url( __FILE__ ) . 'css/wprev_w3.css', array(), $this->version, 'all' );

			wp_enqueue_style( $this->_token, plugin_dir_url( __FILE__ ) . 'css/wptripadvisor_admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->_token . '_wprev_admin', plugin_dir_url( __FILE__ ) . 'css/wprev_admin.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'css/wprev_admin.css' ), 'all' );
			wp_enqueue_style( $this->_token."_wptripadvisor_w3", plugin_dir_url( __FILE__ ) . 'css/wptripadvisor_w3.css', array(), $this->version, 'all' );
			}
			//load template styles for wp_tripadvisor-templates_posts page
			if($_GET['page']=="wp_tripadvisor-templates_posts"|| $_GET['page']=="wp_tripadvisor-get_pro" || $_GET['page']=="wp_tripadvisor-welcome"){
				// Same combined public CSS as the front end (template styles, media thumbs, unslider arrows/dots).
				wp_enqueue_style( $this->_token."_allcss", plugin_dir_url(dirname(__FILE__)) . 'public/css/wptripadvisor_all.css', array(), $this->version, 'all' );
				if ( $_GET['page'] == 'wp_tripadvisor-templates_posts' ) {
					wp_enqueue_style( $this->_token . '_pro_style_preview', plugin_dir_url( __FILE__ ) . 'css/wprev_pro_style_preview.css', array( $this->_token . '_allcss' ), filemtime( plugin_dir_path( __FILE__ ) . 'css/wprev_pro_style_preview.css' ), 'all' );
				}
				// Lity for review media lightbox in template preview + AI Summary thumbs.
				wp_enqueue_style( $this->_token."_lity", plugin_dir_url(dirname(__FILE__)) . 'public/css/lity.min.css', array(), $this->version, 'all' );
			}
			
					//review list
			if($_GET['page']=="wp_tripadvisor-reviews"){
				wp_enqueue_style( $this->_token."lity_min", plugin_dir_url( __FILE__ ) . 'css/lity.min.css', array(), $this->version, 'all' );
			}

			// Star SVG styles for Analytics summary boxes
			if ( $_GET['page'] === 'wp_tripadvisor-analytics' ) {
				wp_enqueue_style( $this->_token . '_allcss', plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/wptripadvisor_all.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'chart-min', plugin_dir_url( __FILE__ ) . 'css/Chart.min.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token . '_daterangepicker', plugin_dir_url( __FILE__ ) . 'css/daterangepicker.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token . '_select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token . '_jqcloud', plugin_dir_url( __FILE__ ) . 'css/jqcloud.css', array(), $this->version, 'all' );
			}

			// AI Analysis sample page
			if ( $_GET['page'] === 'wp_tripadvisor-ai_analysis' ) {
				wp_enqueue_style( 'chart-min', plugin_dir_url( __FILE__ ) . 'css/Chart.min.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token . '_allcss', plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/wptripadvisor_all.css', array(), $this->version, 'all' );
			}
		
		}
		


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_TripAdvisor_Review_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_TripAdvisor_Review_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		

		//scripts for all pages in this plugin
		if(isset($_GET['page'])){
			if($_GET['page']=="wp_tripadvisor-reviews" || $_GET['page']=="wp_tripadvisor-templates_posts" || $_GET['page']=="wp_tripadvisor-get_tripadvisor" || $_GET['page']=="wp_tripadvisor-get_pro"){
				//pop-up script
				wp_register_script( 'simple-popup-js',  plugin_dir_url( __FILE__ ) . 'js/wptripadvisor_simple-popup.min.js' , '', $this->version, false );
				wp_enqueue_script( 'simple-popup-js' );
				
			}
		}

		
	
		
		if(isset($_GET['page'])){
			if($_GET['page']=="wp_tripadvisor-get_tripadvisor"){
				wp_enqueue_script('wptripadvisor_get_tripadvisor-js', plugin_dir_url( __FILE__ ) . 'js/wptripadvisor_get_tripadvisor.js', array( 'jquery','media-upload','thickbox' ), $this->version, false );
				//used for ajax
				wp_localize_script('wptripadvisor_get_tripadvisor-js', 'adminjs_script_vars', 
					array(
					'wptripadvisor_nonce'=> wp_create_nonce('randomnoncestring')
					)
				);
			}

			

			//scripts for review list page
			if($_GET['page']=="wp_tripadvisor-reviews"){
				//admin js
				// Depend only on jQuery. thickbox/media-upload are enqueued separately below
				// for the avatar uploader; declaring them as hard dependencies here risks
				// WordPress silently dropping this script if either handle isn't registered,
				// which would break the edit popup, hide, and delete AJAX handlers.
				wp_enqueue_script('wptripadvisor_review_list_page-js', plugin_dir_url( __FILE__ ) . 'js/wptripadvisor_review_list_page.js', array( 'jquery' ), $this->version, false );

				global $wpdb;
				$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
				$pagenamearray = $wpdb->get_col( "SELECT pagename FROM {$reviews_table_name} WHERE pagename != '' GROUP BY pagename" );
				if ( ! is_array( $pagenamearray ) ) {
					$pagenamearray = array();
				}

				//used for ajax
				wp_localize_script('wptripadvisor_review_list_page-js', 'adminjs_script_vars', 
					array(
					'wptripadvisor_nonce'=> wp_create_nonce('randomnoncestring'),
					'pagenamearray' => wp_json_encode( $pagenamearray ),
					)
				);
				
				//for media popup
				wp_enqueue_script('wptrip_lity-js', plugin_dir_url( __FILE__ ) . 'js/lity.min.js', array( 'jquery' ), $this->version, false );
				
 				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
		 
				wp_enqueue_script('media-upload');
				wp_enqueue_script('wptuts-upload');

			}
			
			//scripts for templates posts page
			if($_GET['page']=="wp_tripadvisor-templates_posts"){
			
				//admin js
				wp_enqueue_script('wptripadvisor_templates_posts_page-js', plugin_dir_url( __FILE__ ) . 'js/wptripadvisor_templates_posts_page.js', array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'js/wptripadvisor_templates_posts_page.js' ), false );
				//used for ajax
				wp_localize_script('wptripadvisor_templates_posts_page-js', 'adminjs_script_vars', 
					array(
					'wptripadvisor_nonce'=> wp_create_nonce('randomnoncestring'),
					'pluginsUrl' => wprev_trip_plugin_url
					)
				);
 				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
				
				//add color picker here
				wp_enqueue_style( 'wp-color-picker' );
				//enque alpha color add-on wptripadvisor-wp-color-picker-alpha.js
				wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wptripadvisor-wp-color-picker-alpha.js', array( 'wp-color-picker' ), '2.1.2', false );
				
				//add public script for preview
	
				wp_enqueue_script( $this->_token."_unslider-min", plugin_dir_url(dirname(__FILE__)) . 'public/js/wprs-unslider-swipe.js', array( 'jquery' ), $this->version, false );
				wp_enqueue_script( $this->_token."_lity", plugin_dir_url(dirname(__FILE__)) . 'public/js/lity.min.js', array( 'jquery' ), $this->version, false );
				wp_enqueue_script( $this->_token."_plublic", plugin_dir_url(dirname(__FILE__)) . 'public/js/wprev-public.js', array( 'jquery' ), $this->version, false );
				wp_localize_script($this->_token."_plublic", 'wprevpublicjs_script_vars', array(
					'wprevplugin_url' => wprev_trip_plugin_url,
					'wprevpluginsurl' => wprev_trip_plugin_url
				));

			}

			//scripts for analytics page
			if ( $_GET['page'] === 'wp_tripadvisor-analytics' ) {
				global $wpdb;
				$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
				$typearray          = $wpdb->get_col( "SELECT type FROM {$reviews_table_name} WHERE LOWER(type) = 'tripadvisor' GROUP BY type" );

				wp_enqueue_script( 'wprevpro_analytics_page-js', plugin_dir_url( __FILE__ ) . 'js/wprevpro_analytics_page.js', array( 'jquery' ), $this->version, false );
				wp_localize_script(
					'wprevpro_analytics_page-js',
					'adminjs_script_vars',
					array(
						'wpfb_nonce'            => wp_create_nonce( 'randomnoncestring' ),
						'ajax_url'              => admin_url( 'admin-ajax.php' ),
						'gmt_offset_minutes'    => (int) ( get_option( 'gmt_offset' ) * 60 ),
						'pluginsUrl'            => wprev_trip_plugin_url,
						'globalwprevtypearray'  => wp_json_encode( $typearray ),
						'customSourcesIconUrls' => wp_json_encode( array() ),
						'msg1'                  => esc_html__( 'Location Filter', 'wp-tripadvisor-review-slider' ),
						'msg2'                  => esc_html__( 'Type Filter', 'wp-tripadvisor-review-slider' ),
						'd1'                    => esc_html__( 'Today', 'wp-tripadvisor-review-slider' ),
						'd2'                    => esc_html__( 'Yesterday', 'wp-tripadvisor-review-slider' ),
						'd3'                    => esc_html__( 'Last 7 Days', 'wp-tripadvisor-review-slider' ),
						'd4'                    => esc_html__( 'Last 30 Days', 'wp-tripadvisor-review-slider' ),
						'd5'                    => esc_html__( 'Last 60 Days', 'wp-tripadvisor-review-slider' ),
						'd6'                    => esc_html__( 'Last 90 Days', 'wp-tripadvisor-review-slider' ),
						'd7'                    => esc_html__( 'This Month', 'wp-tripadvisor-review-slider' ),
						'd8'                    => esc_html__( 'Last Month', 'wp-tripadvisor-review-slider' ),
						'd9'                    => esc_html__( 'This Year', 'wp-tripadvisor-review-slider' ),
						'd10'                   => esc_html__( 'Last Year', 'wp-tripadvisor-review-slider' ),
						'd11'                   => esc_html__( 'All Time', 'wp-tripadvisor-review-slider' ),
						'msg3'                  => esc_html__( 'Error accessing language function via ajax.', 'wp-tripadvisor-review-slider' ),
						'msg4'                  => esc_html__( 'Ratings', 'wp-tripadvisor-review-slider' ),
						'msg5'                  => esc_html__( 'Error returning json object. Please try again or contact us and copy and send us the following:', 'wp-tripadvisor-review-slider' ),
						'msg6'                  => esc_html__( 'Overall Ratings (Old >> New)', 'wp-tripadvisor-review-slider' ),
						'msg7'                  => esc_html__( 'Review Response:', 'wp-tripadvisor-review-slider' ),
						'msg8'                  => esc_html__( 'Type', 'wp-tripadvisor-review-slider' ),
						'msg9'                  => esc_html__( 'Page', 'wp-tripadvisor-review-slider' ),
						'msg10'                 => esc_html__( 'Source URL', 'wp-tripadvisor-review-slider' ),
						'msg11'                 => esc_html__( 'Reviewer URL', 'wp-tripadvisor-review-slider' ),
						'msg12'                 => esc_html__( 'Review Details', 'wp-tripadvisor-review-slider' ),
					)
				);

				wp_register_script( $this->_token . 'chart-js', plugin_dir_url( __FILE__ ) . 'js/Chart.bundle.min.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . 'chart-js' );

				wp_register_script( $this->_token . 'chart-js-trendline', plugin_dir_url( __FILE__ ) . 'js/chartjs-plugin-trendline.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . 'chart-js-trendline' );

				wp_register_script( $this->_token . '_moment', plugin_dir_url( __FILE__ ) . 'js/moment.min.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . '_moment' );

				wp_register_script( $this->_token . '_daterangepicker', plugin_dir_url( __FILE__ ) . 'js/daterangepicker.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . '_daterangepicker' );

				wp_register_script( $this->_token . '_select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . '_select2' );

				wp_register_script( $this->_token . '_jqcloud', plugin_dir_url( __FILE__ ) . 'js/jqcloud.min.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . '_jqcloud' );

				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
			}

			//scripts for AI Analysis sample page
			if ( $_GET['page'] === 'wp_tripadvisor-ai_analysis' ) {
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );

				wp_register_script( $this->_token . 'chart-js', plugin_dir_url( __FILE__ ) . 'js/Chart.bundle.min.js', array(), $this->version, false );
				wp_enqueue_script( $this->_token . 'chart-js' );

				wp_enqueue_script(
					'wprevpro_ai_analysis_page-js',
					plugin_dir_url( __FILE__ ) . 'js/wprevpro_ai_analysis_page.js',
					array( 'jquery', 'thickbox', $this->_token . 'chart-js' ),
					filemtime( plugin_dir_path( __FILE__ ) . 'js/wprevpro_ai_analysis_page.js' ),
					false
				);
				wp_localize_script(
					'wprevpro_ai_analysis_page-js',
					'adminjs_script_vars',
					array(
						'wpfb_nonce' => wp_create_nonce( 'randomnoncestring' ),
						'ajax_url'   => admin_url( 'admin-ajax.php' ),
						'pluginsUrl' => wprev_trip_plugin_url,
					)
				);
			}
		}
		
	}
	
	public function add_menu_pages() {

		/**
		 * adds the menu pages to wordpress
		 */

		$page_title = 'WP TripAdvisor Reviews : Welcome';
		$menu_title = 'WP TripAdvisor';
		$capability = 'manage_options';
		$menu_slug = 'wp_tripadvisor-welcome';
		
		// Now add the submenu page for the actual reviews list
		
		
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,'wp_tripadvisor_welcome'),'dashicons-star-half');
		
		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		$sub_menu_title = 'Welcome';
		add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, array($this,'wp_tripadvisor_welcome'));	
		
		
		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		//$sub_menu_title = 'Get FB Reviews';
		//add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, array($this,'wp_tripadvisor_settings'));
		
		// Now add the submenu page for tripadvisor
		$submenu_page_title = 'WP Reviews Pro : Reviews List';
		$submenu_title = 'Review List';
		$submenu_slug = 'wp_tripadvisor-reviews';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_tripadvisor_reviews'));
		
		// Now add the submenu page for tripadvisor
		$submenu_page_title = 'WP Reviews Pro : TripAdvisor';
		$submenu_title = 'Get TripAdvisor Reviews';
		$submenu_slug = 'wp_tripadvisor-get_tripadvisor';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_tripadvisor_gettripadvisor'));
		

		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'WP Reviews Pro : Templates';
		$submenu_title = 'Templates';
		$submenu_slug = 'wp_tripadvisor-templates_posts';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_tripadvisor_templates_posts'));

		// Analytics
		$submenu_page_title = 'WP TripAdvisor Reviews : Analytics';
		$submenu_title = 'Analytics';
		$submenu_slug = 'wp_tripadvisor-analytics';
		add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array( $this, 'wp_tripadvisor_analytics' ) );

		// Badges (Pro teaser)
		$submenu_page_title = 'WP TripAdvisor Reviews : Badges';
		$submenu_title = 'Badges';
		$submenu_slug = 'wp_tripadvisor-badges';
		add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array( $this, 'wp_tripadvisor_badges' ) );

		// Forms (Pro teaser)
		$submenu_page_title = 'WP TripAdvisor Reviews : Forms';
		$submenu_title = 'Forms';
		$submenu_slug = 'wp_tripadvisor-forms';
		add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array( $this, 'wp_tripadvisor_forms' ) );

		// Floats (Pro teaser)
		$submenu_page_title = 'WP TripAdvisor Reviews : Floats';
		$submenu_title = 'Floats';
		$submenu_slug = 'wp_tripadvisor-float';
		add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array( $this, 'wp_tripadvisor_float' ) );

		// AI Analysis (Pro teaser)
		$submenu_page_title = 'WP TripAdvisor Reviews : AI Analysis';
		$submenu_title = 'AI Analysis';
		$submenu_slug = 'wp_tripadvisor-ai_analysis';
		add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array( $this, 'wp_tripadvisor_ai_analysis' ) );
	
	}
	
	public function wp_tripadvisor_welcome() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/welcome.php';
	}
	
	public function wp_tripadvisor_reviews() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/review_list.php';
	}
	
	public function wp_tripadvisor_templates_posts() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/templates_posts.php';
	}
	public function wp_tripadvisor_gettripadvisor() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_tripadvisor.php';
	}
	public function wp_tripadvisor_analytics() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/analytics.php';
	}
	public function wp_tripadvisor_badges() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/badges.php';
	}
	public function wp_tripadvisor_forms() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/forms.php';
	}
	public function wp_tripadvisor_float() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/float.php';
	}
	public function wp_tripadvisor_ai_analysis() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/ai_analysis.php';
	}
	public function wp_fb_getpro() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_pro.php';
	}
	
	public function wprev_trip_add_external_link_admin_submenu() {
		global $submenu;

		$menu_slug = 'wp_tripadvisor-welcome'; // used as "key" in menus
		$menu_pos = 1; // whatever position you want your menu to appear
		if (array_key_exists($menu_slug, $submenu)) {
		// add the external links to the slug you used when adding the top level menu
		$submenu[$menu_slug][] = array('<div id="wprev-66023">&#11088; Go Pro!</div>', 'manage_options', 'https://wpreviewslider.com/');
		}
	}
	public function wpse_66023_add_jquery() 
	{
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {   
				$('#wprev-66022').parent().attr('target','_blank');  
			});
		</script>
		<?php
	}

	/**
	 * custom option and settings on tripadvisor page
	 */
	 //===========start tripadvisor page settings===========================================================
	public function wptripadvisor_tripadvisor_settings_init()
	{
	
		// register a new setting for "wp_tripadvisor-get_tripadvisor" page
		register_setting('wp_tripadvisor-get_tripadvisor', 'wptripadvisor_tripadvisor_settings',array($this,'wptripadvisor_validate_input'));
		
		// register a new section in the "wp_tripadvisor-get_tripadvisor" page
		add_settings_section(
			'wptripadvisor_tripadvisor_section_developers',
			'',
			array($this,'wptripadvisor_tripadvisor_section_developers_cb'),
			'wp_tripadvisor-get_tripadvisor'
		);
		
		//register tripadvisor business url input field
		add_settings_field(
			'tripadvisor_business_url', // as of WP 4.6 this value is used only internally
			'TripAdvisor Business URL',
			array($this,'wptripadvisor_field_tripadvisor_business_id_cb'),
			'wp_tripadvisor-get_tripadvisor',
			'wptripadvisor_tripadvisor_section_developers',
			[
				'label_for'         => 'tripadvisor_business_url',
				'class'             => 'wptripadvisor_row',
				'wptripadvisor_custom_data' => 'custom',
			]
		);

		//Turn on TripAdvisor Reviews Downloader
		/*
		add_settings_field("tripadvisor_radio", "Turn On TripAdvisor Reviews", array($this,'tripadvisor_radio_display'), "wp_tripadvisor-get_tripadvisor", "wptripadvisor_tripadvisor_section_developers",
			[
				'label_for'         => 'tripadvisor_radio',
				'class'             => 'wptripadvisor_row',
				'wptripadvisor_custom_data' => 'custom',
			]); 
			*/
	
	}
	
	public function wptripadvisor_validate_input( $input ) {
		// Create our array for storing the validated options 
		$output = array();
		
		// Loop through each of the incoming options 
		foreach( $input as $key => $value ) {
			
			// Check to see if the current option has a value. If so, process it. 
			if( isset( $input[$key] ) ) {
			
				// Strip all HTML and PHP tags and properly handle quoted strings 
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				
			} // end if 
			
		} // end foreach 
		
		// Return the array processing any additional functions filtered by this action 
		return apply_filters( 'wptripadvisor_validate_input', $output, $input );
	}
	
	//==== developers section cb ====
	public function wptripadvisor_tripadvisor_section_developers_cb($args)
	{
		//echos out at top of section
	}
	
	//==== field cb =====
	public function wptripadvisor_field_tripadvisor_business_id_cb($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wptripadvisor_tripadvisor_settings');

		// output the field
		?>
		<input id="<?= esc_attr($args['label_for']); ?>" data-custom="<?= esc_attr($args['wptripadvisor_custom_data']); ?>" type="text" name="wptripadvisor_tripadvisor_settings[<?= esc_attr($args['label_for']); ?>]" placeholder="" value="<?php echo esc_url($options[$args['label_for']]); ?>">
		
		<p class="description">
			<?= esc_html__('Enter the TripAdvisor URL for your business and click Save Settings. Example:', 'wp-tripadvisor-review-slider'); ?>
			</br>
			<?= esc_html__('https://www.tripadvisor.com/Restaurant_Review-g30620-d10262422-Reviews-Yellowhammer_Brewing-Huntsville_Alabama.html', 'wp-tripadvisor-review-slider'); ?>
		</p>
		<?php
	}
	/*
	public function tripadvisor_radio_display($args)
		{
		$options = get_option('wptripadvisor_tripadvisor_settings');
		
		   ?>
				<input type="radio" name="wptripadvisor_tripadvisor_settings[<?= esc_attr($args['label_for']); ?>]" value="yes" <?php checked('yes', $options[$args['label_for']], true); ?>>Yes&nbsp;&nbsp;&nbsp;
				<input type="radio" name="wptripadvisor_tripadvisor_settings[<?= esc_attr($args['label_for']); ?>]" value="no" <?php checked('no', $options[$args['label_for']], true); ?>>No
		   <?php
		}
		*/
	//=======end tripadvisor page settings========================================================

	
	/**
	 * Store reviews in table, called from javascript file admin.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wptripadvisor_process_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wptripadvisor_nonce');
		
		// SECURITY FIX: Verify user has permission to manage reviews
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
			wp_die();
		}
		
		$postreviewarray = $_POST['postreviewarray'];
		
		//var_dump($postreviewarray);

		//loop through each one and insert in to db
		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		
		$stats = array();
		
		foreach($postreviewarray as $item) { //foreach element in $arr
			// SECURITY FIX: Sanitize all input data
			$pageid = sanitize_text_field($item['pageid']);
			$pagename = sanitize_text_field($item['pagename']);
			$created_time = sanitize_text_field($item['created_time']);
			$created_time_stamp = strtotime($created_time);
			$reviewer_name = sanitize_text_field($item['reviewer_name']);
			$reviewer_id = sanitize_text_field($item['reviewer_id']);
			$rating = intval($item['rating']);
			$review_text = wp_kses_post($item['review_text']);
			$review_length = str_word_count($review_text);
			$rtype = sanitize_text_field($item['type']);
			
			//check to see if row is in db already
			// SECURITY FIX: Use prepared statement to prevent SQL injection
			$checkrow = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM ".$table_name." WHERE reviewer_name = %s AND (review_length = %d OR created_time_stamp = %d)",
				$reviewer_name,
				$review_length,
				$created_time_stamp
			) );
			
			//echo $wpdb->last_result;
			//echo "<br>here<br>";
			//echo $wpdb->last_query;
			
			echo $checkrow;
			
			if ( null === $checkrow ) {
				$stats[] =array( 
						'pageid' => $pageid, 
						'pagename' => $pagename, 
						'created_time' => $created_time,
						'created_time_stamp' => strtotime($created_time),
						'reviewer_name' => $reviewer_name,
						'reviewer_id' => $reviewer_id,
						'rating' => $rating,
						'review_text' => $review_text,
						'hide' => '',
						'review_length' => $review_length,
						'type' => $rtype
					);
			}
		}
		$i = 0;
		$insertnum = 0;
		foreach ( $stats as $stat ){
			$insertnum = $wpdb->insert( $table_name, $stat );
			$i=$i + 1;
		}
	
		$insertid = $wpdb->insert_id;

		//header('Content-Type: application/json');
		echo $insertnum."-".$insertid."-".$i;

		die();
	}

	/**
	 * Save an edited review (avatar URL + date) via AJAX from the Review List
	 * edit popup, so saving never reloads the page / loses the list scroll position.
	 *
	 * @access  public
	 * @since   14.7
	 * @return  void
	 */
	public function wptripadvisor_savereview_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-tripadvisor-review-slider' ) ) );
			return;
		}

		check_ajax_referer( 'randomnoncestring', 'wptripadvisor_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';

		$r_id       = isset( $_POST['editrid'] ) ? absint( $_POST['editrid'] ) : 0;
		$avatar_url = isset( $_POST['avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['avatar_url'] ) ) : '';
		$rdate_raw  = isset( $_POST['review_date'] ) ? sanitize_text_field( wp_unslash( $_POST['review_date'] ) ) : '';

		if ( $r_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid review.', 'wp-tripadvisor-review-slider' ) ) );
			return;
		}

		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $r_id ) );
		if ( ! $existing ) {
			wp_send_json_error( array( 'message' => __( 'Review not found.', 'wp-tripadvisor-review-slider' ) ) );
			return;
		}

		$parsed_stamp = $rdate_raw !== '' ? strtotime( $rdate_raw ) : false;
		if ( ! $parsed_stamp ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date. Use the format YYYY-MM-DD HH:MM:SS.', 'wp-tripadvisor-review-slider' ) ) );
			return;
		}

		$created_time = date( 'Y-m-d H:i:s', $parsed_stamp );
		$data         = array(
			'userpic'            => $avatar_url,
			'created_time'       => $created_time,
			'created_time_stamp' => $parsed_stamp,
		);
		$format = array( '%s', '%s', '%d' );

		// Keep hidden-reviews fingerprint in sync when date changes.
		// Duplicate downloads still match on reviewer_name + review_length, so this is safe.
		if ( $existing->hide === 'yes' ) {
			$old_val = $existing->reviewer_name . '-' . $existing->created_time_stamp . '-' . $existing->review_length . '-' . $existing->type . '-' . $existing->rating;
			$new_val = $existing->reviewer_name . '-' . $parsed_stamp . '-' . $existing->review_length . '-' . $existing->type . '-' . $existing->rating;

			$tripadvisorhidden      = get_option( 'wptripadvisor_hidden_reviews' );
			$tripadvisorhiddenarray = $tripadvisorhidden ? json_decode( $tripadvisorhidden, true ) : array();
			if ( ! is_array( $tripadvisorhiddenarray ) ) {
				$tripadvisorhiddenarray = array();
			}
			$key = array_search( $old_val, $tripadvisorhiddenarray, true );
			if ( $key !== false ) {
				$tripadvisorhiddenarray[ $key ] = $new_val;
				update_option( 'wptripadvisor_hidden_reviews', wp_json_encode( array_values( $tripadvisorhiddenarray ) ) );
			}
		}

		$updated = $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $r_id ),
			$format,
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => __( 'Database error while saving. Please try again.', 'wp-tripadvisor-review-slider' ) ) );
			return;
		}

		wp_send_json_success(
			array(
				'id'      => $r_id,
				'userpic' => $avatar_url !== '' ? esc_url( $avatar_url ) : '',
				'date'    => esc_html( $created_time ),
			)
		);
	}

	/**
	 * Hides or deletes reviews in table, called from javascript file wptripadvisor_review_list_page.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wptripadvisor_hidereview_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wptripadvisor_nonce');
		
		// SECURITY FIX: Verify user has permission to manage reviews
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
			wp_die();
		}
		
		$rid = intval($_POST['reviewid']);
		$myaction = $_POST['myaction'];

		//loop through each one and insert in to db
		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		
		//check to see if we are deleting or just hiding or showing
		if($myaction=="hideshow"){
			//grab review and see if it is hidden or not
			$myreview = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $rid" );
			
			//pull array from options table of tripadvisor hidden
			$tripadvisorhidden = get_option( 'wptripadvisor_hidden_reviews' );
			if(!$tripadvisorhidden){
				$tripadvisorhiddenarray = array('');
			} else {
				$tripadvisorhiddenarray = json_decode($tripadvisorhidden,true);
			}
			if(!is_array($tripadvisorhiddenarray)){
				$tripadvisorhiddenarray = array('');
			}
			$this_tripadvisor_val = $myreview->reviewer_name."-".$myreview->created_time_stamp."-".$myreview->review_length."-".$myreview->type."-".$myreview->rating;

			if($myreview->hide=="yes"){
				//already hidden need to show
				$newvalue = "";
				
				//remove from $tripadvisorhidden
				if(($key = array_search($this_tripadvisor_val, $tripadvisorhiddenarray)) !== false) {
					unset($tripadvisorhiddenarray[$key]);
				}
				
			} else {
				//shown, need to hide
				$newvalue = "yes";
				
				//need to update TripAdvisor hidden ids in options table here array of name,time,count,type
				 array_push($tripadvisorhiddenarray,$this_tripadvisor_val);
			}
			//update hidden tripadvisor reviews option, use this when downloading tripadvisor reviews so we can re-hide them each download
			$tripadvisorhiddenjson=json_encode($tripadvisorhiddenarray);
			update_option( 'wptripadvisor_hidden_reviews', $tripadvisorhiddenjson );
			
			//update database review table to hide this one
			$data = array( 
				'hide' => "$newvalue"
				);
			$format = array( 
					'%s'
				); 
			$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $rid ), $format, array( '%d' ));
			if($updatetempquery>0){
				echo $rid."-".$myaction."-".$newvalue;
			} else {
				echo $rid."-".$myaction."-fail";
			}

		}
		if($myaction=="deleterev"){
			$deletereview = $wpdb->delete( $table_name, array( 'id' => $rid ), array( '%d' ) );
			if($deletereview>0){
				echo $rid."-".$myaction."-success";
			} else {
				echo $rid."-".$myaction."-fail";
			}
		
		}

		die();
	}
	
	/**
	 * Ajax, retrieves reviews from table, called from javascript file wptripadvisor_templates_posts_page.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wptripadvisor_getreviews_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wptripadvisor_nonce');
		
		// SECURITY FIX: Verify user has permission to manage reviews
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
			wp_die();
		}
		
		$filtertext = htmlentities($_POST['filtertext']);
		$filterrating = htmlentities($_POST['filterrating']);
		$filterrating = intval($filterrating);
		$curselrevs = $_POST['curselrevs'];
		
		//perform db search and return results
		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$rowsperpage = 20;
		
		//pagenumber
		if(isset($_POST['pnum'])){
		$temppagenum = $_POST['pnum'];
		} else {
		$temppagenum ="";
		}
		if ( $temppagenum=="") {
			$pagenum = 1;
		} else if(is_numeric($temppagenum)){
			$pagenum = intval($temppagenum);
		}
		
		//sort direction
		if($_POST['sortdir']=="ASC" || $_POST['sortdir']=="DESC"){
			$sortdir = $_POST['sortdir'];
		} else {
			$sortdir = "DESC";
		}

		//make sure sortby is valid
		if(!isset($_POST['sortby'])){
			$_POST['sortby'] = "";
		}
		$allowed_keys = ['created_time_stamp', 'reviewer_name', 'rating', 'review_length', 'pagename', 'type' , 'hide'];
		$checkorderby = sanitize_key($_POST['sortby']);
	
		if(in_array($checkorderby, $allowed_keys, true) && $_POST['sortby']!=""){
			$sorttable = $_POST['sortby']. " ";
		} else {
			$sorttable = "created_time_stamp ";
		}
		if($_POST['sortdir']=="ASC" || $_POST['sortdir']=="DESC"){
			$sortdir = $_POST['sortdir'];
		} else {
			$sortdir = "DESC";
		}
		
		//get reviews from db
		$lowlimit = ($pagenum - 1) * $rowsperpage;
		$tablelimit = $lowlimit.",".$rowsperpage;
		
		if($filterrating>0){
			$filterratingtext = "rating = ".$filterrating;
		} else {
			$filterratingtext = "rating > 0";
		}
			
		//check to see if looking for previously selected only
		if (is_array($curselrevs)){
			// SECURITY FIX: cast all selected ids to integers and use prepared
			// statement placeholders to prevent SQL injection via curselrevs[].
			$ids = array_map('intval', $curselrevs);
			$ids = array_values(array_filter($ids, function($id){ return $id > 0; }));

			if (!empty($ids)) {
				$placeholders = implode(',', array_fill(0, count($ids), '%d'));
				$query = $wpdb->prepare(
					"SELECT * FROM ".$table_name." WHERE id IN ($placeholders)",
					$ids
				);
				$reviewsrows = $wpdb->get_results($query);
			} else {
				$reviewsrows = array();
			}
			$hidepagination = true;
			$hidesearch = true;
		} else {
		

			//if filtertext set then use different query
			if($filtertext!=""){
				// SECURITY FIX: escape LIKE wildcards and bind the search term
				// as a prepared parameter to prevent SQL injection via filtertext.
				$like = '%' . $wpdb->esc_like($filtertext) . '%';
				$reviewsrows = $wpdb->get_results(
					$wpdb->prepare("SELECT * FROM ".$table_name."
					WHERE (reviewer_name LIKE %s or review_text LIKE %s) AND ".$filterratingtext."
					ORDER BY ".$sorttable." ".$sortdir." 
					LIMIT ".$tablelimit." ", $like, $like)
				);
				$hidepagination = true;
			} else {
				$reviewsrows = $wpdb->get_results(
					$wpdb->prepare("SELECT * FROM ".$table_name."
					WHERE id>%d AND ".$filterratingtext."
					ORDER BY ".$sorttable." ".$sortdir." 
					LIMIT ".$tablelimit." ", "0")
				);
			}
		}
		
		//total number of rows
		$reviewtotalcount = $wpdb->get_var( "SELECT COUNT(*) FROM ".$table_name." WHERE id>1 AND ".$filterratingtext );
		//total pages
		$totalpages = ceil($reviewtotalcount/$rowsperpage);
		
		$reviewsrows['reviewtotalcount']=$reviewtotalcount;
		$reviewsrows['totalpages']=$totalpages;
		$reviewsrows['pagenum']=$pagenum;
		if($hidepagination){
			$reviewsrows['reviewtotalcount']=0;
			//$reviewsrows['totalpages']=0;
			//$reviewsrows['pagenum']=0;
		}
		if($hidesearch){
			//$reviewsrows['reviewtotalcount']=0;
			$reviewsrows['totalpages']=0;
			//$reviewsrows['pagenum']=0;
		}
		
		$results = json_encode($reviewsrows);
		echo $results;

		die();
	}
	
	
	
	/**
	 * replaces insert into post text on media uploader when uploading reviewer avatar
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wptripadvisor_media_text() {
		global $pagenow;
		if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
			// Now we'll replace the 'Insert into Post Button' inside Thickbox
			add_filter( 'gettext', array($this,'replace_thickbox_text') , 1, 3 );
		}
	}
	 
	public function replace_thickbox_text($translated_text, $text, $domain) {
		if ('Insert into Post' == $text) {
			$referer = strpos( wp_get_referer(), 'wp_tripadvisor-reviews' );
			if ( $referer != '' ) {
				return __('Use as Reviewer Avatar', 'wp-tripadvisor-review-slider' );
			}
		}
		return $translated_text;
	}
	

	/**
	 * download csv file of reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wptripadvisor_download_csv() {
      global $pagenow;
      if ($pagenow=='admin.php' && current_user_can('export') && isset($_GET['taction']) && $_GET['taction']=='downloadallrevs' && $_GET['page']=='wp_tripadvisor-reviews') {
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=reviewdata.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';		
		$downloadreviewsrows = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM ".$table_name."
				WHERE id>%d ", "0"),'ARRAY_A'
			);
		$file = fopen('php://output', 'w');
		$delimiter=";";
		
		foreach ($downloadreviewsrows as $line) {
		    fputcsv($file, $line, $delimiter);
		}

        exit();
      }
    }	
	

	/**
	 * Legacy hook: settings-updated no longer triggers a full download.
	 * Downloads are per-source via AJAX.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wptripadvisor_download_tripadvisor() {
		// Intentionally empty — multi-source AJAX download replaced Save & Download.
	}

	/**
	 * Get saved TripAdvisor crawl sources, migrating the legacy single URL if needed.
	 *
	 * @return array
	 */
	public function wptripadvisor_get_crawls() {
		$raw = get_option( 'wprev_tripadvisor_crawls', 'not-exists' );
		if ( 'not-exists' === $raw ) {
			$crawls = array();
			update_option( 'wprev_tripadvisor_crawls', wp_json_encode( $crawls ) );
		} else {
			$crawls = json_decode( $raw, true );
			if ( ! is_array( $crawls ) ) {
				$crawls = array();
			}
		}

		// Migrate legacy single tripadvisor_business_url into crawls.
		$options = get_option( 'wptripadvisor_tripadvisor_settings' );
		if ( is_array( $options ) && ! empty( $options['tripadvisor_business_url'] ) ) {
			$url = esc_url_raw( trim( $options['tripadvisor_business_url'] ) );
			if ( $url && filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$pageid = $this->wptripadvisor_extract_pageid_from_url( $url );
				if ( $pageid && ! isset( $crawls[ $pageid ] ) ) {
					$crawls[ $pageid ] = array(
						'pageid'       => $pageid,
						'businessname' => $this->wptripadvisor_extract_businessname_from_url( $url ),
						'url'          => $url,
						'avg'          => '',
						'total'        => '',
					);
					update_option( 'wprev_tripadvisor_crawls', wp_json_encode( $crawls ) );
				}
			}
		}

		return $crawls;
	}

	/**
	 * Persist crawls option and keep legacy tripadvisor_business_url in sync.
	 *
	 * @param array $crawls Sources keyed by pageid.
	 */
	public function wptripadvisor_save_crawls( $crawls ) {
		if ( ! is_array( $crawls ) ) {
			$crawls = array();
		}
		update_option( 'wprev_tripadvisor_crawls', wp_json_encode( $crawls ) );

		// Keep first source URL in legacy option for older template link fallbacks.
		$options = get_option( 'wptripadvisor_tripadvisor_settings' );
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		$first_url = '';
		foreach ( $crawls as $source ) {
			if ( is_array( $source ) && ! empty( $source['url'] ) ) {
				$first_url = $source['url'];
				break;
			}
		}
		$options['tripadvisor_business_url'] = $first_url;
		update_option( 'wptripadvisor_tripadvisor_settings', $options );
	}

	/**
	 * Extract TripAdvisor location id (d########) from a business URL.
	 *
	 * @param string $url TripAdvisor URL.
	 * @return string
	 */
	public function wptripadvisor_extract_pageid_from_url( $url ) {
		$url = strtok( $url, '?' );
		$url = preg_replace( '/#REVIEWS$/i', '', $url );
		if ( preg_match( '/-d(\d+)/i', $url, $m ) ) {
			return 'd' . $m[1];
		}
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path ) {
			$base = basename( $path, '.html' );
			if ( $base ) {
				return sanitize_title( $base );
			}
		}
		return '';
	}

	/**
	 * Best-effort business name from TripAdvisor URL slug.
	 *
	 * @param string $url TripAdvisor URL.
	 * @return string
	 */
	public function wptripadvisor_extract_businessname_from_url( $url ) {
		if ( preg_match( '/-Reviews-([A-Za-z0-9_]+)/', $url, $m ) ) {
			return str_replace( '_', ' ', $m[1] );
		}
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path ) {
			return str_replace( '_', ' ', basename( $path, '.html' ) );
		}
		return 'TripAdvisor Business';
	}

	/**
	 * Build one source table row HTML for AJAX add.
	 *
	 * @param string $pageid Page id.
	 * @param array  $source Source data.
	 * @return string
	 */
	public function wptripadvisor_source_row_html( $pageid, $source ) {
		$bname     = isset( $source['businessname'] ) ? $source['businessname'] : '';
		$url       = isset( $source['url'] ) ? $source['url'] : '';
		$avg       = isset( $source['avg'] ) ? $source['avg'] : '';
		$total     = isset( $source['total'] ) ? $source['total'] : '';
		$avg_total = ( $avg !== '' || $total !== '' ) ? esc_html( $avg ) . ' / ' . esc_html( $total ) : '—';
		$del_url   = wp_nonce_url(
			admin_url( 'admin.php?page=wp_tripadvisor-get_tripadvisor&ract=del&pageid=' . rawurlencode( $pageid ) ),
			'wptripadvisor_del_source'
		);

		ob_start();
		?>
		<tr data-pageid="<?php echo esc_attr( $pageid ); ?>">
			<td>
				<?php echo esc_html( $bname ); ?>
				<?php if ( $url ) : ?>
					<br><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View on TripAdvisor', 'wp-tripadvisor-review-slider' ); ?></a>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $pageid ); ?></td>
			<td class="trip-source-stats"><?php echo $avg_total; ?></td>
			<td>
				<button type="button" class="button button-primary downloadrevs" data-pageid="<?php echo esc_attr( $pageid ); ?>"><?php esc_html_e( 'Download Reviews', 'wp-tripadvisor-review-slider' ); ?></button>
				<span class="buttonloader2 wprevloader"></span>
				<a class="button" style="color:#a00;" href="<?php echo esc_url( $del_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this source and its reviews?', 'wp-tripadvisor-review-slider' ) ); ?>');"><?php esc_html_e( 'Delete', 'wp-tripadvisor-review-slider' ); ?></a>
				<span class="trip-source-msg"></span>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Delete a source, its reviews, and averages row.
	 *
	 * @param string $pageid Page id.
	 */
	public function wptripadvisor_delete_source( $pageid ) {
		$pageid = sanitize_text_field( $pageid );
		if ( $pageid === '' ) {
			return;
		}
		$crawls = $this->wptripadvisor_get_crawls();
		unset( $crawls[ $pageid ] );
		$this->wptripadvisor_save_crawls( $crawls );

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'wptripadvisor_reviews', array( 'pageid' => $pageid ) );
		$wpdb->delete( $wpdb->prefix . 'wptripadvisor_total_averages', array( 'btp_id' => $pageid ) );
	}

	/**
	 * AJAX: add a TripAdvisor source URL.
	 */
	public function wptripadvisor_ajax_add_source() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => 'Insufficient permissions.' ) );
			wp_die();
		}
		check_ajax_referer( 'randomnoncestring', 'wptripadvisor_nonce' );

		$url  = isset( $_POST['tripadvisor_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['tripadvisor_url'] ) ) ) : '';
		$name = isset( $_POST['businessname'] ) ? sanitize_text_field( wp_unslash( $_POST['businessname'] ) ) : '';

		if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => __( 'Please enter a valid TripAdvisor URL.', 'wp-tripadvisor-review-slider' ) ) );
			wp_die();
		}
		if ( stripos( $url, 'tripadvisor.' ) === false ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => __( 'URL must be a TripAdvisor business page.', 'wp-tripadvisor-review-slider' ) ) );
			wp_die();
		}

		$pageid = $this->wptripadvisor_extract_pageid_from_url( $url );
		if ( $pageid === '' ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => __( 'Could not determine a page ID from that URL.', 'wp-tripadvisor-review-slider' ) ) );
			wp_die();
		}

		$crawls = $this->wptripadvisor_get_crawls();
		if ( isset( $crawls[ $pageid ] ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => __( 'That source is already added.', 'wp-tripadvisor-review-slider' ) ) );
			wp_die();
		}

		if ( $name === '' ) {
			$name = $this->wptripadvisor_extract_businessname_from_url( $url );
		}

		$source = array(
			'pageid'       => $pageid,
			'businessname' => $name,
			'url'          => strtok( $url, '?' ),
			'avg'          => '',
			'total'        => '',
		);
		$crawls[ $pageid ] = $source;
		$this->wptripadvisor_save_crawls( $crawls );

		echo wp_json_encode(
			array(
				'ack'     => 'success',
				'ackmsg'  => __( 'Source added. Click Download Reviews to fetch reviews.', 'wp-tripadvisor-review-slider' ),
				'pageid'  => $pageid,
				'row_html'=> $this->wptripadvisor_source_row_html( $pageid, $source ),
			)
		);
		wp_die();
	}

	/**
	 * AJAX: download reviews for one saved source.
	 */
	public function wptripadvisor_ajax_download_source() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => 'Insufficient permissions.' ) );
			wp_die();
		}
		check_ajax_referer( 'randomnoncestring', 'wptripadvisor_nonce' );

		$pageid = isset( $_POST['pageid'] ) ? sanitize_text_field( wp_unslash( $_POST['pageid'] ) ) : '';
		$crawls = $this->wptripadvisor_get_crawls();
		if ( $pageid === '' || empty( $crawls[ $pageid ]['url'] ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmsg' => __( 'Source not found. Add the URL again.', 'wp-tripadvisor-review-slider' ) ) );
			wp_die();
		}

		$result = $this->wptripadvisor_download_one_source(
			$crawls[ $pageid ]['url'],
			$pageid,
			isset( $crawls[ $pageid ]['businessname'] ) ? $crawls[ $pageid ]['businessname'] : ''
		);

		echo wp_json_encode( $result );
		wp_die();
	}
	
	
	
	//for using curl instead of fopen
	private function file_get_contents_curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
	
	
	//fix stringtotime for other languages
	private function myStrtotime($date_string) {
		$monthnamearray = array(
		'janvier'=>'jan',
		'février'=>'feb',
		'févr.'=>'feb',
		'mars'=>'march',
		'avril'=>'apr',
		'mai'=>'may',
		'juin'=>'jun',
		'juillet'=>'jul',
		'juil.'=>'jul',
		'août'=>'aug',
		'septembre'=>'sep',
		'octobre'=>'oct',
		'novembre'=>'nov',
		'décembre'=>'dec',
		'gennaio'=>'jan',
		'febbraio'=>'feb',
		'marzo'=>'march',
		'aprile'=>'apr',
		'maggio'=>'may',
		'giugno'=>'jun',
		'luglio'=>'jul',
		'agosto'=>'aug',
		'settembre'=>'sep',
		'ottobre'=>'oct',
		'novembre'=>'nov',
		'dicembre'=>'dec',
		'janeiro'=>'jan',
		'fevereiro'=>'feb',
		'março'=>'march',
		'abril'=>'apr',
		'maio'=>'may',
		'junho'=>'jun',
		'julho'=>'jul',
		'agosto'=>'aug',
		'setembro'=>'sep',
		'outubro'=>'oct',
		'novembro'=>'nov',
		'dezembro'=>'dec',
		'enero'=>'jan',
		'febrero'=>'feb',
		'marzo'=>'march',
		'abril'=>'apr',
		'mayo'=>'may',
		'junio'=>'jun',
		'julio'=>'jul',
		'septiembre'=>'sep',
		'octubre'=>'oct',
		'noviembre'=>'nov',
		'diciembre'=>'dec',
		'januari'=>'jan',
		'februari'=>'feb',
		'maart'=>'march',
		'april'=>'apr',
		'mei'=>'may',
		'juni'=>'jun',
		'juli'=>'jul',
		'augustus'=>'aug',
		'september'=>'sep',
		'oktober'=>'oct',
		'november'=>'nov',
		'december'=>'dec',
		' de '=>'',
		'dezember'=>'dec',
		'januar '=>'jan ',
		'stycznia'=>'jan',
		'lutego'=>'feb',
		'februar'=>'feb',
		'marca'=>'march',
		'märz'=>'march',
		'kwietnia'=>'apr',
		'maja'=>'may',
		'czerwca'=>'jun',
		'lipca'=>'jul',
		'sierpnia'=>'aug',
		'września'=>'sep',
		'października'=>'oct',
		'listopada'=>'nov',
		'grudnia'=>'dec',
		'february'=>'feb',
		'января'=>'jan',
		'февраля'=>'feb',
		'марта'=>'march',
		'апреля'=>'apr',
		'мая'=>'may',
		'июня'=>'jun',
		'июля'=>'jul',
		'августа'=>'aug',
		'сентября'=>'sep',
		'октября'=>'oct',
		'ноября'=>'nov',
		'декабря'=>'dec',
		'tháng 1,'=>'jan',
		'tháng 2,'=>'feb',
		'tháng 3,'=>'march',
		'tháng 4,'=>'apr',
		'tháng 5,'=>'may',
		'tháng 6,'=>'jun',
		'tháng 7,'=>'jul',
		'tháng 8,'=>'aug',
		'tháng 9,'=>'sep',
		'tháng 10,'=>'oct',
		'tháng 11,'=>'nov',
		'tháng 12,'=>'dec',
		'Ιανουαρίου'=>'jan',
		'Φεβρουαρίου'=>'feb',
		'Μαρτίου'=>'march',
		'Απριλίου'=>'apr',
		'Μαΐου'=>'may',
		'Ιουνίου'=>'jun',
		'Ιουλίου'=>'jul',
		'Αυγούστου'=>'aug',
		'Σεπτεμβρίου'=>'sep',
		'Οκτωβρίου'=>'oct',
		'Νοεμβρίου'=>'nov',
		'Δεκεμβρίου'=>'dec',
		'janv.'=>'jan',
		'févr.'=>'feb',
		'mars'=>'march',
		'avril'=>'apr',
		'mai'=>'may',
		'juin'=>'jun',
		'juil.'=>'jul',
		'août'=>'aug',
		'sept.'=>'sep',
		'oct.'=>'oct',
		'nov.'=>'nov',
		'déc.'=>'dec',
		'agustus'=>'aug',
		'genn'=>'jan',
		'gen'=>'jan',
		'febbr'=>'feb',
		'feb'=>'feb',
		'mar'=>'mar',
		'apr'=>'apr',
		'magg'=>'may',
		'mag'=>'may',
		'giugno'=>'jun',
		'giu'=>'jun',
		'luglio'=>'jul',
		'lug'=>'jul',
		'ag'=>'aug',
		'ago'=>'aug',
		'sett'=>'sep',
		'set'=>'sep',
		'ott'=>'oct',
		'nov'=>'nov',
		'dic'=>'dec',
		);
		
		$result = strtr(strtolower($date_string), $monthnamearray);
		
		if($result ==''){
			$result = strtr(($date_string), $monthnamearray);
		}
		
		//echo "<br>res:".$result;
		
		//echo "<br>str:". strtotime($result);
		

		return strtotime($result); 
		
	}
	
	
	public function getBetween($content, $start, $end) {
		$n = explode($start, $content);
		$result = Array();
		foreach ($n as $val) {
			$pos = strpos($val, $end);
			if ($pos !== false) {
				$result[] = substr($val, 0, $pos);
			}
		}
		return $result;
	}

		//for using curl instead of fopen
	private function file_get_contents_curl_browser($url,$cookieval) {
		$agent= 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'pragma: no-cache',
							'cache-control: no-cache',
							'upgrade-insecure-requests: 1',
							'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
							'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3', 'accept-encoding: gzip, deflate, br',
							'accept-language: en-US,en;q=0.9',
							'cookie: '.$cookieval.''
					));
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function wprevpro_download_tripadvisor_showuserreviews_url($currenturl) {
		ini_set('memory_limit','500M');
		//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


		if (strpos($currenturl, 'VacationRentalReview') !== false) {
			//this is a vactionrental
			$vactionrental = true;
		} else {
			$vactionrental = false;
		}
					

		$loop = 0;
		phantom:
				
		$apitoken = 'a-demo-key-with-low-quota-per-ip-address';
		$args = array(
			'timeout'     => 60,
		);
		$callfreetrip = false;
		
		if($loop < 2){
			$callurl = $currenturl;
		} else if($loop < 3){
			$callurl = "https://phantomjscloud.com/api/browser/v2/".$apitoken."/?request={url:%22".urlencode($currenturl)."%22,renderType:%22html%22,requestSettings:{doneWhen:[{event:%22domReady%22}]}}";
		} else {
			$callurl = "https://crawl.ljapps.com/freetripphantom.php?urlval=".urlencode($currenturl).'&sfp=free&nobot=1';
			$callfreetrip = true;
		}
		
		//echo $currenturl;
		$responsefile = wp_remote_get( $callurl,$args );

		if ( is_array( $responsefile ) ) {
		  $header = $responsefile['headers']; // array of http header lines
		  $fileurlcontents = $responsefile['body']; // use the content
		} else {
			echo esc_html__('Error finding reviews. Please contact plugin support. Error: ', 'wp-tripadvisor-review-slider');
			if( is_wp_error( $responsefile ) ) {
				echo $response->get_error_message();
			}
			die();
		}
		
		
		//if we are calling freetripphantom then we are getting the links only.nextbtnlink
		if($callfreetrip){
			$surls = json_decode($fileurlcontents,true);
			$parseurl = parse_url($currenturl);
			$newurl = $parseurl['scheme'].'://'.$parseurl['host'].$surls['rtitlelink'];
			$page2url = $parseurl['scheme'].'://'.$parseurl['host'].$surls['nextbtnlink'];
			
			$response =array("page1"=>$newurl,"page2"=>$page2url);
			//print_r($response);
			//die();
		} else {
		
			//fix for lazy load base64 ""
			//$fileurlcontents = str_replace('=="', '', $fileurlcontents);
			
			//echo "loop:".$loop." - callurl: ".$callurl;
			//die();
			
			$html = wptrip_str_get_html($fileurlcontents);
			
			if(!$html){
				if($loop <4){
					$loop = $loop + 1;
					goto phantom;
				} else {
					echo esc_html__('Error 301: Unable to read TripAdvisor page. Please contact support and send the TripAdvisor URL.', 'wp-tripadvisor-review-slider');
					die();
				}
			}
			//echo $html;
			//die();
			//die();
			//unset($fileurlcontents);
			
			//find total and average number here
			$avgrating ='';
			$totalreviews = '';
			
			$reviewobject5 ="";
			$page2url ="";
			$rtitlelink ="";
			$nextbtnlink="";
			//echo $currenturl;
			//echo "<br><br>";
			//check to see if on vacation rental or regular page
			if($vactionrental==true){
				if($html->find('div.reviewSelector')){
					$reviewobject = $html->find('div.reviewSelector',0);
				} else {
					echo esc_html__('Error 102a: Unable to read Vacation Rental TripAdvisor page. Please contact support.', 'wp-tripadvisor-review-slider');
					die();	
				}
			} else {
				if($html->find('div.review-container')){
						$reviewobject = $html->find('div.review-container',0);
						$reviewobject5 = $html->find('div.review-container',5);
				}
			}
			
			//need to get the links
			
			if(isset($reviewobject) && $reviewobject!="" && $rtitlelink==''){
				$rtitlelink = $reviewobject->find('div.quote', 0)->find('a',0)->href;
				$spot = 'h5';
			}
			
			if($reviewobject5!="" && $nextbtnlink==''){
				$nextbtnlink = $reviewobject5->find('div.quote', 0)->find('a',0)->href;
			}
			
			
		if($rtitlelink==""){
			if($html->find("div[class*=ReviewTitle]", 0)){
				$rtitlelink = $html->find("div[class*=ReviewTitle]", 0)->find('a',0)->href;

				if($html->find("div[class*=ReviewTitle]", 4)){
					$nextbtnlink = $html->find("div[class*=ReviewTitle]", 4)->find('a',0)->href;
				}
				$spot = 'h0';
			
			} else if($html->find("div[data-test-target*=review-title]", 0)){

				$rtitlelink = $html->find("div[data-test-target*=review-title]", 0)->find('a',0)->href;

				if($html->find("div[data-test-target*=review-title]", 4)){
					$nextbtnlink = $html->find("div[data-test-target*=review-title]", 4)->find('a',0)->href;
				}
				$spot = 'h1';
			
			} else if($html->find("div.glasR4aX", 0)){
				$rtitlelink = $html->find("div.glasR4aX", 0)->find('a',0)->href;
				$spot = 'h2';

				if($html->find("div.glasR4aX", 4)){
					$nextbtnlink = $html->find("div.glasR4aX", 4)->find('a',0)->href;
				}

			
			} else {
				//try to find links using string search here, had to add for italian languages
				$urlmatches = $this->getBetween($fileurlcontents, 'href="/ShowUserReviews-', '"');

				if(isset($urlmatches[0]) && $urlmatches[0]!='' && isset($urlmatches[1]) && $urlmatches[1]!=''){
					$rtitlelink = "/ShowUserReviews-".$urlmatches['1'];
					$nextbtnlink = "/ShowUserReviews-".$urlmatches['6'];
					$spot = 'h3';

				} else if(isset($urlmatches[0]) && $urlmatches[0]!=''){
					$rtitlelink = "/ShowUserReviews-".$urlmatches['0'];
					$nextbtnlink = "/ShowUserReviews-".$urlmatches['5'];
					$spot = 'h4';
				
					
				} else {

					echo esc_html__('Error 103a: Unable to read TripAdvisor page. Please refresh or contact support if you keep seeing this message.', 'wp-tripadvisor-review-slider');
					//echo $html;
					die();	
				}
			}
		}
			
			//find page name here
			$pagename ='';
			
			//echo strlen($rtitlelink);
			//echo "<br>--";
			//echo $rtitlelink;
			//echo "--";
			if(strlen($rtitlelink)>150 || strlen($rtitlelink)<30 ){
				//echo "loop";
				if($loop < 4){
					//echo "loop:".$loop;
					//need to loop becuase we got the wrong link. tripadvisor probably blocked us.
					$localorphantom ="remote";
					sleep(1);
					$loop = $loop + 1;
					goto phantom;
				}
				$rtitlelink='';
				echo esc_html__('Error 103a: Unable to read TripAdvisor page. Please refresh or contact support if you keep seeing this message.', 'wp-tripadvisor-review-slider');
					//echo $html;
				die();
			}
			//echo $rtitlelink;
			
			//echo $spot;
			//die();
			
			$parseurl = parse_url($currenturl);
			$newurl = $parseurl['scheme'].'://'.$parseurl['host'].$rtitlelink;
			$page2url = $parseurl['scheme'].'://'.$parseurl['host'].$nextbtnlink;
			
			//$response =array("page1"=>$newurl,"page2"=>$page2url);
			
			$response =array("page1"=>$newurl,"page2"=>$page2url,"totalreviews"=>$totalreviews,"avgrating"=>$avgrating,"pagename"=>$pagename);
			$html->clear(); 
			unset($html);
			
		}
			
			//print_r($response);
			//die();

			//create new link based on $currenturl and $rtitlelink
		return $response;
	}
	
	
	
	
		/**
	 * Download reviews for all saved sources (cron / legacy).
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wptripadvisor_download_tripadvisor_master() {
		$crawls = $this->wptripadvisor_get_crawls();
		$messages = array();
		foreach ( $crawls as $pageid => $source ) {
			if ( ! is_array( $source ) || empty( $source['url'] ) ) {
				continue;
			}
			$result = $this->wptripadvisor_download_one_source(
				$source['url'],
				$pageid,
				isset( $source['businessname'] ) ? $source['businessname'] : ''
			);
			if ( ! empty( $result['ackmsg'] ) ) {
				$messages[] = $result['ackmsg'];
			}
		}
		if ( ! empty( $messages ) ) {
			$this->errormsg = implode( ' ', $messages );
		}
	}

	/**
	 * Crawl one TripAdvisor URL, insert reviews, and store source avg/total.
	 *
	 * @param string $tempurl  Business URL.
	 * @param string $pageid   Stable page id (d########).
	 * @param string $pagename Business display name.
	 * @return array
	 */
	public function wptripadvisor_download_one_source( $tempurl, $pageid = '', $pagename = '' ) {
		ini_set( 'memory_limit', '800M' );
		set_time_limit( 180 );

		$result = array(
			'ack'    => 'success',
			'ackmsg' => '',
			'avg'    => '',
			'total'  => '',
		);

		$tempurl = trim( $tempurl );
		if ( ! filter_var( $tempurl, FILTER_VALIDATE_URL ) ) {
			$result['ack']    = 'error';
			$result['ackmsg'] = __( 'Please enter a valid URL.', 'wp-tripadvisor-review-slider' );
			return $result;
		}

		$tempurl = strtok( $tempurl, '?' );
		$tempurl = preg_replace( '/#REVIEWS$/i', '', $tempurl );

		if ( $pageid === '' ) {
			$pageid = $this->wptripadvisor_extract_pageid_from_url( $tempurl );
		}
		if ( $pagename === '' ) {
			$pagename = $this->wptripadvisor_extract_businessname_from_url( $tempurl );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$totalinsert = 0;

		$type           = 'TripAdvisor';
		$listedurl      = $tempurl;
		$pagenum        = 1;
		$perpage        = '';
		$savedpageid    = $pageid;
		$nhful          = '';
		$fid            = '';
		$blockstoinsert = '';
		$nextpageurl    = '';
		// Manual downloads must use iscron=no. iscron=yes hits the crawl server's
		// throttle and returns empty reviews immediately.
		$iscron         = 'no';

		// TripAdvisor often returns forceloop=yes + nextpageurl (ShowUserReviews) with no
		// reviews on the first call. ShowUserReviews pages return ~5 reviews each, so keep
		// following nextpageurl until we have at least 10 reviews (all TripAdvisor types).
		$all_reviews     = array();
		$source_avg      = '';
		$source_total    = '';
		$crawl_debug_log = array();
		$min_reviews     = 10;
		$max_loops       = 6;
		$reviewscrawl    = null;

		for ( $loop = 1; $loop <= $max_loops; $loop++ ) {
			$reviewscrawl = $this->wprpfree_getapps_getrevs_page_tripadvisor( $type, $listedurl, $pagenum, $perpage, $savedpageid, $nhful, $fid, $blockstoinsert, $nextpageurl, $iscron );

			if ( ! empty( $this->last_crawl_debug ) ) {
				$crawl_debug_log[] = $this->last_crawl_debug;
			}

			if ( ! is_array( $reviewscrawl ) ) {
				break;
			}

			if ( $source_avg === '' && ! empty( $reviewscrawl['avg'] ) ) {
				$source_avg = $reviewscrawl['avg'];
			}
			if ( $source_total === '' && ! empty( $reviewscrawl['total'] ) ) {
				$source_total = $reviewscrawl['total'];
			}

			if ( ! empty( $reviewscrawl['reviews'] ) && is_array( $reviewscrawl['reviews'] ) ) {
				$all_reviews = array_merge( $all_reviews, $reviewscrawl['reviews'] );
			}

			$stoploop = isset( $reviewscrawl['stoploop'] ) ? $reviewscrawl['stoploop'] : '';
			if ( $stoploop === 'stop' ) {
				break;
			}

			$forceloop       = isset( $reviewscrawl['forceloop'] ) ? $reviewscrawl['forceloop'] : '';
			$next_from_crawl = isset( $reviewscrawl['nextpageurl'] ) ? $reviewscrawl['nextpageurl'] : '';

			// First page often only returns the ShowUserReviews link. Pro keeps pagenum at 1
			// for that follow-up so the crawler still reads the source average and total.
			if ( $forceloop === 'yes' && $next_from_crawl !== '' ) {
				$nextpageurl = $next_from_crawl;
				continue;
			}

			// Keep paging until we have at least 10 reviews (Pro-style pagination, free cap).
			if ( count( $all_reviews ) < $min_reviews && $next_from_crawl !== '' ) {
				$nextpageurl = $next_from_crawl;
				$pagenum++;
				continue;
			}

			break;
		}

		// Free version: keep at most 10 reviews.
		if ( count( $all_reviews ) > $min_reviews ) {
			$all_reviews = array_slice( $all_reviews, 0, $min_reviews );
		}

		$result['crawl_debug'] = $crawl_debug_log;

		if ( empty( $all_reviews ) ) {
			$result['ack']    = 'error';
			$result['ackmsg'] = __( 'Unable to find any reviews. Please try again or contact support.', 'wp-tripadvisor-review-slider' );
			return $result;
		}

		// Normalize so insert logic below can use $reviewscrawl['reviews'].
		$reviewscrawl            = is_array( $reviewscrawl ) ? $reviewscrawl : array();
		$reviewscrawl['reviews'] = $all_reviews;
		$reviewscrawl['avg']     = $source_avg;
		$reviewscrawl['total']   = $source_total;

		$result['avg']   = $source_avg;
		$result['total'] = $source_total;

		$reviews = array();
		foreach ( $reviewscrawl['reviews'] as $review ) {
			$rtext         = $review['review_text'];
			$review_length = str_word_count( $rtext );
			$timestamp     = $this->myStrtotime( $review['updated'] );
			$unixtimestamp = $timestamp;
			$timestamp     = date( 'Y-m-d H:i:s', $timestamp );

			// Dedupe by name + length (+ pageid). Do not rely on date so edited dates
			// cannot cause the same review to be re-inserted on download.
			if ( $pageid !== '' ) {
				$checkrow = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$table_name} WHERE reviewer_name = %s AND review_length = %d AND pageid = %s",
						$review['reviewer_name'],
						$review_length,
						$pageid
					)
				);
			} else {
				$checkrow = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$table_name} WHERE reviewer_name = %s AND (review_length = %d OR created_time = %s)",
						$review['reviewer_name'],
						$review_length,
						$timestamp
					)
				);
			}

			if ( ! empty( $checkrow ) ) {
				continue;
			}

			if ( ! isset( $review['userpic'] ) ) {
				$review['userpic'] = '';
			}

			// Defense in depth: re-sanitize immediately before the DB write, even though
			// the crawler response is already sanitized when it is first mapped.
			$reviews[] = array(
				'pageid'             => $pageid,
				'pagename'           => trim( $pagename ),
				'reviewer_name'      => sanitize_text_field( $review['reviewer_name'] ),
				'userpic'            => esc_url_raw( $review['userpic'] ),
				'rating'             => (int) $review['rating'],
				'created_time'       => $timestamp,
				'created_time_stamp' => $unixtimestamp,
				'review_text'        => sanitize_textarea_field( trim( $review['review_text'] ) ),
				'hide'               => '',
				'review_length'      => $review_length,
				'type'               => 'TripAdvisor',
				'review_title'       => sanitize_text_field( $review['review_title'] ),
				'mediaurlsarrayjson' => $review['mediaurlsarrayjson'],
				'from_url'           => isset( $review['from_url'] ) ? esc_url_raw( $review['from_url'] ) : esc_url_raw( $listedurl ),
			);
		}

		$reviewtexts   = array();
		$insertreviews = array();
		foreach ( $reviews as $stat ) {
			if ( ! in_array( $stat['review_text'], $reviewtexts, true ) ) {
				$insertreviews[] = $stat;
			}
			$reviewtexts[] = $stat['review_text'];
		}

		foreach ( $insertreviews as $stat ) {
			$insertnum    = $wpdb->insert( $table_name, $stat );
			$totalinsert += (int) $insertnum;
		}

		// Persist source avg/total for badges.
		if ( $pageid !== '' ) {
			$this->updatetotalavgreviews( 'TripAdvisor', $pageid, $source_avg, $source_total, $pagename );

			$crawls = $this->wptripadvisor_get_crawls();
			if ( ! isset( $crawls[ $pageid ] ) ) {
				$crawls[ $pageid ] = array(
					'pageid'       => $pageid,
					'businessname' => $pagename,
					'url'          => $listedurl,
				);
			}
			$crawls[ $pageid ]['avg']          = $source_avg;
			$crawls[ $pageid ]['total']        = $source_total;
			$crawls[ $pageid ]['businessname'] = $pagename;
			$crawls[ $pageid ]['url']          = $listedurl;
			$crawls[ $pageid ]['last_download'] = time();
			$this->wptripadvisor_save_crawls( $crawls );
		}

		$numreturned = count( $reviewscrawl['reviews'] );
		$result['ackmsg'] = sprintf(
			/* translators: 1: reviews found, 2: new reviews inserted */
			__( '%1$d reviews found. %2$d new reviews downloaded. Check the Review List page.', 'wp-tripadvisor-review-slider' ),
			$numreturned,
			$totalinsert
		);
		$this->errormsg = $result['ackmsg'];

		return $result;
	}

	/**
	 * Sanitize a JSON-encoded array of media URLs coming from the remote
	 * crawling service before it is stored or re-encoded.
	 *
	 * @param string $json Raw JSON string of URLs.
	 * @return string Re-encoded JSON of sanitized URLs, or '' if invalid.
	 */
	private function wprevpro_sanitize_media_urls_json( $json ) {
		$decoded = json_decode( $json, true );
		if ( ! is_array( $decoded ) ) {
			return '';
		}

		$safe = array();
		foreach ( $decoded as $url ) {
			if ( is_string( $url ) && $url !== '' ) {
				$safe[] = esc_url_raw( $url );
			}
		}

		return wp_json_encode( $safe );
	}

	/**
	 * Store source avg/total for badges (option + averages table).
	 *
	 * @param string $type     Review type.
	 * @param string $pageid   Page id.
	 * @param string $avg      Source average from crawler.
	 * @param string $total    Source total from crawler.
	 * @param string $pagename Business name.
	 */
	public function updatetotalavgreviews( $type, $pageid, $avg, $total, $pagename = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$avg        = str_replace( ',', '.', (string) $avg );
		$option     = 'wptrip_total_avg_reviews';

		$wppro_total_avg_reviews_array = get_option( $option );
		if ( $wppro_total_avg_reviews_array ) {
			$wppro_total_avg_reviews_array = json_decode( $wppro_total_avg_reviews_array, true );
		}
		if ( ! is_array( $wppro_total_avg_reviews_array ) ) {
			$wppro_total_avg_reviews_array = array();
		}

		$ratingsarray = array();
		$fbreviews    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT rating, type FROM {$table_name} WHERE hide != %s AND pageid = %s",
				'yes',
				$pageid
			)
		);
		$pagetype = $type;
		foreach ( $fbreviews as $fbreview ) {
			if ( $fbreview->rating > 0 ) {
				$ratingsarray[] = (float) $fbreview->rating;
			}
			if ( ! empty( $fbreview->type ) ) {
				$pagetype = $fbreview->type;
			}
		}

		$avgdb   = 0;
		$totaldb = 0;
		if ( count( $ratingsarray ) > 0 ) {
			$avgdb   = round( array_sum( $ratingsarray ) / count( $ratingsarray ), 3 );
			$totaldb = count( $ratingsarray );
		}

		if ( ! isset( $wppro_total_avg_reviews_array[ $pageid ] ) ) {
			$wppro_total_avg_reviews_array[ $pageid ] = array();
		}
		$wppro_total_avg_reviews_array[ $pageid ]['total_indb'] = $totaldb;
		$wppro_total_avg_reviews_array[ $pageid ]['avg_indb']   = $avgdb;
		if ( floatval( $avg ) > 0 ) {
			$wppro_total_avg_reviews_array[ $pageid ]['avg'] = round( floatval( $avg ), 3 );
		}
		if ( intval( $total ) > 0 ) {
			$wppro_total_avg_reviews_array[ $pageid ]['total'] = intval( $total );
		}

		update_option( $option, wp_json_encode( $wppro_total_avg_reviews_array, JSON_FORCE_OBJECT ) );

		$valuearray = array(
			'btp_id'     => $pageid,
			'btp_name'   => $pagename,
			'pagetype'   => $pagetype,
			'total'      => isset( $wppro_total_avg_reviews_array[ $pageid ]['total'] ) ? $wppro_total_avg_reviews_array[ $pageid ]['total'] : '',
			'total_indb' => $totaldb,
			'avg'        => isset( $wppro_total_avg_reviews_array[ $pageid ]['avg'] ) ? $wppro_total_avg_reviews_array[ $pageid ]['avg'] : '',
			'avg_indb'   => $avgdb,
			'numr1'      => '',
			'numr2'      => '',
			'numr3'      => '',
			'numr4'      => '',
			'numr5'      => '',
		);
		$this->updatetotalavgreviewstableinsert( 'page', $valuearray );
	}

	/**
	 * Insert/replace a row in wptripadvisor_total_averages.
	 *
	 * @param string $btp_type   page|template|badge.
	 * @param array  $valuearray Row values.
	 */
	public function updatetotalavgreviewstableinsert( $btp_type, $valuearray ) {
		global $wpdb;
		$table_name_totalavg = $wpdb->prefix . 'wptripadvisor_total_averages';
		$data                = array(
			'btp_id'     => isset( $valuearray['btp_id'] ) ? $valuearray['btp_id'] : '',
			'btp_name'   => isset( $valuearray['btp_name'] ) ? $valuearray['btp_name'] : '',
			'btp_type'   => $btp_type,
			'pagetype'   => isset( $valuearray['pagetype'] ) ? $valuearray['pagetype'] : '',
			'total_indb' => isset( $valuearray['total_indb'] ) ? (string) $valuearray['total_indb'] : '',
			'total'      => isset( $valuearray['total'] ) ? (string) $valuearray['total'] : '',
			'avg_indb'   => isset( $valuearray['avg_indb'] ) ? (string) $valuearray['avg_indb'] : '',
			'avg'        => isset( $valuearray['avg'] ) ? (string) $valuearray['avg'] : '',
			'numr1'      => isset( $valuearray['numr1'] ) ? (string) $valuearray['numr1'] : '',
			'numr2'      => isset( $valuearray['numr2'] ) ? (string) $valuearray['numr2'] : '',
			'numr3'      => isset( $valuearray['numr3'] ) ? (string) $valuearray['numr3'] : '',
			'numr4'      => isset( $valuearray['numr4'] ) ? (string) $valuearray['numr4'] : '',
			'numr5'      => isset( $valuearray['numr5'] ) ? (string) $valuearray['numr5'] : '',
		);
		$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$wpdb->replace( $table_name_totalavg, $data, $format );
	}

	//Added 4/2/2024 using pro version to download reviews.
	//for calling remote get and returning array of reviews to insert, calling Crawler now crawl.ljapps.com
	public function wprpfree_getapps_getrevs_page_tripadvisor($type,$listedurl,$pagenum,$perpage,$savedpageid,$nhful,$fid,$blockstoinsert,$nextpageurl,$iscron){
		$result['ack']='success';
		set_time_limit(150);
			$errormsg='';
			$reviewsarraytemp = Array();
			$nhful='new';
			
			if (filter_var($listedurl, FILTER_VALIDATE_URL)) {
					
				$stripvariableurl = stripslashes($listedurl);
				$listedurl = strtok($stripvariableurl, '?');	//remove all parameters
				$listedurl = preg_replace('/#REVIEWS$/i', '', $listedurl);
				
				
				if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']!=''){
					$ip_server = $_SERVER['SERVER_ADDR'];
				} else {
					//get url of site.
					$ip_server = urlencode(get_site_url());
				}
				$siteurl = urlencode(get_site_url());
				
				//scrapeurl — encode nextpageurl so ShowUserReviews links survive the query string
				$tempurlval = 'https://crawl.ljapps.com/crawlrevs?rip='.$ip_server.'&surl='.$siteurl.'&scrapeurl='.urlencode($listedurl).'&stype=tripadvisor&nhful='.$nhful.'&locationtype=&scrapequery=&tempbusinessname=&pagenum='.$pagenum.'&nextpageurl='.urlencode($nextpageurl).'&iscron='.$iscron.'&sfp=free&nobot=1';
				
				//https://crawl.ljapps.com/crawlrevs?rip=127.0.0.1&surl=https%3A%2F%2Fwptest.ljapps.com&scrapeurl=https://www.tripadvisor.com/Hotel_Review-g7928511-d27689503-Reviews-Feridhoo_Beach_Villa-Feridhoo.html&stype=tripadvisor&sfp=pro&nhful=new&locationtype=&scrapequery=&tempbusinessname=&pagenum=1&nextpageurl=&iscron=yes&sfp=free&nobot=1
				
				
				
				//echo $tempurlval;
				//die();
				
				$serverresponse='';
				$args = array(
					'timeout'     => 120,
					'headers' => array( 
						'Content-Type' => ' application/json',
						'Accept'=> 'application/json'
					)
				); 
				$response = wp_remote_get( $tempurlval, $args );
				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$headers = $response['headers']; // array of http header lines
					$serverresponse    = $response['body']; // use the content
				} else {
					//must have been an error
					$results['ack'] ='error';
					$results['ackmsg'] ='Error 0001a: trouble contacting crawling server with remote_get. Please try again or contact support. '.$response->get_error_message();
					$results['crawl_debug'] = array(
						'request_url' => $tempurlval,
						'raw'         => '',
						'error'       => is_wp_error( $response ) ? $response->get_error_message() : 'unknown',
					);
					$results = json_encode($results);
					echo $results;
					die();
				}
				
				//testing------
				//$serverresponse='';

		//check for block or timeout
		//====================
		if (strpos($serverresponse, "Please wait while your request is being verified") !== false || !isset($serverresponse) || $serverresponse==''  || strpos($serverresponse, "Access denied by Imunify360 bot-protection.") !== false || strpos($serverresponse, "415 Unsupported Media Type") !== false) {
		   //this site is greylisted by imunify360 on cloudways, call backup digital ocean server
		   $response = wp_remote_get( 'https://ocean.ljapps.com/crawlrevs.php?rip='.$ip_server.'&surl='.$siteurl.'&scrapeurl='.urlencode($listedurl).'&stype=tripadvisor&nhful='.$nhful.'&locationtype=&scrapequery=&tempbusinessname=&pagenum='.$pagenum.'&nextpageurl='.urlencode($nextpageurl).'&iscron='.$iscron.'&sfp=free&nobot=1', array( 'timeout' => 150 ) );
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$serverresponse    = $response['body']; // use the content
			}
		}
		//=========================	
	
	
	
	
	
				$serverresponsearray = json_decode($serverresponse, true);
				$this->last_crawl_debug = array(
					'request_url' => $tempurlval,
					'iscron'      => $iscron,
					'raw'         => $serverresponse,
					'parsed'      => $serverresponsearray,
				);

				if($serverresponse=='' || !is_array($serverresponsearray)){
					$results['ack'] ='error';
					$results['ackmsg'] ='Error 0001: trouble contacting crawling server. Please try again or contact support.';
					$results['crawl_debug'] = $this->last_crawl_debug;
					$results = json_encode($results);
					echo $results;
					die();
				}
				//catch limit error
				if($serverresponsearray['ack']=='error'){
					$results['ack'] ='error';
					$results['ackmsg'] ='Error 0002: '.$serverresponsearray['ackmessage'];
					$results['crawl_debug'] = $this->last_crawl_debug;
					$results = json_encode($results);
					echo $results;
					die();
				}
				if(!isset($serverresponsearray['result']) || !is_array($serverresponsearray['result'])){
					$results['ack'] ='error';
					$results['ackmsg'] ='Error 0002b: trouble finding reviews. Contact support with this error code and the search terms or place id you are using.';
					$results['crawl_debug'] = $this->last_crawl_debug;
					$results = json_encode($results);
					echo $results;
					die();
				}
				//catch error
				if($serverresponsearray['result']['ack']=='error'){
					$results['ack'] ='error';
					$results['ackmsg'] ='Error 0003: Please try again. '.$serverresponsearray['ackmessage'].' : '.$serverresponsearray['result']['ackmsg'];
					$results['crawl_debug'] = $this->last_crawl_debug;
					$results = json_encode($results);
					echo $results;
					die();
				}
				//made it this far assume we have reviews.
				$crawlerresultarray = $serverresponsearray['result'];
				
				//pass back forceloop if set
				$result['forceloop']='';
				if(isset($crawlerresultarray['forceloop'])){
					$result['forceloop']=$crawlerresultarray['forceloop'];
				}
				
				if($result['forceloop']!='yes'){
					if(!isset($crawlerresultarray['reviews']) || !is_array($crawlerresultarray['reviews'])){
						$results['ack'] ='error';
						$results['ackmsg'] ='Error 0004 Trip: trouble finding reviews. Contact support with this error code and the search terms or place id you are using. ';
						$results['crawl_debug'] = isset( $this->last_crawl_debug ) ? $this->last_crawl_debug : null;
						$results = json_encode($results);
						echo $results;
						die();
					}
				}
				
				//need totals and avg for this place $getreviewsarray['total']
				$result['total']='';
				$result['avg']='';
				if(isset($crawlerresultarray['avg'])){
					$result['avg']=$crawlerresultarray['avg'];
				}
				if(isset($crawlerresultarray['total'])){
					$result['total']=$crawlerresultarray['total'];
				}
				
				//pass back URL used
				if(isset($crawlerresultarray['callurl'])){
					$result['callurl']=$crawlerresultarray['callurl'];
				}
				//pass back next URL used
				if(isset($crawlerresultarray['nextpageurl'])){
					$result['nextpageurl']=$crawlerresultarray['nextpageurl'];
				}
				//pass back stoploop if set
				if(isset($crawlerresultarray['stoploop'])){
					$result['stoploop']=$crawlerresultarray['stoploop'];
				}
				//pass back jumpnum if set
				$result['jumpnum']='';
				if(isset($crawlerresultarray['jumpnum'])){
					$result['jumpnum']=$crawlerresultarray['jumpnum'];
				}
				//pass back proxy if set
				$result['proxy']='';
				if(isset($crawlerresultarray['proxy'])){
					$result['proxy']=$crawlerresultarray['proxy'];
				}
				
				$x=0;
				$reviewsarray= Array();
				$crawlerreviewsarray = Array();
				if(isset($crawlerresultarray['reviews']) && is_array($crawlerresultarray['reviews'])){
					$crawlerreviewsarray = $crawlerresultarray['reviews'];
				}
				$numreturned = count($crawlerreviewsarray);
				
				foreach ($crawlerreviewsarray as $review) {
					
					$tempownerres='';
					if(isset($review['owner_response']) && $review['owner_response']!=''){
						$tempownerres = sanitize_textarea_field( $review['owner_response'] );
					}
					$templocation ='';
					if(isset($review['location']) && $review['location']!=''){
						$templocation = sanitize_text_field( $review['location'] );
					}	
					$tempmediaurlsarrayjson ='';
					if(isset($review['mediaurlsarrayjson']) && $review['mediaurlsarrayjson']!=''){
						$tempmediaurlsarrayjson = $this->wprevpro_sanitize_media_urls_json( $review['mediaurlsarrayjson'] );
					}					
					
					// Untrusted data from the remote crawling service: sanitize every field
					// before it enters local storage, regardless of transport trust.
					$reviewsarray[] = [
					 'reviewer_name' => isset( $review['user_name'] ) ? sanitize_text_field( $review['user_name'] ) : '',
					 'reviewer_id' => '',
					 'reviewer_email' => '',
					 'userpic' => isset( $review['userpic'] ) ? esc_url_raw( $review['userpic'] ) : '',
					 'rating' => isset( $review['rating'] ) ? (int) $review['rating'] : 0,
					 'updated' => isset( $review['created_time'] ) ? sanitize_text_field( $review['created_time'] ) : '',
					 'review_text' => isset( $review['review_text'] ) ? sanitize_textarea_field( $review['review_text'] ) : '',
					 'review_title' => isset( $review['review_title'] ) ? sanitize_text_field( $review['review_title'] ) : '',
					 'from_url' => $listedurl,
					 'from_url_review' => isset( $review['user_link'] ) ? esc_url_raw( $review['user_link'] ) : '',
					 'language_code' => '',
					 'location' => $templocation,
					 'recommendation_type' => '',
					 'company_title' =>  '',
					 'company_url' => '',
					 'company_name' => '',
					 'mediaurlsarrayjson' => $tempmediaurlsarrayjson,
					 'owner_response' => $tempownerres,
					 ];
					
					$x++;
				}

			$result['reviews'] = $reviewsarray;

			}
		return $result;
	}
	
	/**
	 * download tripadvisor reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wptripadvisor_download_tripadvisor_master_old() {
		ini_set('memory_limit','800M');
		set_time_limit(60);
		//make sure file get contents is turned on for this host
		$errormsg ='';
		$ShowUserReviews = false;
		$HotelUserReviews = false;
		$onfirstpage = false;
		$attractionproductreviews=false;
		$attractionreviews=false;
		$totalinsert = 0;
		
			global $wpdb;
			$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
			$options = get_option('wptripadvisor_tripadvisor_settings');
			
			$tempurl = trim($options['tripadvisor_business_url']);
			
			if (strpos($tempurl, 'ShowUserReviews') !== false) {
				$ShowUserReviews=true;
			} else if (strpos($tempurl, 'AttractionProductReview') !== false) {
				$attractionproductreviews=true;
			} else if (strpos($tempurl, 'Attraction_Review') !== false) {
				$attractionreviews=true;
			}
			
			//make sure you have valid url, if not display message
			if (filter_var($tempurl, FILTER_VALIDATE_URL)) {
			  // you're good
			  //echo "valid url";
			  //if($options['tripadvisor_radio']=='yes'){
				//echo "passed both tests";
				$stripvariableurl = strtok($options['tripadvisor_business_url'], '?');
				$urlvalue = trim($stripvariableurl);
				if (strpos($stripvariableurl, 'ShowUserReviews') === false  && strpos($stripvariableurl, 'AttractionProductReview') == false) {
					//this is a hotel or vacation rental review, we have to now find the ShowUserReviews url
					//====we are now using this for all types in order to get userpics
					$urlarray = $this->wprevpro_download_tripadvisor_showuserreviews_url($stripvariableurl);
					$urlvalue = $urlarray['page1'];
					//if($urlarray['page2']!=""){
						//$tripadvisorurl[1] = $urlarray['page2'];
					//} else {
						//$tripadvisorurl[1] ='';
					//}
					$ShowUserReviews = true;
					sleep(1);
				}
				//echo $urlvalue;
				//die();

				//we are now looping to make sure we are at first showuserreviewpage.
				$loop = 0;
				start:
				//goto start;
				
				
				//loop to grab pages
				$reviews = [];
				$n=1;


				$localorphantom = "local";
				$phatomtry = 1;
				phantom:
				
				if($phatomtry == 1){
					$apitoken = 'a-demo-key-with-low-quota-per-ip-address';
					$phantomtempurlvalue = "https://phantomjscloud.com/api/browser/v2/".$apitoken."/?request={url:%22".urlencode($urlvalue)."%22,renderType:%22html%22,requestSettings:{doneWhen:[{event:%22domReady%22}]}}";
				} else {
					//calling server so we can hide phantomjs api key
					$phantomtempurlvalue = "https://crawl.ljapps.com/freetripphantom.php?urlval=".urlencode($urlvalue).'&sfp=free&nobot=1';
					//echo $phantomtempurlvalue;
					//die();
				}
					//echo $phantomtempurlvalue;
					//die();

					$args = array(
						'timeout'     => 60,
					);
					if($localorphantom =="local"){
						//echo "local";
						//echo $urlvalue;
						$response = wp_remote_get( $urlvalue,$args );
						//$fileurlcontents = $response['body']; // use the content
						//echo $fileurlcontents;
						//die();
					} else {
						//echo "phantom";
						//echo $phantomtempurlvalue;
						$response = wp_remote_get( $phantomtempurlvalue,$args );
					}

					if ( is_array( $response ) ) {
					  $header = $response['headers']; // array of http header lines
					  $fileurlcontents = $response['body']; // use the content
					} else {
						//echo esc_html__('Error finding reviews. Please contact plugin support.', 'wp-tripadvisor-review-slider');
						$errormsg = $errormsg . ' Error finding reviews. Please contact plugin support.';
						$this->errormsg = $errormsg;
						//die();
					}

										
					$html = wptrip_str_get_html($fileurlcontents);
					
					//unset($fileurlcontents);
					//echo $fileurlcontents;
					//var_dump($html);
					//die();
					
					if (!is_object($html)) {
						if(count($reviewcontainerdiv)<1 && $phatomtry<4){
						
							if($phatomtry==1){
								//try local one more time.
								$localorphantom="local";
							} else {
								$localorphantom="phantom";
							}
							$phatomtry = $phatomtry+1;
							sleep(1);
							goto phantom;
						}
				
						echo "Unable to retrieve TripAdvisor reviews for that URL. Please refresh the page to try again or contact support.";
						die();
					}
					
					$parseurl = parse_url($urlvalue);
					
			
					//check to make sure we are on first page here.
					if($ShowUserReviews && $loop == 0){
						//echo "here";
						if($html->find("a[class=pageNum first current]",0)){
							//we must be on first page. se we continue on
							$onfirstpage = true;
							//echo "here1";
						} else if($html->find("a[class=pageNum first]",0)){
							//we must find href for page one.
							$newurl = $html->find("a[class=pageNum first]",0)->href;
							$urlvalue = $parseurl['scheme'].'://'.$parseurl['host'].$newurl;
							$onfirstpage = true;
							$loop++;
							//echo "here2:".$urlvalue;
							//echo "<br>";
							sleep(1);
							goto start;
						}							
					}
					//die();
					
					//find tripadvisor business name and add to db under pagename
					$pagename ='';
					if($html->find('.heading_title', 0)){
						$pagename = $html->find('.heading_title', 0)->plaintext;
					}
					if($pagename==''){
						if($html->find('a[class=HEADING]',0)){
						$pagename = $html->find('a[class=HEADING]',0)->plaintext;
						}
					}
					if($pagename==''){
						if($html->find('h1[id=HEADING]',0)){
						$pagename = $html->find('h1[id=HEADING]',0)->plaintext;
						}
					}
					
					//echo "pagename:".$pagename;
					//die();

					
					//find lazyload image js variable and convert to array #\slazyImgs\s*=\s*(.*?);\s*$#s
					
					$startstringpos = stripos("$html","var lazyImgs = [") + 16;
					$choppedstr = substr("$html", $startstringpos);
					$endstringpos = stripos("$choppedstr","]");
					$finalstring = trim(substr("$html", $startstringpos, $endstringpos));
					$finalstring =str_replace(":true",':"true"',$finalstring);
					$finalstring ="[".str_replace(":false",':"false"',$finalstring)."]";
					$jsonlazyimg  = json_decode($finalstring, true);
					
					//echo "finalstring:";
					//echo $finalstring;
					//echo "<br>";
					//print_r($jsonlazyimg);
					//die();
					
					$reviewcontainerdiv = Array();
					//fix for hotel reviews..
					//if($HotelUserReviews){
					//	$reviewcontainerdiv = $html->find('div.hotels-hotel-review-community-content-review-list-parts-SingleReview__reviewContainer--2LYmA');
					//} else {
					
					//echo $html;
					//die();
					//echo "here3";
					if($attractionproductreviews ){
						//echo "here4";
						if($html->find('div.LbPSX', 0)){
							//another attraction review
							$reviewcontainerdiv = $html->find('div[data-automation=reviewCard]');
							//echo "here5";
						}
						//die();
							
					} else {
						if($html->find('div.reviewSelector', 0)){
							$reviewcontainerdiv = $html->find('div.reviewSelector');
							//echo "here1";
						} else if($html->find('div.review-container', 0)){
							$reviewcontainerdiv = $html->find('div.review-container');
							//echo "here2";
						} else if($html->find('div._1c8_1ITO', 0)){
							//print_r($html->find('div._1c8_1ITO', 0));
							$reviewcontainerdiv = $html->find('div._1c8_1ITO', 0)->children;
						} else if($html->find("div[class=Dq9MAugU T870kzTX LnVzGwUB]",0)){
							//echo "here3";
								//another attraction review
								$reviewcontainerdiv = $html->find("div[class=Dq9MAugU T870kzTX LnVzGwUB]");
						} else if($html->find('div[class=eVykL Gi z cPeBe MD cwpFC]',0)){
								//another attraction review
								//eVykL Gi z cPeBe MD cwpFC
								//echo "here4";
								$reviewcontainerdiv = $html->find('div[class=eVykL Gi z cPeBe MD cwpFC]');
						} else if($html->find('div.bPhtn', 0)){
								//another attraction review
								$reviewcontainerdiv = $html->find('div.bPhtn', 0)->children;
							
						} else if($html->find('div.dHjBB', 0)){
								//another attraction review
								$reviewcontainerdiv = $html->find('div.dHjBB', 0)->children;
						} else if($html->find('div.LbPSX', 0)){
								//another attraction review
								$reviewcontainerdiv = $html->find('div.LbPSX', 0)->children;
						}
					}
					
					if(count($reviewcontainerdiv)<1 && $phatomtry<4){
						
						if($phatomtry==1){
							//try local one more time.
							$localorphantom="local";
						} else {
							$localorphantom="phantom";
						}
						$phatomtry = $phatomtry+1;
						sleep(1);
						goto phantom;

					} else {
						//$errormsg = $errormsg . ' Error 103a: Unable to read TripAdvisor page.';
						//$this->errormsg = $errormsg;
						
					}
					
					//echo $urlvalue;
					//echo $fileurlcontents;
					//die();
					//echo "here5";

					//echo $html;
					
					//$html->clear();
					//unset($html);

					//echo "<br><br><br>";
					//print_r($reviewcontainerdiv[0]);
					//die();
					
					
					//echo memory_get_usage();
					//echo "<br>";
					//echo memory_get_peak_usage();
					$foundavatar = false;
					$i = 1;
					foreach ($reviewcontainerdiv as $review) {
						
					
						$tempcheckclass = $review->class;
						$pos = strpos($tempcheckclass, 'recent_review'); //check to see if partial review
						if ($pos === false) {
							if ($i > 21) {
									break;
							}
							$user_name='';
							$userimage='';
							$rating='';
							$datesubmitted='';
							$rtext='';
							// Find user_name
							if($review->find('div.username', 0)){
								$user_name = $review->find('div.username', 0)->plaintext;
							}
							if($user_name==''){
								if($review->find('div.info_text', 0)){
									$user_name = $review->find('div.info_text', 0)->find('div', 0)->plaintext;
								}
							}
							
							if($user_name==''){
								if($review->find("span[class=DrjyGw-P _1SRa-qNz NGv7A1lw _2yS548m8 _2cnjB3re _1TAWSgm1 _1Z1zA2gh _2-K8UW3T _2AAjjcx8]", 0)){
									$user_name = $review->find("span[class=DrjyGw-P _1SRa-qNz NGv7A1lw _2yS548m8 _2cnjB3re _1TAWSgm1 _1Z1zA2gh _2-K8UW3T _2AAjjcx8]", 0)->plaintext;
								}
							}
							if($user_name==''){
								if($review->find("a[class=ui_header_link _1r_My98y]", 0)){
									$user_name = $review->find("a[class=ui_header_link _1r_My98y]", 0)->plaintext;
								}
							}
							if($user_name==''){
								if($review->find("a[class=ui_header_link bPvDb]", 0)){
									$user_name = $review->find("a[class=ui_header_link bPvDb]", 0)->plaintext;
								}
							}
							//for attraction 2
							if($user_name==''){
								if($review->find("a[class=iPqaD _F G- ddFHE eKwUx btBEK fUpii]", 0)){
									$user_name = $review->find("a[class=iPqaD _F G- ddFHE eKwUx btBEK fUpii]", 0)->plaintext;
								}
							}
							//for attraction 2 biGQs _P fiohW fOtGX
							if($user_name==''){
								if($review->find("span[class=biGQs _P fiohW fOtGX]", 0)){
									$user_name = $review->find("span[class=biGQs _P fiohW fOtGX]", 0)->plaintext;
								}
							}
							
							//echo "username:".$user_name;
							//die();
							
							// Find userimage ui_avatar, need to pull from lazy load varible
							if($review->find('div.ui_avatar', 0)){
								if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
									$userimageid = $review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->id;
									//strip id from 
									$userimageid = strrchr ($userimageid , "_" );
									//loop through array and return url
									if (isset($jsonlazyimg) && is_array($jsonlazyimg)){
									for ($x = 0; $x <= count($jsonlazyimg); $x++) {
										//get temp id
										$tempid = $jsonlazyimg[$x]['id'];
										$tempid = strrchr ($tempid , "_" );
										if($userimageid==$tempid){
											$userimage = $jsonlazyimg[$x]['data'];
											$x = count($jsonlazyimg) + 1;
										}
									} 
									}
								}
							}
							//echo "<br>userimage1:".$userimage;

							//if userimage not found check
							$checkstringpos =  strpos($userimage, 'base64');
							if($userimage =='' || $checkstringpos>0){
								if($review->find('div.ui_avatar', 0)){
									if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
										if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'}){
											$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'};
										} else {
											$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->src;
										}
									}
								}
								
							}
							//echo "<br>userimage2:".$userimage;
							//another fix
							if($userimage ==''){
								if($review->find('div.avatar', 0)){
									if($review->find('div.avatar', 0)->find('img.avatar', 0)){
										$userimage =$review->find('div.avatar', 0)->find('img.avatar', 0)->{'src'};
									}
								}
							}
							//echo "<br>userimage3:".$userimage;
							//one more try for hotels
							if($userimage =='' && $review->find('div.ui_avatar', 0)){
								if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
										$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->src;
								}
							}
							//echo "<br>userimage4:".$userimage;
							//if userimage not found check
							if($userimage =='' && $review->find('div.ui_avatar', 0)){
								if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
									if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'}){
										$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'};
									}
								}
								//echo "<br>userimage:".$userimage;
							}
							//echo "<br>userimage5:".$userimage;
							//another check for activities userimage
							if($userimage =='' && $review->find('picture._2f-Th360', 0)){
								if($review->find('picture._2f-Th360', 0)->find('img', 0)){
										$userimage =$review->find('picture._2f-Th360', 0)->find('img', 0)->src;
								}
							}
							//echo "<br>userimage6:".$userimage;
							//another check for activities userimage
							if($userimage =='' && $review->find("a[class=_3x5_awTA ui_social_avatar inline]", 0)){
								if($review->find("a[class=_3x5_awTA ui_social_avatar inline]", 0)->find('img', 0)){
										$userimage =$review->find("a[class=_3x5_awTA ui_social_avatar inline]", 0)->find('img', 0)->src;
								}
							}
							//echo "<br>userimage7:".$userimage;
							//another check for activities userimage
							if($userimage =='' && $review->find("a[class=ui_social_avatar inline]", 0)){
								if($review->find("a[class=ui_social_avatar inline]", 0)->find('img', 0)){
										$userimage =$review->find("a[class=ui_social_avatar inline]", 0)->find('img', 0)->src;
								}
							}
							//echo "<br>userimage8:".$userimage;
							//bugwz I ui_social_avatar inline
							if($userimage =='' && $review->find("a[class=bugwz I ui_social_avatar inline]", 0)){
								if($review->find("a[class=bugwz I ui_social_avatar inline]", 0)->find('img', 0)){
										$userimage =$review->find("a[class=bugwz I ui_social_avatar inline]", 0)->find('img', 0)->src;
								}
							}
							//echo "<br>userimage9:".$userimage;
							//another check for activities userimage
							if($userimage =='' && $review->find("a[class=iPqaD _F G- ddFHE eKwUx]", 0)){
								if($review->find("a[class=iPqaD _F G- ddFHE eKwUx]", 0)->find('img', 0)){
										$userimage =$review->find("a[class=iPqaD _F G- ddFHE eKwUx]", 0)->find('img', 0)->src;
								}
							}
							//echo "<br>userimage10:".$userimage;
							//another check for activities userimage
							if($userimage =='' && $review->find("div[class=FGwzt PaRlG]", 0)){
								if($review->find("div[class=FGwzt PaRlG]", 0)->find('img', 0)){
										$userimage =$review->find("div[class=FGwzt PaRlG]", 0)->find('img', 0)->src;
								}
							}
							

							// find rating
							if($review->find('span.ui_bubble_rating', 0)){
								$temprating = $review->find('span.ui_bubble_rating', 0)->class;
								$int = filter_var($temprating, FILTER_SANITIZE_NUMBER_INT);
								//echo $int."<br>";
								$rating = str_replace(0,"",$int);
							}
							
							//rating for activities
							$temprating='';
							if($rating ==''){
								if($review->find('.zWXXYhVR', 0)){
								$temprating = $review->find('.zWXXYhVR', 0)->title;
								} else if($review->find("svg[class=RWYkj d H0]", 0)){
								$temprating = $review->find("svg[class=RWYkj d H0]", 0)->title;	
								} else if($review->find("svg[class=UctUV d H0]", 0)){
								$temprating = $review->find("svg[class=UctUV d H0]", 0)->getAttribute('aria-label');
								}
								if($temprating==''){
									if($review->find("svg[class=UctUV d H0]", 0)){
										if($review->find("svg[class=UctUV d H0]", 0)->find("title", 0)){
											$temprating = $review->find("svg[class=UctUV d H0]", 0)->find("title", 0)->plaintext;
										}
									}
								}
								$temprating = str_replace('.0 of 5 bubbles',"",$temprating);
								$temprating = str_replace('Punteggio ',"",$temprating);
								$temprating = str_replace(',0 su 5',"",$temprating);
								$temprating = str_replace(',0 sur 5',"",$temprating);
								$rating = filter_var($temprating, FILTER_SANITIZE_NUMBER_INT);
								$rating = $rating[0];
							}

							//echo "<br>rating: ".$rating;	
							//die();
							
							// find date
							if($review->find('span.ratingDate', 0)){
								$datesubmitted = $review->find('span.ratingDate', 0)->title;
							}
							if($datesubmitted ==''){
								if($review->find('span.ratingDate', 0)){
									$datesubmitted = $review->find('span.ratingDate', 0)->innertext;
									$datesubmitted = preg_replace("(<([a-z]+)>.*?</\\1>)is","",$datesubmitted);
									$datesubmitted = str_replace("Reviewed","",$datesubmitted);
									$datesubmitted = str_replace("Beoordeeld","",$datesubmitted);
									$datesubmitted = str_replace("op","",$datesubmitted);
									$datesubmitted = str_replace('Recensito il',"",$datesubmitted);
								}
							}
							$datesubmitted = str_replace(' г.',"",$datesubmitted);

							//for activities 2 _2fxQ4TOx
							if($datesubmitted ==''){
								if($review->find("div[class=_2fxQ4TOx]", 0)){
									$datesubmitted = $review->find("div[class=_2fxQ4TOx]", 0)->plaintext;
									if(1 === preg_match('~[0-9]~', $datesubmitted)){
										#has numbers
									} else {
										if ($this->yesterdaycontains($datesubmitted)) {
											
											$datesubmitted = date('M d, Y',strtotime("-1 days"));
											//echo "found:".$datesubmitted;
											//die();
										} else {
										$datesubmitted ='';
										}
									}
								}
							}
							//echo "found:".$datesubmitted;
							//for activities 2
							if($datesubmitted ==''){
								if($review->find("span[class=_34Xs-BQm]", 0)){
									$datesubmitted = $review->find("span[class=_34Xs-BQm]", 0)->plaintext;
								}
							}
							
							
							//for activities 2 DrjyGw-P _26S7gyB4 _1z-B2F-n _1dimhEoy
							if($datesubmitted ==''){
								if($review->find("div[class=DrjyGw-P _26S7gyB4 _1z-B2F-n _1dimhEoy]", 0)){
									$datesubmitted = $review->find("div[class=DrjyGw-P _26S7gyB4 _1z-B2F-n _1dimhEoy]", 0)->plaintext;
								}
							}
							//for activities 2 euPKI _R Me S4 H3
							if($datesubmitted ==''){
								if($review->find("span[class=euPKI _R Me S4 H3]", 0)){
									$datesubmitted = $review->find("span[class=euPKI _R Me S4 H3]", 0)->plaintext;
								}
							}
							//for activities
							if($datesubmitted ==''){
								if($review->find("div[class=WlYyy diXIH cspKb bQCoY]", 0)){
									$datesubmitted = $review->find("div[class=WlYyy diXIH cspKb bQCoY]", 0)->plaintext;
								}
							}
							//for activities  biGQs _P pZUbB ncFvv osNWb
							if($datesubmitted ==''){
								if($review->find("div[class=biGQs _P pZUbB ncFvv osNWb]", 0)){
									$datesubmitted = $review->find("div[class=biGQs _P pZUbB ncFvv osNWb]", 0)->plaintext;
								}
							}
							
									$datesubmitted = str_replace($user_name,"",$datesubmitted);
									$datesubmitted = str_replace(" wrote a review ","",$datesubmitted); 
									$datesubmitted = str_replace("Written ","",$datesubmitted);
									$datesubmitted = str_replace("Scritta in data ","",$datesubmitted); 
									$datesubmitted = str_replace("Date of experience:","",$datesubmitted); 
									$datesubmitted = str_replace("Date de l'expérience","",$datesubmitted);
									
								
									$datesubmitted = str_replace("eine Bewertung geschrieben.","",$datesubmitted);
									$datesubmitted = str_replace("hat im","",$datesubmitted);
									$datesubmitted = str_replace("Écrit le ","",$datesubmitted);
									$datesubmitted = str_replace("a écrit un avis","",$datesubmitted);
									$datesubmitted = str_replace("(","",$datesubmitted);
									$datesubmitted = str_replace(")","",$datesubmitted);
									$datesubmitted = str_replace(":","",$datesubmitted);
									
									$string = htmlentities($datesubmitted, null, 'utf-8');
									$content = str_replace("&nbsp;", " ", $string);
									$datesubmitted = html_entity_decode($content);
									
									$datesubmitted = trim($datesubmitted);
							
							//echo "found:".$datesubmitted;
							//die();
											
						
							
							if($ShowUserReviews==true){
								// find text
								if($review->find('div.prw_reviews_text_summary_hsx', 0)){
									$rtext = $review->find('div.prw_reviews_text_summary_hsx', 0)->find('p', 0)->plaintext;
								}
								if($rtext==''){
									if($review->find('div.prw_reviews_resp_sur_review_text', 0)){
										$rtext = $review->find('div.prw_reviews_resp_sur_review_text', 0)->find('p', 0)->plaintext;
									}
								}
								if($rtext==''){
									if($review->find('div[class=prw_rup prw_reviews_text_summary_hsx]', 0)){
										$rtext = $review->find('div[class=prw_rup prw_reviews_text_summary_hsx]', 0)->find('p', 0)->plaintext;
									}
								}
								//if this is first one treat differently
								if($i==1){
									if($review->find('div.prw_reviews_resp_sur_review_text', 0)){
										if($review->find('div.prw_reviews_resp_sur_review_text', 0)->find('span.fullText', 0)){
											$rtext = $review->find('div.prw_reviews_resp_sur_review_text', 0)->find('span.fullText', 0)->plaintext;
										} else {
											$rtext = $review->find('div.prw_reviews_resp_sur_review_text', 0)->find('p', 0)->plaintext;
										}
									} else if($review->find('div.prw_reviews_resp_sur_review_text_expanded', 0)){
										$rtext = $review->find('div.prw_reviews_resp_sur_review_text_expanded', 0)->find('p', 0)->plaintext;
									}
								}
								
							} else {
								// find text
								if($review->find('div.prw_reviews_text_summary_hsx', 0)){
									$rtext = $review->find('div.prw_reviews_text_summary_hsx', 0)->find('p', 0)->plaintext;
								}

							}
							
							//backukp text method for hotel
							if($rtext ==''){
								if($review->find('div.prw_reviews_text_summary_hsx', 0)){
								$rtext = $review->find('div.prw_reviews_text_summary_hsx', 0)->find('p', 0)->plaintext;
								}
							}
							if($rtext ==''){
								if($review->find('div.vrReviewText', 0)){
									$rtext = $review->find('div.vrReviewText', 0)->find('p', 0)->plaintext;
								}
							}
							//if rtext is blank try one more time, used to get top review on hotels
							if($rtext==''){
								if($review->find('div.entry', 0)){
									$rtext = $review->find('div.entry', 0)->find('p', 0)->plaintext;
								}
							}
							//try again for activities
							if($rtext==''){
								if($review->find("div[class=DrjyGw-P _26S7gyB4 _2nPM5Opx]", 0)){
									$rtext = $review->find("div[class=DrjyGw-P _26S7gyB4 _2nPM5Opx]", 0)->plaintext;
								}
							}
							//try again for activities
							if($rtext==''){
								if($review->find("div[class=cPQsENeY]", 0)){
									$rtext = $review->find("div[class=cPQsENeY]", 0)->plaintext;
								}
							}
							//try again for activities
							if($rtext==''){
								if($review->find("q[class=XllAv H4 _a]", 0)){
									$rtext = $review->find("q[class=XllAv H4 _a]", 0)->plaintext;
									//$rtext = mb_substr($rtext, 0, -10);
									$rtext = str_replace("…","",$rtext);
								}
							}
							//try again for activities
							if($rtext==''){
								if($review->find("div[class=WlYyy diXIH dDKKM]", 0)){
									$rtext = $review->find("div[class=WlYyy diXIH dDKKM]", 0)->plaintext;
									//$rtext = mb_substr($rtext, 0, -10);
									$rtext = str_replace("…","",$rtext);
								}
							}
							//try again for activities
							if($rtext==''){
								if($review->find("div[class=biGQs _P pZUbB KxBGd]", 0)){
									$rtext = $review->find("div[class=biGQs _P pZUbB KxBGd]", 0)->plaintext;
									//$rtext = mb_substr($rtext, 0, -10);
									$rtext = str_replace("…","",$rtext);
								}
							}
							
							$rtext = str_replace("…","",$rtext);
							$rtext = str_replace("&hellip;","",$rtext);
							
							$review_title ='';
							if($review->find("span[class=noQuotes]", 0)){
								$review_title = $review->find("span[class=noQuotes]", 0)->plaintext;
							}
							
/*							
							echo $user_name;
							echo "<br>-<br>";
							echo $userimage;
							echo "<br>-<br>";
							echo $rating;
							echo "<br>-<br>";
							echo $datesubmitted;
							echo "<br>-<br>";
							echo $rtext;
							echo "<br>--------<br>";
							die();
							*/
							
							
							//mediaurlsarrayjson, max of 8 images
							//--------------
							$mediaurlsarrayjson='';
							$mediaurlsarray = Array();
							
							if($attractionproductreviews){
								if($review->find('button[class=ajoIU _S B-]',0)){
									//have at least one image
									$imagesobject = $review->find('button[class=ajoIU _S B-]');
								} else if($review->find('button[class=UikNM _G B- _S _W _T c G_ wSSLS wnNQG]',0)){
									$imagesobject = $review->find('button[class=UikNM _G B- _S _W _T c G_ wSSLS wnNQG]');
								}
								if($imagesobject){
									//echo $imagesobject[0]->find('img', 0)->src;
									//die();
									foreach ($imagesobject as $imageobj) {
										if($imageobj->find('img', 0)){
											$tempurl= $imageobj->find('img', 0)->src;
											if($tempurl!='' && strpos($tempurl, "base64") == false){
												//get bigger image.
												$imgsizes = array("w=400", "w=300", "w=200", "w=100");
												$mediaurlsarray[]=str_replace($imgsizes,"w=500",$tempurl);
											}
											if(count($mediaurlsarray)>7){
												break;
											}
											$mediaimg='';
											$tempurl='';
										}
									}
									$mediaurlsarrayjson = json_encode($mediaurlsarray);
									unset($mediaurlsarray);
								}
							} else {
							
								if($review->find('div[class=photoContainer]',0)){
									//have at least one image
									$imagesobject = $review->find('div[class=photoContainer]');
									foreach ($imagesobject as $imageobj) {
										if($imageobj->find('img', 1)){
											$tempurl= $imageobj->find('img', 1)->src;
											if($tempurl!='' && strpos($tempurl, "base64") == false){
												//get bigger image.
												$mediaurlsarray[]=str_replace("/photo-l/","/photo-s/",$tempurl);
											}
											if(count($mediaurlsarray)>7){
												break;
											}
											$mediaimg='';
											$tempurl='';
										} else if($imageobj->find('img', 0)){
											$tempurl= $imageobj->find('img', 0)->src;
											if($tempurl!='' && strpos($tempurl, "base64") == false){
												//get bigger image.
												$mediaurlsarray[]=str_replace("/photo-l/","/photo-s/",$tempurl);
											}
											if(count($mediaurlsarray)>7){
												break;
											}
											$mediaimg='';
											$tempurl='';
										}
									}
									$mediaurlsarrayjson = json_encode($mediaurlsarray);
									unset($mediaurlsarray);
								}
							}
							
							if($rating>0 && $rtext!=''){
								$review_length = str_word_count($rtext);
								$pos = strpos($userimage, 'default_avatars');
								if ($pos === false) {
									$userimage = str_replace("60s.jpg","120s.jpg",$userimage);
								}
								
								
								//$timestamp = strtotime($datesubmitted);
								//echo $datesubmitted."<br>";
								$timestamp = $this->myStrtotime($datesubmitted);
								//echo $timestamp."<br>";
								$unixtimestamp = $timestamp;
								$timestamp = date("Y-m-d H:i:s", $timestamp);
								//echo $timestamp."<br>";
								//echo "here";
								//die();
								//check option to see if this one has been hidden
								//pull array from options table of tripadvisor hidden
								$tripadvisorhidden = get_option( 'wptripadvisor_hidden_reviews' );
								if(!$tripadvisorhidden){
									$tripadvisorhiddenarray = array('');
								} else {
									$tripadvisorhiddenarray = json_decode($tripadvisorhidden,true);
								}
								$this_tripadvisor_val = trim($user_name)."-".strtotime($datesubmitted)."-".$review_length."-TripAdvisor-".$rating;
								if (in_array($this_tripadvisor_val, $tripadvisorhiddenarray)){
									$hideme = 'yes';
								} else {
									$hideme = 'no';
								}
								
								//add check to see if already in db, skip if it is and end loop
								$reviewindb = 'no';
								$user_name = trim($user_name);
								
								//$checkrow = $wpdb->get_var( 'SELECT id FROM '.$table_name.' WHERE reviewer_name = "'.$user_name.'" AND (review_length = "'.$review_length.'" OR created_time = "'.$timestamp.'")' );
								
								$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$user_name."' AND (review_length = '".$review_length."' OR created_time = '".$timestamp."')" );
						
								if( empty( $checkrow ) ){
										$reviewindb = 'no';
								} else {
										$reviewindb = 'yes';
								}
								
								if($userimage!=''){
									$foundavatar = true;
								}
								
								if( $reviewindb == 'no' )
								{
								$reviews[] = [
										'reviewer_name' => $user_name,
										'pagename' => trim($pagename),
										'userpic' => $userimage,
										'rating' => $rating,
										'created_time' => $timestamp,
										'created_time_stamp' => $unixtimestamp,
										'review_text' => trim($rtext),
										'hide' => $hideme,
										'review_length' => $review_length,
										'type' => 'TripAdvisor',
										'review_title' => $review_title,
										'mediaurlsarrayjson' => $mediaurlsarrayjson,
								];
								}
								$review_length ='';
							}
					 
							$i++;
						}
					}

					//find total number here and end break loop early if total number less than 50. review-count
					//$totalreviews = $html->find('span.header_rating', 0)->find('span[property=v:count]', 0)->plaintext;
					//$totalreviews = intval($totalreviews);
					//if (($n*20) > $totalreviews) {
					//				break;
					//		}
					//sleep for random 2 seconds
					sleep(rand(0,2));
					$n++;
					
				
					//print_r($reviews);
					//die();
				
				//remove duplicates
				//remove duplicates
				$reviewtexts = [];
				$insertreviews = [];
				foreach ( $reviews as $stat ){
					if (!in_array($stat['review_text'], $reviewtexts)) {
						$insertreviews[] = $stat;
					}
					$reviewtexts[] = $stat['review_text'];
				}
				//echo "<br><br>";
				//print_r($insertreviews);
				//print("<pre>".print_r($insertreviews,true)."</pre>");
				
			
				//add all new tripadvisor reviews to db
				$insertnum=0;
				
				foreach ( $insertreviews as $stat ){
					//print_r($stat); 
					$insertnum = $wpdb->insert( $table_name, $stat );
					//echo "<br><br>in:";
					//echo $insertnum;
					//echo "<br><br>";
					$totalinsert = $totalinsert + $insertnum;
				}
				//die();
				//if on first page now try to find second page and loop again.
				if($ShowUserReviews && $loop < 2 && $onfirstpage){
					if($html->find("a[class=nav next ui_button primary]",0)){
						$newurl = $html->find("a[class=nav next ui_button primary]",0)->href;
						$urlvalue = $parseurl['scheme'].'://'.$parseurl['host'].$newurl;
						if (strpos($urlvalue, 'ShowUserReviews') !== false) {
							//echo $urlvalue;
							//die();
							$loop++;
							goto start;
						}
					} else if($html->find("a[class=nav next]",0)){
						$newurl = $html->find("a[class=nav next]",0)->href;
						$urlvalue = $parseurl['scheme'].'://'.$parseurl['host'].$newurl;
						if (strpos($urlvalue, 'ShowUserReviews') !== false) {
							//echo $urlvalue;
							//die();
							$loop++;
							goto start;
						}
					}						
				}
				
				//reviews added to db
				if($totalinsert>0 && $totalinsert!='0'){
					$errormsg = $errormsg . $totalinsert. ' TripAdvisor reviews downloaded. They should now be on the Review List page.';
					$this->errormsg = $errormsg;
				} else {
					print_r($insertreviews);
					$errormsg = $errormsg . ' Unable to find any new reviews. Please try again or contact support.';
					$this->errormsg = $errormsg;
				}

				
									// clean up memory
				if (!empty($html)) {
					$html->clear();
					unset($html);
				}
				if (!empty($reviewcontainerdiv)) {
					unset($reviewcontainerdiv);
				}

				// clean up memory
				if (!empty($html)) {
					$html->clear();
					unset($html);
				}
				if (!empty($html)) {
					$html->clear();
					unset($html);
				}
				
			 // }
			  
			  
			} else {
				$errormsg = $errormsg . ' Please enter a valid URL.';
				$this->errormsg = $errormsg;
			}
			/*
			if($options['tripadvisor_radio']=='no'){
				$wpdb->delete( $table_name, array( 'type' => 'TripAdvisor' ) );
				//cancel wp cron job
			}
			*/
			

		if($errormsg !=''){
			//echo $errormsg;
		}
	}
	

	
	/**
	 * check if string has yesterday in it for date
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function yesterdaycontains($str)
	{
		//echo '<br>str:'.$str;
		
		$array = array("Hier", "yesterday", "ieri", "ayer","gisteren");
		foreach($array as $a) {
			if (stripos($str,$a) !== false){
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * displays message in admin if it's been longer than 30 days.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_admin_notice__success () {

		$activatedtime = get_option('wprev_activated_time_trip');
		//if this is an old install then use 23 days ago
		if($activatedtime==''){
			$activatedtime= time() - (86400*23);
			update_option( 'wprev_activated_time_trip', $activatedtime );
		}
		$thirtydaysago = time() - (86400*14);
		
		//check if an option was clicked on
		if (isset($_GET['wprevpronotice'])) {
		  $wprevpronotice = $_GET['wprevpronotice'];
		} else {
		  //Handle the case where there is no parameter
		   $wprevpronotice = '';
		}
		if($wprevpronotice=='mlater_trip'){		//hide the notice for another 30 days
			update_option( 'wprev_notice_hide_trip', 'later' );
			$newtime = time() - (86400*21);
			update_option( 'wprev_activated_time_trip', $newtime );
			$activatedtime = $newtime;
			
		} else if($wprevpronotice=='notagain_trip'){		//hide the notice forever
			update_option( 'wprev_notice_hide_trip', 'never' );
		}
		
		$wprev_notice_hide = get_option('wprev_notice_hide_trip');

		if($activatedtime<$thirtydaysago && $wprev_notice_hide!='never'){
		
			$urltrimmedtab = remove_query_arg( array('taction', 'tid', 'sortby', 'sortdir', 'opt') );
			$urlmayberlater = esc_url( add_query_arg( 'wprevpronotice', 'mlater_trip',$urltrimmedtab ) );
			$urlnotagain = esc_url( add_query_arg( 'wprevpronotice', 'notagain_trip',$urltrimmedtab ) );
			
			$temphtml = '<p>Hey, I noticed you\'ve been using my <b>WP TripAdvisor Review Slider</b> plugin for a while now – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? <br>
			Thanks!<br>
			~ Josh W.<br></p>
			<ul>
			<li><a href="https://wordpress.org/support/plugin/wp-tripadvisor-review-slider/reviews/#new-post" target="_blank">Ok, you deserve it</a></li>
			<li><a href="'.$urlmayberlater.'">Not right now, maybe later</a></li>
			<li><a href="'.$urlnotagain.'">Don\'t remind me again</a></li>
			</ul>
			<p>P.S. If you\'ve been thinking about upgrading to the <a href="https://wpreviewslider.com/" target="_blank">Pro</a> version, here\'s a 10% off coupon code you can use! ->  <b>wprevpro10off</b></p>';
			
			?>
			<div class="notice notice-info">
				<div class="wprevpro_admin_notice" style="color: #007500;">
				<?php _e( $temphtml, $this->_token ); ?>
				</div>
			</div>
			<?php
		}

	}
	
			/**
	 * add dashboard widget to wordpress admin
	 * @access  public
	 * @since   7.1
	 * @return  void
	 */
	public function wprevtrip_dashboard_widget() {
		global $wp_meta_boxes;
		//wp_add_dashboard_widget('custom_help_widget', 'Theme Support', 'custom_dashboard_help');
		add_meta_box( 'id', 'WP TripAdvisor Review Slider Recent Reviews', array($this,'custom_dashboard_help'), 'dashboard', 'side', 'high' );
	}
	 
	public function custom_dashboard_help() {
		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$tempquery = "select * from ".$reviews_table_name." ORDER by created_time_stamp Desc limit 4";
		$reviewrows = $wpdb->get_results($tempquery);
		$now = time(); // or your date as well
		
		echo '<style>
			img.wprev_dash_avatar {float: left;margin-right: 8px;border-radius: 20px;}
			.wprev_dash_stars {float: right;}
			p.wprev_dash_text {margin-top: -6px;}
			span.wprev_dash_timeago {font-size: 12px;font-style: italic;}
			.wprev_dash_revdiv {min-height: 50px;}
			</style>';
		echo '<ul>';
		foreach ( $reviewrows as $review ) 
		{
			$timesince = '';
			if(strlen($review->review_text)>130){
				$reviewtext = substr($review->review_text,0,130).'...';
			} else {
				$reviewtext = $review->review_text;
			}
			
			$your_date = $review->created_time_stamp;
			$datediff = $now - $your_date;
			$daysago = round($datediff / (60 * 60 * 24));
			if($daysago==1){
				$daysagohtml = $daysago.' day ago';
			} else {
				$daysagohtml = $daysago.' days ago';
			}
			if($review->rating<1){
				if($review->recommendation_type=='positive'){
					$review->rating=5;
				} else {
					$review->rating=2;
				}
			}

			$rating   = absint( $review->rating );
			$imgs_url = plugin_dir_url(__DIR__).'/public/partials/imgs/';
			$starfile = 'tripadvisor_stars_'.$rating.'.png';
			$starhtml='<img src="'.esc_url( $imgs_url.$starfile ).'" alt="'.esc_attr( $rating.' star rating' ).'" class="wprev_dash_stars">';
			
			$avatarhtml = '';
			if(isset($review->userpic) && $review->userpic!=''){
				$avatarhtml = '<img alt="" src="'.esc_url( $review->userpic ).'" class="wprev_dash_avatar" height="40" width="40">';
			}
			
			// Untrusted review data (reviewer name/text) must always be escaped on
			// output here, even though it is sanitized on the way in.
			echo '<li><div class="wprev_dash_revdiv">'.$avatarhtml.'<div class="wprev_dash_stars">'.$starhtml.'</div><h4 class="wprev_dash_name">'.esc_html( $review->reviewer_name ).' - <span class="wprev_dash_timeago">'.esc_html( $daysagohtml ).'</span></h4><p class="wprev_dash_text">'.esc_html( $reviewtext ).'</p></div></li>';
			
		}
		echo '</ul>';
		
		echo '<div><a href="admin.php?page=wp_tripadvisor-reviews">All Reviews</a> - <a href="https://wpreviewslider.com/" target="_blank">Go Pro For More Cool Features!</a></div>';
	}

	/**
	 * Ajax: return rendered template HTML for preview.
	 */
	public function wptripadvisor_previewtemplate_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
			return;
		}

		$tid = isset( $_POST['tid'] ) ? absint( $_POST['tid'] ) : 0;
		check_ajax_referer( 'randomnoncestring', 'wptripadvisor_nonce' );

		$returnarray = $this->wptripadvisor_previewtemplate_ajax_get( $tid );
		echo wp_json_encode( $returnarray );
		die();
	}

	/**
	 * Build preview HTML for a template id.
	 *
	 * @param int $tid Template id.
	 * @return array
	 */
	public function wptripadvisor_previewtemplate_ajax_get( $tid ) {
		$atts = array( 'tid' => $tid );
		require_once plugin_dir_path( __DIR__ ) . 'public/class-wp-tripadvisor-review-slider-public.php';
		$plugin_public_class = new WP_TripAdvisor_Review_Public( $this->get_token(), $this->get_version() );
		$templatehtml        = $plugin_public_class->wptripadvisor_usetemplate_func( $atts, null );

		return array(
			'tid'          => $tid,
			'ack'          => 'success',
			'templatehtml' => $templatehtml,
		);
	}

	/**
	 * Ajax: save review template and return preview HTML.
	 */
	public function wptripadvisor_savetemplate_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
			return;
		}

		check_ajax_referer( 'randomnoncestring', 'wptripadvisor_nonce' );

		$formdata  = isset( $_POST['data'] ) ? stripslashes( $_POST['data'] ) : '';
		$formarray = json_decode( $formdata, true );
		if ( ! is_array( $formarray ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmessage' => 'Invalid form data.' ) );
			die();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_post_templates';

		$t_id               = isset( $formarray['edittid'] ) ? sanitize_text_field( $formarray['edittid'] ) : '';
		$title              = isset( $formarray['wptripadvisor_template_title'] ) ? sanitize_text_field( $formarray['wptripadvisor_template_title'] ) : '';
		$template_type      = isset( $formarray['wptripadvisor_template_type'] ) ? sanitize_text_field( $formarray['wptripadvisor_template_type'] ) : 'post';
		$style              = isset( $formarray['wprevpro_template_style'] ) ? sanitize_text_field( $formarray['wprevpro_template_style'] ) : '1';
		if ( $style !== '1' && $style !== '6' ) {
			$style = '1';
			if ( ! empty( $t_id ) ) {
				$existing_style = $wpdb->get_var( $wpdb->prepare( "SELECT style FROM {$table_name} WHERE id = %d", absint( $t_id ) ) );
				if ( $existing_style === '1' || $existing_style === '6' ) {
					$style = $existing_style;
				}
			}
		}
		$display_num        = isset( $formarray['wptripadvisor_t_display_num'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_display_num'] ) : '3';
		$display_num_rows   = isset( $formarray['wptripadvisor_t_display_num_rows'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_display_num_rows'] ) : '1';
		$display_order      = isset( $formarray['wptripadvisor_t_display_order'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_display_order'] ) : 'newest';
		$hide_no_text       = isset( $formarray['wptripadvisor_t_hidenotext'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_hidenotext'] ) : 'no';
		$template_css       = isset( $formarray['wptripadvisor_template_css'] ) ? sanitize_textarea_field( $formarray['wptripadvisor_template_css'] ) : '';
		$createslider       = isset( $formarray['wptripadvisor_t_createslider'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_createslider'] ) : 'yes';
		$numslides          = isset( $formarray['wptripadvisor_t_numslides'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_numslides'] ) : '3';
		$read_more          = isset( $formarray['wprevpro_t_read_more'] ) ? sanitize_text_field( $formarray['wprevpro_t_read_more'] ) : 'no';
		$read_more_text     = isset( $formarray['wprevpro_t_read_more_text'] ) ? sanitize_text_field( $formarray['wprevpro_t_read_more_text'] ) : 'read more';
		$review_same_height = isset( $formarray['wprevpro_t_review_same_height'] ) ? sanitize_text_field( $formarray['wprevpro_t_review_same_height'] ) : 'no';
		$min_rating         = isset( $formarray['wptripadvisor_t_min_rating'] ) ? sanitize_text_field( $formarray['wptripadvisor_t_min_rating'] ) : '1';
		$slidermobileview   = isset( $formarray['wprevpro_slidermobileview'] ) ? sanitize_text_field( $formarray['wprevpro_slidermobileview'] ) : '';

		$templatemiscarray = array();
		$misc_map          = array(
			'showstars'      => 'wprevpro_template_misc_showstars',
			'showdate'       => 'wprevpro_template_misc_showdate',
			'avataropt'      => 'wprevpro_template_misc_avataropt',
			'showicon'       => 'wprevpro_template_misc_showicon',
			'bgcolor1'       => 'wprevpro_template_misc_bgcolor1',
			'bgcolor2'       => 'wprevpro_template_misc_bgcolor2',
			'tcolor1'        => 'wprevpro_template_misc_tcolor1',
			'tcolor2'        => 'wprevpro_template_misc_tcolor2',
			'tcolor3'        => 'wprevpro_template_misc_tcolor3',
			'bradius'        => 'wprevpro_template_misc_bradius',
			'showmedia'      => 'wprevpro_t_showmedia',
			'verified'       => 'wprevpro_template_misc_verified',
			'lastnameformat' => 'wprevpro_template_misc_lastname',
			'blocation'      => 'wprevpro_t_blocation',
			'filtersource'   => 'wprevpro_t_filtersource',
			'slideautodelay' => 'wptripadvisor_t_slideautodelay',
			'slidespeed'     => 'wptripadvisor_t_slidespeed',
			'bshape'         => 'wprevpro_t_bshape',
			'bimgsize'       => 'wprevpro_t_bimgsize',
			'bbradius'       => 'wprevpro_t_bbradius',
			'bbwidth'        => 'wprevpro_t_bbwidth',
			'bbtnurl'        => 'wprevpro_t_bbtnurl',
			'bimgurl'        => 'wprevpro_t_bimgurl',
			'bname'          => 'wprevpro_t_bname',
			'bnameurl'       => 'wprevpro_t_bnameurl',
			'read_more_num'  => 'wprevpro_t_read_more_num',
		);
		foreach ( $misc_map as $key => $postkey ) {
			if ( isset( $formarray[ $postkey ] ) ) {
				$templatemiscarray[ $key ] = sanitize_text_field( $formarray[ $postkey ] );
			}
		}
		// filtersource is later used to build a "pageid = ..." SQL clause, so
		// restrict it to the same charset TripAdvisor pageids can ever contain
		// (see wptripadvisor_extract_pageid_from_url()); reject everything else.
		// Free version: single source only (never accept comma/array multi-select).
		if ( isset( $templatemiscarray['filtersource'] ) ) {
			$filtersource_raw = $templatemiscarray['filtersource'];
			if ( is_array( $filtersource_raw ) ) {
				$filtersource_raw = reset( $filtersource_raw );
			}
			$filtersource_raw = sanitize_text_field( (string) $filtersource_raw );
			// Take first value if a comma-separated list was posted.
			if ( strpos( $filtersource_raw, ',' ) !== false ) {
				$parts            = explode( ',', $filtersource_raw );
				$filtersource_raw = trim( $parts[0] );
			}
			$templatemiscarray['filtersource'] = preg_replace( '/[^A-Za-z0-9_-]/', '', $filtersource_raw );
		}

		$filtersource_single = isset( $templatemiscarray['filtersource'] ) ? $templatemiscarray['filtersource'] : '';

		$color_keys = array( 'bbcolor' => 'wprevpro_t_bbcolor', 'bbkcolor' => 'wprevpro_t_bbkcolor', 'bbtncolor' => 'wprevpro_t_bbtncolor', 'read_more_color' => 'wprevpro_t_read_more_color' );
		foreach ( $color_keys as $key => $postkey ) {
			if ( isset( $formarray[ $postkey ] ) ) {
				$templatemiscarray[ $key ] = WP_TripAdvisor_Review_Sanitize::sanitize_css_color( $formarray[ $postkey ] );
			}
		}

		$tfont1_val = isset( $formarray['wprevpro_template_misc_tfont1'] ) ? absint( $formarray['wprevpro_template_misc_tfont1'] ) : 0;
		$tfont2_val = isset( $formarray['wprevpro_template_misc_tfont2'] ) ? absint( $formarray['wprevpro_template_misc_tfont2'] ) : 0;
		$templatemiscarray['tfont1'] = $tfont1_val > 0 ? (string) $tfont1_val : '';
		$templatemiscarray['tfont2'] = $tfont2_val > 0 ? (string) $tfont2_val : '';

		$checkbox_keys = array( 'bhreviews', 'bhbtn', 'bhbased', 'bhphoto', 'bhname', 'bcenter', 'bdropsh', 'bhpow', 'bobasedon', 'borevus', 'sliderautoplay', 'sliderhideprevnext', 'sliderhidedots', 'sliderfixedheight' );
		$checkbox_map  = array(
			'bhreviews'           => 'wprevpro_t_bhreviews',
			'bhbtn'               => 'wprevpro_t_bhbtn',
			'bhbased'             => 'wprevpro_t_bhbased',
			'bhphoto'             => 'wprevpro_t_bhphoto',
			'bhname'              => 'wprevpro_t_bhname',
			'bcenter'             => 'wprevpro_t_bcenter',
			'bdropsh'             => 'wprevpro_t_bdropsh',
			'bhpow'               => 'wprevpro_t_bhpow',
			'bobasedon'           => 'wprevpro_t_bobasedon',
			'borevus'             => 'wprevpro_t_borevus',
			'sliderautoplay'      => 'wprevpro_sliderautoplay',
			'sliderhideprevnext'  => 'wprevpro_sliderhideprevnext',
			'sliderhidedots'      => 'wprevpro_sliderhidedots',
			'sliderfixedheight'   => 'wprevpro_sliderfixedheight',
		);
		foreach ( $checkbox_map as $key => $postkey ) {
			if ( isset( $formarray[ $postkey ] ) ) {
				$templatemiscarray[ $key ] = sanitize_text_field( $formarray[ $postkey ] );
			}
		}

		$templatemiscjson = wp_json_encode( $templatemiscarray );
		$timenow          = time();

		$data = array(
			'title'               => $title,
			'template_type'       => $template_type,
			'style'               => $style,
			'created_time_stamp'  => $timenow,
			'display_num'         => $display_num,
			'display_num_rows'    => $display_num_rows,
			'display_order'       => $display_order,
			'hide_no_text'        => $hide_no_text,
			'template_css'        => $template_css,
			'min_rating'          => $min_rating,
			'min_words'           => '',
			'max_words'           => '',
			'rtype'               => '["tripadvisor"]',
			'rpage'               => $filtersource_single,
			'createslider'        => $createslider,
			'numslides'           => $numslides,
			'sliderautoplay'      => '',
			'sliderdirection'     => '',
			'sliderarrows'        => '',
			'sliderdots'          => '',
			'sliderdelay'         => '',
			'sliderheight'        => '',
			'showreviewsbyid'     => '',
			'template_misc'       => $templatemiscjson,
			'read_more'           => $read_more,
			'read_more_text'      => $read_more_text,
			'slidermobileview'    => $slidermobileview,
			'review_same_height'  => $review_same_height,
		);

		$format = array(
			'%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d',
			'%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s',
			'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
		);

		$returnarray = array(
			'iu'         => '',
			'ack'        => '',
			'ackmessage' => '',
			't_id'       => '',
		);

		if ( $t_id === '' ) {
			$returnarray['iu'] = 'insert';
			$inserttemplate    = $wpdb->insert( $table_name, $data, $format );
			$t_id              = $wpdb->insert_id;
			if ( ! $inserttemplate ) {
				$returnarray['ack']        = 'error';
				$returnarray['ackmessage'] = __( 'Unable to update. Try refreshing the page.', 'wp-tripadvisor-review-slider' );
			} else {
				$returnarray['ack']        = 'success';
				$returnarray['ackmessage'] = __( 'Template Saved!', 'wp-tripadvisor-review-slider' );
			}
		} else {
			$returnarray['iu'] = 'update';
			$updatetempquery   = $wpdb->update( $table_name, $data, array( 'id' => absint( $t_id ) ), $format, array( '%d' ) );
			if ( false === $updatetempquery ) {
				$returnarray['ack']        = 'error';
				$returnarray['ackmessage'] = __( 'Unable to update. Try refreshing the page.', 'wp-tripadvisor-review-slider' );
			} else {
				$returnarray['ack']        = 'success';
				$returnarray['ackmessage'] = __( 'Template Updated!', 'wp-tripadvisor-review-slider' );
			}
		}

		$returnarray['t_id'] = $t_id;
		$returnpreview       = $this->wptripadvisor_previewtemplate_ajax_get( $t_id );
		$returnarray['templatehtml'] = $returnpreview['templatehtml'];

		echo wp_json_encode( $returnarray );
		die();
	}

	public function get_token() {
		return $this->_token;
	}

	public function get_version() {
		return $this->version;
	}

}
