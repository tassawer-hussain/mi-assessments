<?php
/**
 * Fired during plugin activation
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Admin_Menu {

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       2.0.0
	 */
	public function __construct() {

		$this->mi_api = new Mi_Assessments_API();

		add_action( 'admin_menu', array( $this, 'mi_create_add_menu' ) );

		/*
		 * Ajax Hook Initialization to Get Generate Secret Key
		 */
		add_action( 'wp_ajax_generate_secret_key', array( $this, 'generate_secret_key_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_generate_secret_key', array( $this, 'generate_secret_key_ajax_callback' ) );

		/*
		 * Ajax Hook Initialization to Save Secret Key
		 */
		add_action( 'wp_ajax_save_secret_key', array( $this, 'save_secret_key_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_save_secret_key', array( $this, 'save_secret_key_ajax_callback' ) );
	}

	/**
	 * Add custom admin submenu pages using WordPress hooks.
	 *
	 * @since   1.0.0
	 */
	public function mi_create_add_menu() {

		// Add new assessmnet page.
		add_submenu_page(
			'edit.php?post_type=tti_assessments',
			__( 'Add New', 'tti-platform' ),
			__( 'Add New', 'tti-platform' ),
			'manage_options',
			'ttiplatform_api',
			array( $this, 'mi_add_new_assessment_callback' )
		);

		// Add settings page.
		add_submenu_page(
			'edit.php?post_type=tti_assessments',
			__( 'Settings', 'tti-platform' ),
			__( 'Settings', 'tti-platform' ),
			'manage_options',
			'ttiplatform_settings',
			array( $this, 'mi_settings_page_callback' ),
			15
		);

		// Add mapping page.
		add_submenu_page(
			'edit.php?post_type=tti_assessments',
			__( 'Mappings', 'tti-platform' ),
			__( 'Mapping', 'tti-platform' ),
			'manage_options',
			'ttiplatform_mappings',
			array( $this, 'mi_mappings_page_callback' ),
			20
		);

		// Add error log page.
		add_submenu_page(
			'edit.php?post_type=tti_assessments',
			__( 'MI Error Log', 'tti-platform' ),
			__( 'Error Log', 'tti-platform' ),
			'manage_options',
			'mi-error-logs',
			array( $this, 'mi_error_logs_callback' ),
			25
		);

		// Add error log page.
		add_submenu_page(
			'edit.php?post_type=tti_assessments',
			__( 'Documentation', 'tti-platform' ),
			__( 'Documentation', 'tti-platform' ),
			'manage_options',
			'mi-documentation',
			array( $this, 'mi_documentation_callback' ),
			30
		);

	}

	/**
	 * Admin setting page callback function.
	 *
	 * @since   1.0.0
	 */
	public function mi_add_new_assessment_callback() {

		$mi_secret_key = get_option( 'ttiplatform_secret_key' );
		$mi_listener   = get_option( 'ttiplatform_secret_key_listener' );

		if ( isset( $mi_secret_key )
			&& ! empty( $mi_secret_key )
			&& isset( $mi_listener )
			&& ! empty( $mi_listener ) ) {
				require_once MI_ADMIN_PATH . 'partials/mi-add-assessment-options.php';
		} else {
			require_once MI_ADMIN_PATH . 'partials/mi-add-assessment-options-key-error.php';
		}
	}

	/**
	 * Function to create HTML of admin settings page.
	 *
	 * @since   1.0.0
	 */
	public function mi_settings_page_callback() {
		require_once MI_ADMIN_PATH . 'partials/mi-settings-page.php';
	}

	/**
	 * Function to process mapping page.
	 *
	 * @since   1.7.0
	 */
	public function mi_mappings_page_callback() {

		require_once MI_INCLUDES_PATH . 'classes/class-mi-mapping-handler.php';

		$ajax_obj = new Mi_Mapping_Handler();
		$map_data = $ajax_obj->return_mapping_data();

		require_once MI_ADMIN_PATH . 'partials/user/mi-assessment-mapping-rules.php';
	}

	/**
	 * Function to create HTML of error log page.
	 *
	 * @since   1.0.0
	 */
	public function mi_error_logs_callback() {
		require_once MI_ADMIN_PATH . 'partials/mi-errror-log.php';
	}

	/**
	 * Function to create HTML of documentation page.
	 *
	 * @since   1.0.0
	 */
	public function mi_documentation_callback() {
		require_once MI_ADMIN_PATH . 'partials/mi-documentation.php';
	}

	/**
	 * Function to generate secret key.
	 *
	 * @since   1.0.0
	 */
	public function generate_secret_key_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_admin_nonce', 'nonce' );

		$randstr = generate_random_string();

		echo wp_json_encode( $randstr );

		// Log the event details.
		$mi_error_log = array(
			'action'  => 'Generating new secret key',
			'status'  => 'Secret Key generated successfully',
			'message' => 'New secret is ' . $randstr,
		);
		Mi_Error_Log::put_error_log( $mi_error_log, 'array', 'primary' );

		wp_die();
	}

	/**
	 * Function to save secret key.
	 *
	 * @since   1.0.0
	 */
	public function save_secret_key_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_admin_nonce', 'nonce' );

		$secret_key = isset( $_POST['secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['secret_key'] ) ) : '';

		// Early bail if key is empty.
		if ( empty( $secret_key ) ) {

			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => '<span class="error">' . esc_html( __( 'Error! Please generate secret key first.', 'tti-platform' ) ) . '</span>',
				)
			);

			wp_die();
		}

		$get_secret_key = get_option( 'ttiplatform_secret_key' );

		// No change in secret key. Previou key is received again.
		if ( isset( $get_secret_key ) && ! empty( $get_secret_key ) && $secret_key === $get_secret_key ) {

			$message = '<span class="warning">' . __( 'Secret key already saved in the system.', 'tti-platform' ) . '</span>';

		} else {

			$saved_secret_key          = update_option( 'ttiplatform_secret_key', $secret_key );
			$saved_secret_key_listener = update_option( 'ttiplatform_secret_key_listener', site_url() . '/listener/?link=$LINK&password=$PASSWORD&key=' . $secret_key );

			if ( $saved_secret_key ) {
				$message = '<span class="success">' . __( 'Secret key has been saved successfully.', 'tti-platform' ) . '</span>';
			}
		}

		// Log the event details.
		$mi_error_log = array(
			'action'  => 'Saving the new secret key',
			'status'  => 'Secret Key saved successfully',
			'message' => 'New secret is ' . $secret_key,
		);
		Mi_Error_Log::put_error_log( $mi_error_log, 'array', 'primary' );

		// check users assessments.
		$this->mi_api->mi_update_return_url_of_user_level_assessments();

		// update the listener URL of all assessments.
		$this->mi_api->mi_update_return_url_of_all_assessments();

		echo wp_json_encode(
			array(
				'message' => $message,
				'status'  => 'success',
			)
		);

		wp_die();
	}
}

new Mi_Admin_Menu();
