<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/admin
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Tabs styles & scripts.
		add_action( 'init', array( $this, 'mi_create_user_extension_instance' ) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// Hide default 'Add New' menu item for CPT.
		wp_enqueue_style(
			$this->plugin_name . 'hide-add-new',
			MI_ADMIN_URL . 'css/mi-assessments-admin-menu.css',
			array(),
			generate_random_string(),
			'all'
		);

		if ( $this->is_mi_admin_pages() ) {

			wp_enqueue_style(
				$this->plugin_name,
				MI_ADMIN_URL . 'css/mi-assessments-admin.css',
				array(),
				generate_random_string(),
				'all'
			);

			wp_enqueue_style(
				$this->plugin_name . '-sweetalert',
				'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css',
				array(),
				generate_random_string(),
				'all'
			);

		}

		// if error log page.
		if ( $this->is_mi_error_log_page() ) {

			wp_enqueue_style(
				$this->plugin_name . '-error-log',
				MI_ADMIN_URL . 'css/mi-error-log.css',
				array(),
				generate_random_string(),
				'all'
			);

		}

		// enqueue styles for user extension.
		wp_enqueue_style(
			$this->plugin_name . 'user-extension',
			MI_ADMIN_URL . 'css/mi-assessment-user-extension.css',
			array(),
			generate_random_string(),
			'all'
		);

		// enqueue styles for user mapping.
		wp_enqueue_style(
			$this->plugin_name . 'user-mapping',
			MI_ADMIN_URL . 'css/mi-assessment-user-mapping.css',
			array(),
			generate_random_string(),
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if ( $this->is_mi_admin_pages() ) {

			// Pass post tpe to JS value to update the 'Add New' button URL.
			$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : ''; // phpcs:ignore

			wp_enqueue_script(
				$this->plugin_name,
				MI_ADMIN_URL . 'js/mi-assessments-admin.js',
				array( 'jquery' ),
				generate_random_string(),
				false
			);

			wp_enqueue_script(
				$this->plugin_name . '-sweetalert',
				'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js',
				array(),
				generate_random_string(),
				true
			);

			wp_localize_script(
				$this->plugin_name,
				'mi_assessments_admin_ajax_obj',
				array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'siteurl'        => site_url(),
					'mi_admin_url'   => MI_ADMIN_URL,
					'post_type'      => $post_type,
					'add_new_href'   => esc_url_raw( admin_url( 'edit.php?post_type=tti_assessments&page=ttiplatform_api', 'https' ), 'https' ),
					'mi_admin_nonce' => wp_create_nonce( 'mi_assessment_admin_nonce' ),
				)
			);

		}

		// if error log page.
		if ( $this->is_mi_error_log_page() ) {

			wp_enqueue_script(
				$this->plugin_name . '-error-log',
				MI_ADMIN_URL . 'js/mi-error-log.js',
				array( 'jquery' ),
				generate_random_string(),
				true
			);

		}

		// enqueue script for user extension.
		wp_enqueue_script(
			$this->plugin_name . '-user-extension',
			MI_ADMIN_URL . 'js/mi-assessment-user-extension.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		// enqueue script for user mapping.
		wp_enqueue_script(
			$this->plugin_name . '-user-mapping',
			MI_ADMIN_URL . 'js/mi-assessment-user-mapping.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		// localize user extension script.
		wp_localize_script(
			$this->plugin_name . '-user-extension',
			'tti_platform_admin_user_obj',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'siteurl'       => site_url(),
				'mi_user_nonce' => wp_create_nonce( 'mi_user_level_assessment_nonce' ),
			)
		);

		// enqueue script for group edit.
		wp_enqueue_script(
			$this->plugin_name . '-group-edit',
			MI_ADMIN_URL . 'js/mi-assessments-group-edit.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

	}

	/**
	 * Enqueue scripts/styles for popup shortcode.
	 *
	 * @since 1.2.0
	 */
	public function tti_platform_admin_scripts_tabs() {

		// phpcs:ignore
		$actual_link = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';
		$arr         = explode( '/', $actual_link );

		/* Only load if specific page */
		if ( isset( $arr[ count( $arr ) - 1 ] ) &&
			'mi-shortcode-popup.php' === $arr[ count( $arr ) - 1 ] ) {

			/**
			 * Enqueue styles for popup shortcode.
			 *
			 * @since 1.2.0
			 */
			wp_enqueue_style(
				$this->plugin_name . '-tabs',
				MI_ADMIN_URL . 'css/mi-assessments-admin-tabs.css',
				array(),
				generate_random_string(),
				'all'
			);

			/**
			 * Enqueue styles for popup shortcode.
			 *
			 * @since 1.2.0
			 */
			wp_enqueue_script(
				$this->plugin_name . '-tabs',
				MI_ADMIN_URL . 'js/mi-assessments-admin-tabs.js',
				array( 'jquery', 'wp-tinymce' ),
				generate_random_string(),
				true
			);

			wp_localize_script(
				$this->plugin_name . '-tabs',
				'mi_assessments_admin_tabs_ajax_obj',
				array(
					'tabs_ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'mi_tabs_nonce' => wp_create_nonce( 'mi_assessment_popup_tabs' ),
				)
			);

		}

	}

	/**
	 * Check if admin page opening related to our plugin.
	 *
	 * @since   1.4.2
	 * @return boolean return true if current page is related to this plugin
	 */
	private function is_mi_admin_pages() {
		global $post, $pagenow;

		// phpcs:ignore
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;

		/* if assessment post type page */
		if ( isset( $_SERVER['REQUEST_URI'] )
		&& ( strpos( $_SERVER['REQUEST_URI'], 'tti_assessments' ) !== false // phpcs:ignore
		|| strpos( $_SERVER['REQUEST_URI'], 'ttiplatform_settings' ) !== false // phpcs:ignore
		|| strpos( $_SERVER['REQUEST_URI'], 'tti-profile-assessment-page' ) !== false ) ) { // phpcs:ignore

			return true;

		} elseif ( ( 0 !== $post_id && $post_id > 0 ) || ( 'post-new.php' === $pagenow ) ) {
			$p_type = get_post_type( $post_id );

			if ( 'tti_assessments' === $p_type
			|| in_array( $pagenow, array( 'post-new.php', 'post.php', 'edit.php' ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if it is our plugin error log page.
	 *
	 * @since   1.4.2
	 * @return boolean return true if current page is related to this plugin
	 */
	private function is_mi_error_log_page() {
		global $post, $pagenow;

		// if error log page.
		if ( strpos( $_SERVER['REQUEST_URI'], 'mi-error-logs' ) !== false ) { // phpcs:ignore
			return true;
		}

		return false;
	}

	/**
	 * Create the instance of the user extension class.
	 *
	 * @since   2.0.0
	 */
	public function mi_create_user_extension_instance() {
		// Initialize the user level assessment class on init hook.
		$this->user_extension = new MI_User_Extension();
	}

}


