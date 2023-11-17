<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
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
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Mi_Assessments_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

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
		if ( defined( 'MI_ASSESSMENTS_VERSION' ) ) {
			$this->version = MI_ASSESSMENTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'mi-assessments';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mi_Assessments_Loader. Orchestrates the hooks of the plugin.
	 * - Mi_Assessments_I18n. Defines internationalization functionality.
	 * - Mi_Assessments_Admin. Defines all hooks for the admin area.
	 * - Mi_Assessments_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// Include debug functions.
		require_once MI_INCLUDES_PATH . 'functions/debug-functions.php';

		// Include error log class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-error-log.php';

		// Include API Class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-assessments-api.php';

		// Include helper functions.
		require_once MI_INCLUDES_PATH . 'functions/helper-functions.php';

		// Include default overrided WordPress action/filter hooks.
		require_once MI_INCLUDES_PATH . 'functions/wordpress-action-filter-hooks.php';

		// Include default overrided WooCommerce action/filter hooks.
		require_once MI_INCLUDES_PATH . 'functions/woocommerce-action-filter-hooks.php';

		// load admin functionlaity related files.
		if ( is_admin() ) {
			require_once MI_INCLUDES_PATH . 'classes/class-mi-assessments-cpt.php';
			require_once MI_INCLUDES_PATH . 'classes/class-mi-admin-menu.php';
			require_once MI_INCLUDES_PATH . 'classes/class-mi-tinymce-shortcode.php';
		}

		require_once MI_INCLUDES_PATH . 'classes/class-mi-user-extension.php';

		// Include error log class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-cron-jobs.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mi-assessments-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mi-assessments-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mi-assessments-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mi-assessments-public.php';

		$this->loader = new Mi_Assessments_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mi_Assessments_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Mi_Assessments_I18n();

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

		$plugin_admin = new Mi_Assessments_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Tabs styles & scripts.
		$this->loader->add_action( 'init', $plugin_admin, 'tti_platform_admin_scripts_tabs' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Mi_Assessments_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Run public init hooks.
		$this->loader->add_action( 'init', $plugin_public, 'init_hook_tasks' );

		// grant retake assessment to user.
		$this->loader->add_action( 'wp_ajax_tti_retaking_assessment', $plugin_public, 'mi_grant_user_retake_assessments' );
		$this->loader->add_action( 'wp_ajax_nopriv_tti_retaking_assessment', $plugin_public, 'mi_grant_user_retake_assessments' );

		// Payment complete. - Check self retake assessment process.
		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_public, 'retake_assessment_on_self_purchase' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
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
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Mi_Assessments_Loader    Orchestrates the hooks of the plugin.
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
