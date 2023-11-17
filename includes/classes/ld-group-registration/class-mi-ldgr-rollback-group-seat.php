<?php
/**
 * Rollback Group Seat.
 *
 * @link       https://ministryinsights.com/
 * @since      1.6.3
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Rollback Group Seat if user has not taken the assessment or started the course.
 *
 * @since      1.6.3
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_LDGR_Rollback_Group_Seat {

	/**
	 * Define the constructor
	 *
	 * @since  1.6.3
	 */
	public function __construct() {

		// Roll back seat if user didn't start the course.
		add_action( 'wdm_removal_request_accepted_successfully', array( $this, 'tti_rollback_group_seat_on_removal' ), 9999, 2 );

	}

	/**
	 * Roll back seat if user didn't start the course.
	 *
	 * @param int $group_id Group ID.
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function tti_rollback_group_seat_on_removal( $group_id, $user_id ) {

		$seat_rollback       = false;
		$is_assessment_group = is_group_has_assessment_shortcode( $group_id );

		// assessment related group.
		if ( $is_assessment_group ) {

			$links_id = get_the_link_ids_from_group_id( $group_id );

			if ( count( $links_id ) > 0 ) {
				foreach ( $links_id as $link_id ) {

					global $wpdb;
					$assessment_table = $wpdb->prefix . 'assessments';

					// Prepare and execute the SQL query to get a single row.
					$results = $wpdb->get_row( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							'SELECT * FROM %i WHERE user_id = %s AND link_id = %s AND status = 1', // phpcs:ignore
							$assessment_table,
							$user_id,
							$link_id
						)
					);

					if ( null === $results ) {
						$seat_rollback = true;
					}
				}
			}
		} else {
			$course_id     = get_the_courses_id_by_group_id( $group_id );
			$course_status = learndash_user_get_course_progress( $user_id, $course_id[0] );
			if ( 'not_started' === $course_status['status'] ) {
				$seat_rollback = true;
			}
		}

		if ( $seat_rollback ) {

			$group_limit = get_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, true );
			if ( '' === $group_limit ) {
				$group_limit = 0;
			}

			++$group_limit;
			update_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, $group_limit );

			$user_data = get_user_by( 'id', $user_id );

			// Log the user removal.
			$mi_error_log                 = array();
			$mi_error_log['ACTION']       = 'User Removed & Seat Rollback.';
			$mi_error_log['Group Name']   = get_the_title( $group_id );
			$mi_error_log['Removed On']   = gmdate( 'Y-m-d' );
			$mi_error_log['User Details'] = array(
				'First name' => $user_data->first_name,
				'Last name'  => $user_data->last_name,
				'email'      => $user_data->user_email,
			);

			// fetch group leaders details.
			$user_query_args = array(
				'orderby'    => 'display_name',
				'order'      => 'ASC',
				'meta_query' => array( // phpcs:ignore
					array(
						'key'     => 'learndash_group_leaders_' . intval( $group_id ),
						'value'   => intval( $group_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			);
			$user_query      = new WP_User_Query( $user_query_args );
			if ( isset( $user_query->results ) ) {

				foreach ( $user_query->results as $key => $value ) {
					$leader_data = get_user_by( 'id', $value->ID );

					$mi_error_log['Group Leaders'][] = array(
						'First name' => $leader_data->first_name,
						'Last name'  => $leader_data->last_name,
						'email'      => $leader_data->user_email,
					);

				}
			}

			Mi_Error_Log::put_error_log( $mi_error_log, 'array', 'success' );
		}

	}

}
