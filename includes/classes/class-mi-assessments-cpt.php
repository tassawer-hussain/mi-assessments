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
class Mi_Assessments_CPT {

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       2.0.0
	 */
	public function __construct() {

		// Create an object of the assessment api class.
		$this->mi_api = new Mi_Assessments_API();

		// register post type.
		add_action( 'init', array( $this, 'assessment_post_type' ) );

		// manage custom columns.
		add_filter( 'manage_tti_assessments_posts_columns', array( $this, 'assessment_add_custom_column' ) );

		// Display custom colum data.
		add_action( 'manage_tti_assessments_posts_custom_column', array( $this, 'assessment_add_custom_column_data' ), 10, 2 );

		// Make custom column sortable.
		add_filter( 'manage_edit-tti_assessments_sortable_columns', array( $this, 'assessment_add_custom_column_make_sortable' ) );

		// Register Custom Meta Box.
		add_action( 'add_meta_boxes', array( $this, 'assessment_meta_box' ) );

		// Save/Update Meta box information.
		add_action( 'save_post', array( $this, 'assessment_meta_box_information' ) );

		// validate assessment details before adding to assessment post type.
		add_action( 'wp_ajax_validate_assessment_details_before_adding', array( $this, 'mi_validate_assessment_details_before_adding_callback' ) );
		add_action( 'wp_ajax_nopriv_validate_assessment_details_before_adding', array( $this, 'mi_validate_assessment_details_before_adding_callback' ) );

		// Insert assessment into assessment post type.
		add_action( 'wp_ajax_insert_assessments', array( $this, 'insert_assessments' ) );
		add_action( 'wp_ajax_nopriv_insert_assessments', array( $this, 'insert_assessments' ) );

		/* Ajax to restore the trashed post */
		add_action( 'wp_ajax_restore_trashed_post', array( $this, 'restore_trashed_post' ) );
		add_action( 'wp_ajax_nopriv_restore_trashed_post', array( $this, 'restore_trashed_post' ) );

		// Add active/suspend option in bulk action dropdown. bulk_actions-edit-{CPT name}.
		add_filter( 'bulk_actions-edit-tti_assessments', array( $this, 'mi_assessment_register_bulk_actions' ) );

		// Function to process the bulk action. handle_bulk_actions-edit-{CPT name}.
		add_filter( 'handle_bulk_actions-edit-tti_assessments', array( $this, 'mi_assessment_process_bulk_actions' ), 10, 3 );

		// Display notices on successful bulk action completion.
		add_action( 'admin_notices', array( $this, 'mi_assessment_bulk_action_process_notices' ) );

		// Remove default password field from the assessment 'Quick Edit' screen.
		add_action( 'admin_head-edit.php', array( $this, 'remove_password_from_quick_edit' ) );

	}

