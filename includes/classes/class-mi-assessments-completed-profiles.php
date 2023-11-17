<?php
/**
 * Display User assessment history.
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
 * This class defines all code necessary to display user assessment history.
 * API URL: https://api.ttiadmin.com/api/documentation
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Completed_Profiles {

	/**
	 * Contains array of completed profiles of the current user.
	 *
	 * @var array
	 */
	public $users_data;

	/**
	 * Array contains user details
	 *
	 * @var array
	 */
	public $users_details;

	/**
	 * Current user id
	 *
	 * @var integer
	 */
	public $current_user_id;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since       1.5.1
	 */
	public function __construct() {

		if ( ! is_user_logged_in() ) {

			// early bail. User is not logged in.
			esc_html_e( 'Please logged in to see your group users.', 'tti-platform' );

		} else {

			// Proceed only if user logged in.
			global $current_user;
			wp_get_current_user();

			$this->users_data      = array();
			$this->users_details   = array();
			$this->current_user_id = $current_user->ID;

			// 1 - load scripts & styles.
			$this->load_scripts_and_styles_for_completed_profiles();

			// 2 - Get user data. Display the data table of completed profile.
			$this->get_current_leader_users_data();
		}

	}

	/**
	 * Function to process the assessment history layout and data.
	 *
	 * @since       1.6
	 */
	public function load_scripts_and_styles_for_completed_profiles() {

		if ( ! is_admin() ) {

			// Registered CSS Files.
			wp_enqueue_style( 'mi-assessments-datatable' );

			wp_enqueue_style( 'mi-assessments-responsive-datatable' );

			wp_enqueue_style(
				'mi-assessments-completed-profiles',
				MI_PUBLIC_URL . 'css/mi-assessments-completed-profiles.css',
				array(),
				generate_random_string(),
				'all'
			);

			// Registered JS Files.
			wp_enqueue_script( 'mi-assessments-datatable' );

			wp_enqueue_script( 'mi-assessments-responsive-datatable' );

			wp_enqueue_script(
				'mi-assessments-completed-profiles',
				MI_PUBLIC_URL . 'js/mi-assessments-completed-profiles.js',
				array( 'jquery', 'mi-assessments-datatable', 'mi-assessments-responsive-datatable' ),
				generate_random_string(),
				true
			);

			wp_localize_script(
				'mi-assessments-completed-profiles',
				'mi_completed_profiles',
				array(
					'ajaxurl'      => admin_url( 'admin-ajax.php' ),
					'siteurl'      => site_url(),
					'menu_display' => __( 'Display _MENU_ entries', 'tti-platform-application-screening' ),
					'zeroRecords'  => __( 'Nothing found - sorry', 'tti-platform-application-screening' ),
					'info'         => __( 'Showing page _PAGE_ of _PAGES_', 'tti-platform-application-screening' ),
					'infoEmpty'    => __( 'No records available', 'tti-platform-application-screening' ),
					'infoFiltered' => __( '(filtered from _MAX_ total records)', 'tti-platform-application-screening' ),
					'Search'       => __( 'Search', 'tti-platform-application-screening' ),
					'First'        => __( 'First', 'tti-platform-application-screening' ),
					'Previous'     => __( 'Previous', 'tti-platform-application-screening' ),
					'Last'         => __( 'Last', 'tti-platform-application-screening' ),
					'Next'         => __( 'Next', 'tti-platform-application-screening' ),
				)
			);

		}
	}

	/**
	 * Function to get current leader users data.
	 *
	 * @since       1.5.1
	 */
	public function get_current_leader_users_data() {

		$this->users_data = $this->get_group_user_details();

		if ( is_array( $this->users_data ) && count( $this->users_data ) > 0 ) {

			foreach ( $this->users_data as $uid => $us_id ) {
				$this->get_single_user_details( $us_id );
			}
		} else {
			// User is not leader of any group or the current user has admin right.
			// fetch the assessments completed by him.
			$this->get_single_user_details( $this->current_user_id );
		}

		$data = $this->users_details;
		require_once MI_PUBLIC_PATH . 'partials/mi-assessments-completed-profiles.php';
	}

	/**
	 * Function to check if current user is group leader or not.
	 *
	 * @since       1.5.1
	 * @return boolean|array return group emails
	 */
	public function get_group_user_details() {

		$group_ids            = array();
		$group_leaders_emails = array();

		// Checks if the current user has the group leader capabilities. Returns true if the user is group leader otherwise false.
		$group_leader_status = learndash_is_group_leader_user( $this->current_user_id );

		// current user is not group leader.
		if ( ! $group_leader_status ) {
			return false;
		}

		// Gets the list of group IDs administered by the current user.
		$group_ids = learndash_get_administrators_group_ids( $this->current_user_id );

		// No group found.
		if ( count( $group_ids ) < 1 ) {
			return false;
		}

		$group_leaders_emails = array();

		foreach ( $group_ids as $k => $v ) {

			$key   = 'learndash_group_users_' . $v;
			$users = get_users(
				array(
					'meta_key' => $key, // phpcs:ignore
				)
			);

			if ( ! empty( $users ) ) {
				foreach ( $users as $key => $user ) {
					$group_leaders_emails[] = $user->ID;
				}
			}
		}

		return array_unique( $group_leaders_emails );
	}

	/**
	 * Function to get user details from assessments table.
	 *
	 * @since       1.5.1
	 * @param integer $us_id contains user id.
	 */
	public function get_single_user_details( $us_id ) {

		global $wpdb;
		$db_table_name = $wpdb->prefix . 'assessments';

		// phpcs:ignore
		$results = $wpdb->get_results(
			$wpdb->prepare( // phpcs:ignore
				// phpcs:ignore
				'SELECT * FROM %i
				WHERE `user_id` = %d',
				$db_table_name,
				$us_id
			)
		);

		foreach ( $results as $key => $value ) {
			if (
				isset( $value->first_name ) && ! empty( $value->first_name ) &&
				isset( $value->last_name ) && ! empty( $value->last_name ) &&
				isset( $value->link_id ) && ! empty( $value->link_id ) &&
				isset( $value->report_id ) && ! empty( $value->report_id ) &&
				isset( $value->api_token ) && ! empty( $value->api_token ) &&
				isset( $value->service_location ) && ! empty( $value->service_location ) &&
				isset( $value->created_at ) && ! empty( $value->created_at ) &&
				isset( $value->assessment_result ) && ! empty( $value->assessment_result )
			) {
				$this->users_details[] = $value;
			}
		}
	}

}

// initialize the class to output the date.
new Mi_Assessments_Completed_Profiles();
