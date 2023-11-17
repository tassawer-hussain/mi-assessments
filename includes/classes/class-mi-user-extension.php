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
class MI_User_Extension {

	/**
	 * String contains user id
	 *
	 * @var string
	 */
	private $user_id;

	/**
	 * Array contains assessments lists.
	 *
	 * @var array
	 */
	protected $list_assessments;

	/**
	 * Contains list page action.
	 *
	 * @var string
	 */
	public $list_action;

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       1.7.0
	 */
	public function __construct() {

		// Create API class instance.
		$this->mi_api = new Mi_Assessments_API();

		// set user id property.
		$this->user_id = $this->get_user_id();

		$this->return_url = add_query_arg(
			array(
				'page'    => 'tti-profile-assessment-page',
				'user_id' => $this->user_id,
			),
			admin_url( 'users.php', 'https' )
		);

		// AJAX callback to save mapping date from mapping page.
		add_action( 'wp_ajax_save_mapping_data', array( $this, 'tti_save_mapping_data' ) );
		add_action( 'wp_ajax_nopriv_save_mapping_data', array( $this, 'tti_save_mapping_data' ) );

		// Setup hidden menu item under the users.
		add_action( 'admin_menu', array( $this, 'user_profile_menu' ), 999 );

		// Display assessment tab on user profile screen.
		add_action( 'edit_user_profile', array( $this, 'tti_platform_user_template' ), 999, 1 );
		add_action( 'show_user_profile', array( $this, 'tti_platform_user_template' ), 999, 1 );

		// Ajax callback to validate user assessment.
		add_action( 'wp_ajax_validate_user_assessment', array( $this, 'mi_validate_user_assessment_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_validate_user_assessment', array( $this, 'mi_validate_user_assessment_ajax_callback' ) );

		// Ajax callback to insert user assessment.
		add_action( 'wp_ajax_insert_user_assessments', array( $this, 'mi_insert_user_assessment_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_insert_user_assessments', array( $this, 'mi_insert_user_assessment_ajax_callback' ) );

		// Ajax callback to update user assessment.
		add_action( 'wp_ajax_update_user_assessment', array( $this, 'mi_update_user_assessment_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_update_user_assessment', array( $this, 'mi_update_user_assessment_ajax_callback' ) );

		// Ajax callback to insert user assessment settings.
		add_action( 'wp_ajax_insert_user_asses_settings', array( $this, 'mi_insert_user_assesement_settings_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_insert_user_asses_settings', array( $this, 'mi_insert_user_assesement_settings_ajax_callback' ) );

	}

	/**
	 * Get the user ID.
	 *
	 * Look for $_GET['user_id']. If anything else, force the user ID to the
	 * current user's ID so they aren't left without a user to edit.
	 *
	 * @since 1.7.0
	 *
	 * @return int
	 */
	private function get_user_id() {

		$this->user_id = (int) get_current_user_id();

		// We'll need a user ID when not on self profile.
		if ( ! empty( $_GET['user_id'] ) ) { // phpcs:ignore
			$this->user_id = (int) $_GET['user_id']; // phpcs:ignore
		}

		return $this->user_id;
	}

	/**
	 * Function to validate given user assessment
	 *
	 * @since   1.7.0
	 */
	public function tti_save_mapping_data() {

		require_once MI_INCLUDES_PATH . 'classes/class-mi-mapping-handler.php';

		$ajax_obj = new Mi_Mapping_Handler();
		$ajax_obj->tti_update_mapping_data();

	}

	/**
	 * Register the assessment page for user profile tab
	 *
	 * @since 1.7.0
	 */
	public function user_profile_menu() {

		// Register the hidden submenu.
		add_submenu_page(
			'profile.php', // Use the parent slug as usual.
			null,
			'',
			'manage_options',
			'tti-profile-assessment-page',
			array( $this, 'tti_display_user_assessments' )
		);
	}

	/**
	 * Function to check request to user page content
	 *
	 * @since   1.7.0
	 */
	public function tti_display_user_assessments() {

		$user_id       = $this->user_id;
		$settings_data = $this->get_assess_settings();
		$profile_url   = get_edit_user_link( $this->user_id );

		$this->tti_platform_decide_action();

		if ( 'edit' === $this->list_action ) {

			// edit assessment action form.
			$this->tti_platform_handle_edit_action();

		} elseif ( 'delete' === $this->list_action ) {

			// delete assessment action form.
			$this->tti_platform_handle_del_action();

		} else {

			// display assessments list.
			$this->tti_platform_add_wp_lists();
			$ass_lists = $this->get_assess_list();
			$lists_obj = new Mi_User_Assessment_Lists( $ass_lists );

			require_once MI_ADMIN_PATH . 'partials/user/mi-user-level-assessments.php';
		}

	}

	/**
	 * Function to handle edit assessment action
	 *
	 * @since   1.7.0
	 * @return array
	 */
	public function get_assess_settings() {

		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessment-handler.php';

		$ass_hndlr_obj = new Mi_User_Assessment_Handler();
		$settings_data = $ass_hndlr_obj->tti_return_assessments_settings( $this->user_id );

		return $settings_data;

	}

	/**
	 * Function to decide list page action
	 *
	 * @since   1.7.0
	 * @return void
	 */
	public function tti_platform_decide_action() {
		$this->list_action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list'; // phpcs:ignore
	}

	/**
	 * Function to handle edit assessment action
	 *
	 * @since   1.7.0
	 * @return void
	 */
	public function tti_platform_handle_edit_action() {

		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessment-handler.php';

		$ass_hndlr_obj = new Mi_User_Assessment_Handler();
		$result        = $ass_hndlr_obj->tti_show_edit_user_form( $this->user_id );
	}

	/**
	 * Function to handle delete assessment action
	 *
	 * @since   1.7.0
	 * @return void
	 */
	public function tti_platform_handle_del_action() {

		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessment-handler.php';

		$ass_hndlr_obj = new Mi_User_Assessment_Handler();
		$result        = $ass_hndlr_obj->tti_delete_user_assessment( $this->user_id );

		if ( $result ) {
			$this->render_notice( 'true' );
		} else {
			$this->render_notice( 'false' );
		}
	}

	/**
	 * Function to render action message
	 *
	 * @since   1.7.0
	 *
	 * @param string $msg Contain current user id.
	 * @return void
	 */
	public function render_notice( $msg ) {

		if ( 'true' === $msg ) {

			echo '<a href="' . esc_attr( esc_url_raw( $this->return_url, 'https' ) ) . '">Back To Assessments</a>
            <div class="tti-ass-del-success"><p><strong>Success</strong>: Assessment deleted successfully</p></div>';
		} else {
			echo '<a href="' . esc_attr( esc_url_raw( $this->return_url, 'https' ) ) . '">Back To Assessments</a>
            <div class="tti-ass-del-error"><p><strong>Error</strong>: Assessment deletion failed</p></div>';
		}

	}

	/**
	 * Function to add logic files related to WP_List_Table
	 *
	 * @since   1.7.0
	 * @return void
	 */
	public function tti_platform_add_wp_lists() {

		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessment-lists.php';
	}

	/**
	 * Function to handle edit assessment action
	 *
	 * @since   1.7.0
	 * @return array
	 */
	public function get_assess_list() {

		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessment-handler.php';

		$ass_hndlr_obj = new Mi_User_Assessment_Handler();
		$lists         = $ass_hndlr_obj->tti_return_assessments_curr_user( $this->user_id );

		return $lists;
	}

	/**
	 * Function to register the button
	 *
	 * @since   1.7.0
	 * @param array $buttons contains buttons.
	 *
	 * @return void
	 */
	public function tti_platform_user_template( $buttons ) {

		if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
			$position_css = 'left: 17.4%;';
		} else {
			$position_css = '
                border-bottom: 1px solid #ccc;
                margin: 0;
                padding-top: 9px;
                padding-bottom: 0;
                line-height: inherit;';
		}

		if ( current_user_can( 'edit_user' ) ) :
			printf( '<h2 id="tti-profile-user-nav" class="tti-nav-tab-wrapper" style="%s"><a class="tti-nav-tab" href="%s">%s</a></h2>', esc_attr( $position_css ), esc_url( $this->return_url ), esc_html__( 'Assessment', 'tti-platform' ) );
		endif;

	}

	/**
	 * Function to validate given user assessment
	 *
	 * @since   1.7.0
	 */
	public function mi_validate_user_assessment_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_user_level_assessment_nonce', 'nonce' );

		$response = array();

		if ( isset( $_POST['api_key_user'] ) && ! empty( $_POST['api_key_user'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key_user'] ) );
		}

		if ( isset( $_POST['api_service_location_user'] ) && ! empty( $_POST['api_service_location_user'] ) ) {
			$api_service_location = sanitize_text_field( wp_unslash( $_POST['api_service_location_user'] ) );
		}

		if ( isset( $_POST['tti_link_id_user'] ) && ! empty( $_POST['tti_link_id_user'] ) ) {
			$tti_link_id = sanitize_text_field( wp_unslash( $_POST['tti_link_id_user'] ) );
		}

		$can_print_report = 'false';

		/* API v 3.0 url */
		$url = $api_service_location . '/api/v3/links/' . $tti_link_id;

		$response_body = $this->mi_api->mi_send_api_request( $url, $api_key, 'GET' );

		$response_body = json_decode( $response_body );

		// early bail if encounter any issue in API call.
		if ( 'error' === $response_body->status ) {
			echo wp_json_encode( $response_body );
			exit;
		}

		$api_response_pt = $response_body->response;

		// No error encounter.
		if ( isset( $api_response_pt->email_to ) && true === $api_response_pt->email_to ) {
			$can_print_report = 'true';
		}

		/* Assessment status */
		if ( isset( $api_response_pt->disabled ) && true !== $api_response_pt->disabled ) {
			$api_response['assessment_status_hidden'] = 'true';
		} else {
			$api_response['assessment_status_hidden'] = 'false';
			$api_response['message']                  = 'This Link Login is disabled and cannot be added. Please provide a valid Link Login.';
		}

		/* Assessment name */
		if ( isset( $api_response_pt->name ) && '' !== $api_response_pt->name ) {
			$api_response['assessment_name_hidden'] = $api_response_pt->name;
		} else {
			$api_response['assessment_name_hidden'] = 'Assessment';
		}

		/* Assessment locked status */
		if ( isset( $api_response_pt->locked ) && true === $api_response_pt->locked ) {
			$api_response['assessment_locked_status'] = 'true';
		} else {
			$api_response['assessment_locked_status'] = 'false';
		}

		$api_response['print_status'] = $can_print_report;
		$api_response['status']       = 'success';

		echo wp_json_encode( $api_response );

		exit;
	}

	/**
	 * Function to validate given user assessment
	 *
	 * @since   1.7.0
	 */
	public function mi_insert_user_assessment_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_user_level_assessment_nonce', 'nonce' );

		if ( isset( $_POST['tti_user_id'] ) && ! empty( $_POST['tti_user_id'] ) ) {
			$user_id = sanitize_text_field( wp_unslash( $_POST['tti_user_id'] ) );
		}

		if ( isset( $_POST['name'] ) && ! empty( $_POST['name'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		}

		if ( isset( $_POST['link_id'] ) && ! empty( $_POST['link_id'] ) ) {
			$link_id = sanitize_text_field( wp_unslash( $_POST['link_id'] ) );
		}

		if ( isset( $_POST['status_assessment'] ) && ! empty( $_POST['status_assessment'] ) ) {
			$status_assessment = sanitize_text_field( wp_unslash( $_POST['status_assessment'] ) );
		}

		if ( isset( $_POST['organization_hidden'] ) && ! empty( $_POST['organization_hidden'] ) ) {
			$organization_hidden = sanitize_text_field( wp_unslash( $_POST['organization_hidden'] ) );
		}

		if ( isset( $_POST['print_report'] ) && ! empty( $_POST['print_report'] ) ) {
			$print_report = sanitize_text_field( wp_unslash( $_POST['print_report'] ) );
		}

		if ( isset( $_POST['send_rep_group_lead'] ) && ! empty( $_POST['send_rep_group_lead'] ) ) {
			$send_rep_group_lead = sanitize_text_field( wp_unslash( $_POST['send_rep_group_lead'] ) );
		}

		if ( isset( $_POST['api_key_hidden'] ) && ! empty( $_POST['api_key_hidden'] ) ) {
			$api_key_hidden = sanitize_text_field( wp_unslash( $_POST['api_key_hidden'] ) );
		}

		if ( isset( $_POST['account_login_hidden'] ) && ! empty( $_POST['account_login_hidden'] ) ) {
			$account_login_hidden = sanitize_text_field( wp_unslash( $_POST['account_login_hidden'] ) );
		}

		if ( isset( $_POST['api_service_location_hidden'] ) && ! empty( $_POST['api_service_location_hidden'] ) ) {
			$api_service_location_hidden = sanitize_text_field( wp_unslash( $_POST['api_service_location_hidden'] ) );
		}

		if ( isset( $_POST['survay_location_hidden'] ) && ! empty( $_POST['survay_location_hidden'] ) ) {
			$survay_location_hidden = sanitize_text_field( wp_unslash( $_POST['survay_location_hidden'] ) );
		}

		if ( isset( $_POST['status_locked'] ) && ! empty( $_POST['status_locked'] ) ) {
			$status_locked = sanitize_text_field( wp_unslash( $_POST['status_locked'] ) );
		}

		if ( isset( $_POST['report_api_check'] ) && ! empty( $_POST['report_api_check'] ) ) {
			$report_api_check = sanitize_text_field( wp_unslash( $_POST['report_api_check'] ) );
		}

		$user_ass = array(
			'title'                => $organization_hidden,
			'account_login'        => $account_login_hidden,
			'api_key'              => $api_key_hidden,
			'api_service_location' => $api_service_location_hidden,
			'survey_location'      => $survay_location_hidden,
			'link_id'              => $link_id,
			'status_assessment'    => $status_assessment,
			'organization_hidden'  => $organization_hidden,
			'print_report'         => $print_report,
			'send_rep_group_lead'  => $send_rep_group_lead,
			'status_locked'        => $status_locked,
			'report_api_check'     => $report_api_check,
			'name'                 => $name,
		);

		/* saving report metadata script */
		$url = $api_service_location_hidden . '/api/v3/links/' . $link_id;

		$response_body = $this->mi_api->mi_send_api_request( $url, $api_key_hidden, 'GET' );

		$response_body = json_decode( $response_body );

		// early bail if encounter any issue in API call.
		if ( 'error' === $response_body->status ) {
			echo wp_json_encode( $response_body );
			exit;
		}

		$response = $response_body->response;

		// can print report script.
		$can_print_report      = 'false';
		$can_group_leader_mail = 'false';

		if ( isset( $response->email_to ) && true === $response->email_to ) {
			$can_print_report      = 'true';
			$can_group_leader_mail = 'true';
		}

		$user_ass['can_print_assessment'] = $can_print_report;

		if ( 'true' === $can_print_report ) {
			$user_ass['print_report'] = $print_report;
		} else {
			$user_ass['print_report'] = '';
		}

		// Update the Group Leader Mail function.
		if ( 'true' === $can_group_leader_mail ) {
			$user_ass['send_rep_group_lead'] = $send_rep_group_lead;
		} else {
			$user_ass['send_rep_group_lead'] = '';
		}

		// Api report.
		if ( 'yes' === strtolower( $report_api_check ) || 'no' === strtolower( $report_api_check ) ) {
			$user_ass['report_api_check'] = $report_api_check;
		} else {
			$user_ass['report_api_check'] = '';
		}

		$report_view_id = 0;
		$report_data    = array();

		/* can print report script ends */
		foreach ( $response->reportviews as $key => $value ) {
			$report_view_id                        = $value->id;
			$report_instrument_details             = $value->assessment; // link id / assessment instrument details.
			$user_ass['report_view_id']            = $report_view_id;
			$user_ass['report_instrument_details'] = $report_instrument_details;
			$report_data[ $report_view_id ]        = $this->get_report_metadata( $report_view_id, $api_service_location_hidden, $api_key_hidden );
		}

		// insert report metadata into usermeta.
		update_user_meta( $user_id, 'report_metadata_' . $link_id, serialize( $report_data ) ); // phpcs:ignore

		/* going to Update user metadata 'user_assessment_data' */
		$lists = get_user_meta( $user_id, 'user_assessment_data', true );
		$lists = unserialize( $lists ); // phpcs:ignore

		if ( ! empty( $lists ) && is_array( $lists ) ) {
			$lists[ $link_id ] = $user_ass;
		} else {
			$lists             = array();
			$lists[ $link_id ] = $user_ass;
		}

		$result = update_user_meta( $user_id, 'user_assessment_data', serialize( $lists ) ); // phpcs:ignore

		// everything went good so far.
		$api_response['status'] = 'success';

		echo wp_json_encode( $api_response );

		exit;

	}

	/**
	 * Function to validate given user assessment
	 *
	 * @since   1.7.0
	 */
	public function mi_update_user_assessment_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_user_level_assessment_nonce', 'nonce' );

		if ( isset( $_POST['tti_link_id_user'] ) && ! empty( $_POST['tti_link_id_user'] ) ) {
			$link_id = sanitize_text_field( wp_unslash( $_POST['tti_link_id_user'] ) );
		}

		if ( isset( $_POST['tti_user_id'] ) && ! empty( $_POST['tti_user_id'] ) ) {
			$user_id = sanitize_text_field( wp_unslash( $_POST['tti_user_id'] ) );
		}

		$lists = get_user_meta( $user_id, 'user_assessment_data', true );
		$lists = unserialize( $lists ); // phpcs:ignore

		$new_assess = array();

		if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
			$new_assess['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
		}

		if ( isset( $_POST['api_key_user'] ) && ! empty( $_POST['api_key_user'] ) ) {
			$new_assess['api_key'] = sanitize_text_field( wp_unslash( $_POST['api_key_user'] ) );
		}

		if ( isset( $_POST['account_login_user'] ) && ! empty( $_POST['account_login_user'] ) ) {
			$new_assess['account_login'] = sanitize_text_field( wp_unslash( $_POST['account_login_user'] ) );
		}

		if ( isset( $_POST['api_service_location_user'] ) && ! empty( $_POST['api_service_location_user'] ) ) {
			$new_assess['api_service_location'] = sanitize_text_field( wp_unslash( $_POST['api_service_location_user'] ) );
		}

		if ( isset( $_POST['survey_location'] ) && ! empty( $_POST['survey_location'] ) ) {
			$new_assess['survey_location'] = sanitize_text_field( wp_unslash( $_POST['survey_location'] ) );
		}

		$new_assess['link_id'] = $link_id;

		if ( isset( $_POST['status_assessment'] ) && ! empty( $_POST['status_assessment'] ) ) {
			$new_assess['status_assessment'] = sanitize_text_field( wp_unslash( $_POST['status_assessment'] ) );
		}

		if ( isset( $_POST['organization_hidden'] ) && ! empty( $_POST['organization_hidden'] ) ) {
			$new_assess['organization_hidden'] = sanitize_text_field( wp_unslash( $_POST['organization_hidden'] ) );
		}

		if ( isset( $_POST['print_report'] ) && ! empty( $_POST['print_report'] ) ) {
			$new_assess['print_report'] = sanitize_text_field( wp_unslash( $_POST['print_report'] ) );
		}

		if ( isset( $_POST['send_rep_group_lead'] ) && ! empty( $_POST['send_rep_group_lead'] ) ) {
			$new_assess['send_rep_group_lead'] = sanitize_text_field( wp_unslash( $_POST['send_rep_group_lead'] ) );
		}

		if ( isset( $_POST['status_locked'] ) && ! empty( $_POST['status_locked'] ) ) {
			$new_assess['status_locked'] = sanitize_text_field( wp_unslash( $_POST['status_locked'] ) );
		}

		if ( isset( $_POST['name'] ) && ! empty( $_POST['name'] ) ) {
			$new_assess['name'] = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		}

		if ( isset( $_POST['report_view_id'] ) && ! empty( $_POST['report_view_id'] ) ) {
			$new_assess['report_view_id'] = sanitize_text_field( wp_unslash( $_POST['report_view_id'] ) );
		}

		if ( isset( $_POST['report_api_check'] ) && ! empty( $_POST['report_api_check'] ) ) {
			$new_assess['report_api_check'] = sanitize_text_field( wp_unslash( $_POST['report_api_check'] ) );
		}

		$new_assess['report_instrument_details'] = $lists[ $link_id ]['report_instrument_details'];

		if ( ! empty( $lists ) && is_array( $lists ) ) {
			$lists[ $link_id ] = $new_assess;
		} else {
			$lists             = array();
			$lists[ $link_id ] = $new_assess;
		}

		update_user_meta( $user_id, 'user_assessment_data', serialize( $lists ) ); // phpcs:ignore

		$api_response['status'] = 'success';

		echo wp_json_encode( $api_response );

		exit;

	}

	/**
	 * Function to validate given user assessment
	 *
	 * @since   1.7.0
	 */
	public function mi_insert_user_assesement_settings_ajax_callback() {

		// check nonce.
		check_ajax_referer( 'mi_user_level_assessment_nonce', 'nonce' );

		if ( isset( $_POST['tti_user_id'] ) && ! empty( $_POST['tti_user_id'] ) ) {
			$tti_user_id = sanitize_text_field( wp_unslash( $_POST['tti_user_id'] ) );
		}

		if ( isset( $_POST['user_capa'] ) && ! empty( $_POST['user_capa'] ) ) {
			$settings_data['user_capa'] = sanitize_text_field( wp_unslash( $_POST['user_capa'] ) );
		}

		// Update user metadata.
		$result = update_user_meta( $tti_user_id, 'user_assessment_settings', serialize( $settings_data ) ); // phpcs:ignore

		$response = array(
			'status'  => 'success',
			'message' => 'Successfully Save Assessment Settings',
		);

		echo wp_json_encode( $response );
		exit;

	}


	/**
	 * Function to get all report metadata.
	 *
	 * @since    1.7.0
	 * @param array  $report_view_id contains report view id.
	 * @param string $api_service_location contains service location link.
	 * @param string $api_key contains api key.
	 * @return array contains api response
	 */
	public function get_report_metadata( $report_view_id, $api_service_location, $api_key ) {

		/* API v 3.0 url */
		$url = esc_url_raw( $api_service_location ) . '/api/v3/reportviews/' . $report_view_id;

		$response_body = json_decode( $this->mi_api->mi_send_api_request( $url, $api_key, 'GET' ) );

		// early bail if encounter any issue in API call.
		if ( 'error' === $response_body->status ) {
			return wp_json_encode( $response_body );
		}

		return $response_body->response;
	}

}