	/**
	 * Creates a new custom post type (tti_assessments).
	 *
	 * @since   1.0.0
	 */
	public function assessment_post_type() {
		$labels = array(
			'name'               => _x( 'Assessments', 'Post Type General Name', 'mi-assessments' ),
			'singular_name'      => _x( 'Assessment', 'Post Type Singular Name', 'mi-assessments' ),
			'menu_name'          => __( 'Assessments', 'mi-assessments' ),
			'parent_item_colon'  => __( 'Parent Assessment', 'mi-assessments' ),
			'all_items'          => __( 'All Assessments', 'mi-assessments' ),
			'view_item'          => __( 'View Assessment', 'mi-assessments' ),
			'add_new_item'       => __( 'Add New Assessment', 'mi-assessments' ),
			'add_new'            => __( 'Add New', 'mi-assessments' ),
			'edit_item'          => __( 'Edit Assessment', 'mi-assessments' ),
			'update_item'        => __( 'Update Assessment', 'mi-assessments' ),
			'search_items'       => __( 'Search Assessment', 'mi-assessments' ),
			'not_found'          => __( 'Not Found', 'mi-assessments' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'mi-assessments' ),
		);

		// Set other options for Custom Post Type.
		$args = array(
			'label'               => __( 'assessments', 'mi-assessments' ),
			'description'         => __( 'Custom post type for storing the API assessment details', 'mi-assessments' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
		);

		// Registering your Custom Post Type.
		register_post_type( 'tti_assessments', $args );
	}

	/**
	 * Function to add the custom column to the tti_assessment post type.
	 *
	 * @since    1.0.0
	 * @param array $columns contains custom post type tti_assessment columns.
	 * @return array return columns
	 */
	public function assessment_add_custom_column( $columns ) {

		$columns['organization']      = __( 'Title', 'mi-assessments' );
		$columns['account_id']        = __( 'Account ID', 'mi-assessments' );
		$columns['link_id']           = __( 'Link ID', 'mi-assessments' );
		$columns['group_leader']      = __( 'Group Leader(s)', 'mi-assessments' );
		$columns['status_assessment'] = __( 'Status', 'mi-assessments' );
		$columns['report_api_check']  = __( 'Report Type', 'mi-assessments' );

		return $columns;
	}

	/**
	 * Function to add the data to the custom column of the assessment post type tti_assessment.
	 *
	 * @since   1.0.0
	 *
	 * @param string  $column contains all columns data for post type tti_assessment.
	 * @param integer $post_id contains post ID.
	 */
	public function assessment_add_custom_column_data( $column, $post_id ) {

		switch ( $column ) {

			case 'organization':
				echo esc_html( get_post_meta( $post_id, 'organization', true ) );
				break;

			case 'account_id':
				echo esc_html( get_post_meta( $post_id, 'account_login', true ) );
				break;

			case 'link_id':
				echo esc_html( get_post_meta( $post_id, 'link_id', true ) );
				break;

			case 'status_assessment':
				echo esc_html( get_post_meta( $post_id, 'status_assessment', true ) );
				break;

			case 'group_leader':
				echo esc_html( get_post_meta( $post_id, 'send_rep_group_lead', true ) );
				break;

			case 'report_api_check':
				$report_api_check = get_post_meta( $post_id, 'report_api_check', true );
				echo esc_html( ( 'yes' === strtolower( $report_api_check ) || empty( $report_api_check ) ) ? 'API' : 'Response' );
				break;

		}
	}

	/**
	 * Function to make the custom column sortable for assessment post type tti_assessment.
	 *
	 * @since   1.0.0
	 *
	 * @param array $columns contains columns data for assessment post type tti_assessment.
	 * @return array contains latest columns data
	 */
	public function assessment_add_custom_column_make_sortable( $columns ) {

		$columns['organization']      = 'organization';
		$columns['account_id']        = 'Account ID';
		$columns['link_id']           = 'Link ID';
		$columns['group_leader']      = 'Group Leader(s)';
		$columns['status_assessment'] = 'Status';
		$columns['report_api_check']  = 'Report Type';

		return $columns;
	}

	/**
	 * Function to add metabox in post type tti_assessment.
	 *
	 * @since   1.0.0
	 */
	public function assessment_meta_box() {
		add_meta_box(
			'assessment-meta-box', // ID.
			__( 'Assessment Details', 'mi-assessments' ), // Title.
			array( $this, 'assessment_meta_box_render' ), // Callback.
			'tti_assessments' // Screen.
		);

	}

	/**
	 * Callback Function to render metabox HTML in post type tti_assessment.
	 *
	 * @since   1.0.0
	 * @param array $post contains post data.
	 */
	public function assessment_meta_box_render( $post ) {

		$organization         = ( ! empty( get_post_meta( $post->ID, 'organization', true ) ) ) ? get_post_meta( $post->ID, 'organization', true ) : '';
		$api_key              = ( ! empty( get_post_meta( $post->ID, 'api_key', true ) ) ) ? get_post_meta( $post->ID, 'api_key', true ) : '';
		$account_login        = ( ! empty( get_post_meta( $post->ID, 'account_login', true ) ) ) ? get_post_meta( $post->ID, 'account_login', true ) : '';
		$api_service_location = ( ! empty( get_post_meta( $post->ID, 'api_service_location', true ) ) ) ? get_post_meta( $post->ID, 'api_service_location', true ) : '';
		$survay_location      = ( ! empty( get_post_meta( $post->ID, 'survay_location', true ) ) ) ? get_post_meta( $post->ID, 'survay_location', true ) : '';
		$link_id              = ( ! empty( get_post_meta( $post->ID, 'link_id', true ) ) ) ? get_post_meta( $post->ID, 'link_id', true ) : '';
		$status_assessment    = ( ! empty( get_post_meta( $post->ID, 'status_assessment', true ) ) ) ? get_post_meta( $post->ID, 'status_assessment', true ) : '';
		$print_report         = ( ! empty( get_post_meta( $post->ID, 'print_report', true ) ) ) ? get_post_meta( $post->ID, 'print_report', true ) : '';
		$report_metadata      = ( ! empty( get_post_meta( $post->ID, 'report_metadata', true ) ) ) ? get_post_meta( $post->ID, 'report_metadata', true ) : '';
		$report_api_check     = ( ! empty( get_post_meta( $post->ID, 'report_api_check', true ) ) ) ? get_post_meta( $post->ID, 'report_api_check', true ) : '';

		/* Check if assesment can be printed or not */
		$can_print_assessment = ( ! empty( get_post_meta( $post->ID, 'can_print_assessment', true ) ) ) ? get_post_meta( $post->ID, 'can_print_assessment', true ) : '';

		/* Check if email send to group leader or not */
		$send_rep_group_lead = ( ! empty( get_post_meta( $post->ID, 'send_rep_group_lead', true ) ) ) ? get_post_meta( $post->ID, 'send_rep_group_lead', true ) : '';

		require_once MI_ADMIN_PATH . 'partials/mi-assessments-meta-box.php';
	}

	/**
	 * Function to add meta boxes with the input fields.
	 *
	 * @since   1.0.0
	 * @param integer $post_id contains post ID.
	 */
	public function assessment_meta_box_information( $post_id ) {

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$posted_data = filter_input_array( INPUT_POST );

		if ( isset( $posted_data['send_rep_group_lead'] ) ) {
			update_post_meta( $post_id, 'send_rep_group_lead', sanitize_text_field( $posted_data['send_rep_group_lead'] ) );
		}

		if ( isset( $posted_data['organization'] ) ) {
			update_post_meta( $post_id, 'organization', sanitize_text_field( $posted_data['organization'] ) );
		}

		if ( isset( $posted_data['api_key'] ) ) {
			update_post_meta( $post_id, 'api_key', sanitize_text_field( $posted_data['api_key'] ) );
		}

		if ( isset( $posted_data['account_login'] ) ) {
			update_post_meta( $post_id, 'account_login', sanitize_text_field( $posted_data['account_login'] ) );
		}

		if ( isset( $posted_data['api_service_location'] ) ) {
			update_post_meta( $post_id, 'api_service_location', sanitize_text_field( $posted_data['api_service_location'] ) );
		}

		if ( isset( $posted_data['survay_location'] ) ) {
			update_post_meta( $post_id, 'survay_location', sanitize_text_field( $posted_data['survay_location'] ) );
		}

		if ( isset( $posted_data['link_id'] ) ) {
			update_post_meta( $post_id, 'link_id', sanitize_text_field( $posted_data['link_id'] ) );
		}

		if ( isset( $posted_data['print_report'] ) ) {
			update_post_meta( $post_id, 'print_report', sanitize_text_field( $posted_data['print_report'] ) );
		}

		if ( isset( $posted_data['report_api_check'] ) ) {
			update_post_meta( $post_id, 'report_api_check', sanitize_text_field( $posted_data['report_api_check'] ) );
		}

	}

	/**
	 * Function to validate assessment before adding into the assessment post type.
	 *
	 * @since    1.0.0
	 */
	public function mi_validate_assessment_details_before_adding_callback() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_admin_nonce', 'nonce' );

		if ( isset( $_POST['api_service_location'] ) ) {
			$api_service_location = sanitize_text_field( wp_unslash( $_POST['api_service_location'] ) );
		}

		if ( isset( $_POST['api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
		}

		if ( isset( $_POST['tti_link_id'] ) ) {
			$link_id = sanitize_text_field( wp_unslash( $_POST['tti_link_id'] ) );
		}

		// get the assessment by link id.
		$response_body = json_decode( $this->mi_api->mi_get_assessment_by_link( $api_service_location, $api_key, $link_id ) );

		$can_print_report = 'false';

		$log_details = array(
			'ACTION' => 'Validating assessment before inserting.',
		);

		// Check for error.
		if ( isset( $response_body->status ) && 'error' === $response_body->status ) {

			$can_print_report             = 'false';
			$api_response['print_status'] = $can_print_report;
			$api_response['status']       = 'error';

			/* error message */
			if ( isset( $response_body->message ) && '' !== $response_body->message ) {
				$api_response['message'] = $response_body->message;
			} else {
				$api_response['message'] = 'This Link Login not found and cannot be added. Please provide a valid details.';
			}

			// log event info.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'error' );

			echo wp_json_encode( $api_response );

			wp_die();
		}

		// No error found so far. GOOD TO GO!
		if ( isset( $response_body->email_to ) && true === $response_body->email_to ) {
			$can_print_report = 'true';
		}

		// Assessment status.
		if ( isset( $response_body->disabled ) && ( 0 === $response_body->disabled ) ) {
			$api_response['assessment_status_hidden'] = 'true';
		} else {
			$api_response['assessment_status_hidden'] = 'false';
			$api_response['message']                  = 'This Link Login is disabled and cannot be added. Please provide a valid Link Login.';
		}

		// Assessment name.
		if ( isset( $response_body->name ) && '' !== $response_body->name ) {
			$api_response['assessment_name_hidden'] = $response_body->name;
		} else {
			$api_response['assessment_name_hidden'] = 'Assessment';
		}

		// Assessment locked status.
		if ( isset( $response_body->locked ) && true === $response_body->locked ) {
			$api_response['assessment_locked_status'] = 'true';
		} else {
			$api_response['assessment_locked_status'] = 'false';
		}

		$api_response['print_status'] = $can_print_report;
		$api_response['status']       = 'success';

		// log event info.
		Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'success' );

		echo wp_json_encode( $api_response );

		wp_die();

	}

	/**
	 * Function to insert assessment as a post type tti_assessment.
	 *
	 * @since   1.0.0
	 */
	public function insert_assessments() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_admin_nonce', 'nonce' );

		$log_details = array(
			'ACTION' => 'Inserting assessment into the database after validation.',
		);

		global $wpdb, $post;

		$mi_secret_key   = get_option( 'ttiplatform_secret_key' );
		$mi_listener_url = get_option( 'ttiplatform_secret_key_listener' );

		// early bail. Any one from these 2 variable is not set or empty.
		if ( ! isset( $mi_secret_key ) || ! isset( $mi_listener_url ) || empty( $mi_secret_key ) || empty( $mi_listener_url ) ) {

			// log event info.
			$log_details['message'] = 'Secret Key or Return URL is not set. Please set them first.';
			Mi_Error_Log::put_error_log( $log_details, 'array', 'error' );

			echo wp_json_encode( array( 'status' => 'return_url' ) );
			wp_die();

		}

		$name                        = ( isset( $_POST['name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$link_id                     = ( isset( $_POST['link_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['link_id'] ) ) : '';
		$status_assessment           = ( isset( $_POST['status_assessment'] ) ) ? sanitize_text_field( wp_unslash( $_POST['status_assessment'] ) ) : '';
		$organization_hidden         = ( isset( $_POST['organization_hidden'] ) ) ? sanitize_text_field( wp_unslash( $_POST['organization_hidden'] ) ) : '';
		$print_report                = ( isset( $_POST['print_report'] ) ) ? sanitize_text_field( wp_unslash( $_POST['print_report'] ) ) : '';
		$send_rep_group_lead         = ( isset( $_POST['send_rep_group_lead'] ) ) ? sanitize_text_field( wp_unslash( $_POST['send_rep_group_lead'] ) ) : '';
		$api_key_hidden              = ( isset( $_POST['api_key_hidden'] ) ) ? sanitize_text_field( wp_unslash( $_POST['api_key_hidden'] ) ) : '';
		$account_login_hidden        = ( isset( $_POST['account_login_hidden'] ) ) ? sanitize_text_field( wp_unslash( $_POST['account_login_hidden'] ) ) : '';
		$api_service_location_hidden = ( isset( $_POST['api_service_location_hidden'] ) ) ? sanitize_text_field( wp_unslash( $_POST['api_service_location_hidden'] ) ) : '';
		$survay_location_hidden      = ( isset( $_POST['survay_location_hidden'] ) ) ? sanitize_text_field( wp_unslash( $_POST['survay_location_hidden'] ) ) : '';
		$status_locked               = ( isset( $_POST['status_locked'] ) ) ? sanitize_text_field( wp_unslash( $_POST['status_locked'] ) ) : '';
		$report_api_check            = ( isset( $_POST['report_api_check'] ) ) ? sanitize_text_field( wp_unslash( $_POST['report_api_check'] ) ) : '';

		// Check application screening addon.
		// phpcs:ignore
		if ( in_array( 'tti-platform-application-screening/tti-platform-application-screening.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			/* if addon exists */
		} elseif ( 'false' === $status_locked ) {
			// if addon not exists.
			echo wp_json_encode( array( 'status' => '6' ) );
			wp_die();
		}

		$status_assessment = ( 'true' === $status_assessment ) ? 'Active' : 'Suspended';

		// phpcs:ignore
		$wpdb->query(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts
				WHERE `post_title` = %s
				AND `post_type` = %s",
				$name,
				'tti_assessments'
			)
		);

		// Early bail. Assessment already created.
		if ( $wpdb->num_rows ) {

			$message       = __( 'Assessment already exist.', 'mi-assessments' );
			$popup_message = '';

			if ( isset( $wpdb->last_result[0]->ID ) && $wpdb->last_result[0]->ID > 0 ) {

				$post_status = get_post_status( $wpdb->last_result[0]->ID );
				$post_id     = $wpdb->last_result[0]->ID;

				if ( 'trash' === $post_status ) {

					$status        = 3; // 3 status means post is in trash.
					$popup_message = __( 'Your assessment is in the trash. Would you like to restore the assessment?', 'mi-assessments' );
					$message       = $post_id;

				} elseif ( 'publish' === $post_status ) {

					$status        = 2; // 2 status means post already publish
					$popup_message = __( 'Assessment Already exists.', 'mi-assessments' );

				} else {

					$status        = 0; // 0 status means post exist with different publish_status
					$popup_message = __( 'Assessment already exists. Please check with developer about the publish status', 'mi-assessments' );

				}
			} else {
				$status        = 0;
				$popup_message = __( 'Assessment already exists. Please check with developer about the publish status', 'mi-assessments' );
			}

			$already_exist = array(
				'status'        => $status,
				'message'       => $message,
				'popup_message' => $popup_message,
				'post_status'   => $post_status,
			);

			Mi_Error_Log::put_error_log( array_merge( $log_details, $already_exist ), 'array', 'error' );

			echo wp_json_encode( $already_exist );

			wp_die();
		}

		// Assessment not exist. Let's create one.
		$post_id = wp_insert_post(
			array(
				'post_type'      => 'tti_assessments',
				'post_title'     => $name,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		// Error in creating assessment.
		if ( is_wp_error( $post_id ) || ! $post_id ) {

			$status        = 0;
			$message       = __( 'Error in adding assessment.', 'mi-assessments' );
			$popup_message = __( 'Error in creating assessment. Please check with developer about the publish status', 'mi-assessments' );

			$insert_error = array(
				'message'       => $message,
				'status'        => $status,
				'popup_message' => $popup_message,
			);

			Mi_Error_Log::put_error_log( array_merge( $log_details, $insert_error ), 'array', 'error' );

			echo wp_json_encode( $insert_error );

			wp_die();

		}

		// Successfully Created the assessment. Let's delete the transient.
		delete_transient( 'mi_assessments_post_type' );

		// Let's update the assessment meta.
		add_post_meta( $post_id, 'link_id', $link_id );
		add_post_meta( $post_id, 'status_locked', $status_locked );
		add_post_meta( $post_id, 'organization', $organization_hidden );
		add_post_meta( $post_id, 'api_key', $api_key_hidden );
		add_post_meta( $post_id, 'account_login', $account_login_hidden );
		add_post_meta( $post_id, 'api_service_location', $api_service_location_hidden );
		add_post_meta( $post_id, 'survay_location', $survay_location_hidden );
		add_post_meta( $post_id, 'status_assessment', $status_assessment );
		add_post_meta( $post_id, 'report_api_check', $report_api_check );

		// Assessment successfully inserted. Get the assessment by link id and save other metadata.
		$response_body = $this->mi_api->mi_get_assessment_by_link( $api_service_location_hidden, $api_key_hidden, $link_id );

		$can_print_report      = 'false';
		$can_group_leader_mail = 'false';

		if ( isset( $response_body->email_to ) && true === $response_body->email_to ) {
			$can_print_report      = 'true';
			$can_group_leader_mail = 'true';
		}

		add_post_meta( $post_id, 'can_print_assessment', $can_print_report );

		/* Update Can Print Report function */
		if ( 'true' === $can_print_report ) {
			add_post_meta( $post_id, 'print_report', $print_report );
		} else {
			add_post_meta( $post_id, 'print_report', '' );
		}

		/* Update the Group Leader Mail function */
		if ( 'true' === $can_group_leader_mail ) {
			add_post_meta( $post_id, 'send_rep_group_lead', $send_rep_group_lead );
		} else {
			add_post_meta( $post_id, 'send_rep_group_lead', '' );
		}

		foreach ( $response_body->reportviews as $key => $value ) {
			$report_id   = $value->id;
			$report_data = $this->mi_api->mi_get_report_metadata( $report_id, $api_service_location_hidden, $api_key_hidden );
		}

		add_post_meta( $post_id, 'report_metadata', serialize( $report_data ) ); // phpcs:ignore

		// set the listener URL of this assessments.
		$this->mi_api->mi_set_return_url_of_newly_created_assessment( $post_id );

		$message = __( 'Assessment successfully added.', 'mi-assessments' );
		$status  = 1;

		$insert_success = array(
			'status'        => $status,
			'message'       => $message,
			'popup_message' => $message,
		);

		Mi_Error_Log::put_error_log( array_merge( $log_details, $insert_success ), 'array', 'success' );

		echo wp_json_encode( $insert_success );

		wp_die();

	}

	/**
	 * Restore the trashed post by post id.
	 *
	 * @since   1.0.0
	 */
	public function restore_trashed_post() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_admin_nonce', 'nonce' );

		$status = 0;

		if ( isset( $_POST['post_id'] ) ) {
			$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
			$result  = wp_untrash_post( $post_id );

			if ( $result ) {
				$status = 1;
			}
		}

		$arr = array( 'status' => $status );
		echo wp_json_encode( $arr );

		wp_die();
	}

	/**
	 * Function to add your custom bulk action in dropdown.
	 *
	 * @since    1.0.0
	 *
	 * @param array $bulk_actions contains bulk action data.
	 * @return array contains latest bulk option data
	 */
	public function mi_assessment_register_bulk_actions( $bulk_actions ) {

		$bulk_actions['assessment_activation'] = __( 'Active', 'mi-assessments' );
		$bulk_actions['assessment_suspended']  = __( 'Suspended', 'mi-assessments' );

		return $bulk_actions;

	}

	/**
	 * Process the bulk action for custom post type 'TTI Assessments'. Function to make sure that action name in the hook is the same like the option value.
	 *
	 * @param string $redirect Redirect URL to after performing the action.
	 * @param string $doaction Which custom bulk action do we need to perform.
	 * @param array  $object_ids Object IDs of the selected post.
	 *
	 * @return string
	 */
	public function mi_assessment_process_bulk_actions( $redirect, $doaction, $object_ids ) {

		// let's remove query args first. These are added after performing the actions.
		$redirect = remove_query_arg(
			array( 'assessment_status_active', 'assessment_status_suspend' ),
			$redirect
		);

		if ( 'assessment_activation' === $doaction ) {

			// set variable for "assessment_activation" bulk action.
			$request_action = 'Active';
			$request_method = 'PUT';

			// do not forget to add query args to URL because we will show notices later.
			$redirect = add_query_arg(
				'assessment_status_active', // just a parameter for URL.
				count( $object_ids ), // how many posts have been selected.
				$redirect
			);

		} elseif ( 'assessment_suspended' === $doaction ) {

			// set variable for "assessment_suspended" bulk action.
			$request_action = 'Suspended';
			$request_method = 'DELETE';

			// do not forget to add query args to URL because we will show notices later.
			$redirect = add_query_arg(
				'assessment_status_suspend', // just a parameter for URL.
				count( $object_ids ), // how many posts have been selected.
				$redirect
			);

		}

		foreach ( $object_ids as $post_id ) {
			$api_key              = ( ! empty( get_post_meta( $post_id, 'api_key', true ) ) ) ? get_post_meta( $post_id, 'api_key', true ) : '';
			$api_service_location = ( ! empty( get_post_meta( $post_id, 'api_service_location', true ) ) ) ? get_post_meta( $post_id, 'api_service_location', true ) : '';
			$link_id              = ( ! empty( get_post_meta( $post_id, 'link_id', true ) ) ) ? get_post_meta( $post_id, 'link_id', true ) : '';

			/* API v3.0 url */
			$url = $api_service_location . '/api/v3/links/' . $link_id . '/enable';

			$response_body = $this->mi_api->mi_send_api_request( $url, $api_key, $request_method );

			if ( isset( $response_body ) ) {
				update_post_meta( $post_id, 'status_assessment', $request_action );
			}
		}

		return $redirect;
	}

	/**
	 * Function to assessment update notices on activation.Function to
	 *
	 * @since   1.0.0
	 */
	public function mi_assessment_bulk_action_process_notices() {

		// validate the default nonce of WordPress.
		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-posts' );
		}

		// Admin notice for assessment bulk action 'Active'.
		if ( ! empty( $_REQUEST['assessment_status_active'] ) ) {

			$count = (int) $_REQUEST['assessment_status_active'];

			$message = sprintf(
				// translators: %d: Number of assessments affected.
				_n(
					'Status of %d assessment has been changed to "Active".',
					'Status of %d assessments have been changed to "Active".',
					$count
				),
				number_format_i18n( $count )
			);

			/**
			 * Filter to update assessment active order status notices
			 *
			 * @since  1.2
			 */
			$message = apply_filters( 'ttisi_platform_assessment_active_order_status_notices', $message );

			echo wp_kses( '<div class="updated notice is-dismissible"><p>' . $message . '</p></div>', 'post' );

		}

		// Admin notice for assessment bulk action 'Suspend'.
		if ( ! empty( $_REQUEST['assessment_status_suspend'] ) ) {

			$count = (int) $_REQUEST['assessment_status_suspend'];

			$message = sprintf(
				// translators: %d: Number of assessments affected.
				_n(
					'Status of %d assessment has been changed to "Suspend".',
					'Status of %d assessments have been changed to "Suspend".',
					$count
				),
				number_format_i18n( $count )
			);

			/**
			 * Filter to update assessment suspended order status notices message
			 *
			 * @since  1.2
			 */
			$message = apply_filters( 'ttisi_platform_assessment_suspended_order_status_notices', $message );

			echo wp_kses( '<div class="updated notice is-dismissible"><p>' . $message . '</p></div>', 'post' );

		}

	}

	/**
	 * Function to hide password field from Quick Edits in the assessment post type tti_assessment.
	 *
	 * @since   1.0.0
	 */
	public function remove_password_from_quick_edit() {
		global $current_screen;
		if ( 'edit-tti_assessments' !== $current_screen->id ) {
			return;
		}
		?>
		<script type="text/javascript">         
			jQuery(document).ready( function($) {
				$('span:contains("Password")').each(function (i) {
					$(this).parent().parent().remove();
				});
			});    
		</script>
		<?php
	}


}

// Initialize the assessment post type class.
new Mi_Assessments_CPT();
