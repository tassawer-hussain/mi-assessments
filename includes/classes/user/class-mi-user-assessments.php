<?php
/**
 * Class contains user level assessment related functions.
 *
 * @link       https://ministryinsights.com/
 * @since      1.7.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 *
 * Class to handle user level assessment functionality
 *
 * @since      1.7.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_User_Assessments {

	/**
	 * Contains link id
	 *
	 * @var string
	 */
	protected $link_id;

	/**
	 * Contains assessment id
	 *
	 * @var integer
	 */
	protected $assess_id;

	/**
	 * Current assessment details
	 *
	 * @var array
	 */
	protected $current_assess;

	/**
	 * Current user all assessment details
	 *
	 * @var array
	 */
	protected $old_assess;

	/**
	 * Contains report view id
	 *
	 * @var string
	 */
	protected $reportview_id;

	/**
	 * Contains group leader ID
	 *
	 * @var string
	 */
	protected $group_leader_id;

	/**
	 * Contains matched response arrays
	 *
	 * @var array
	 */
	protected $matched_arr;

	/**
	 * Contains status if we should use user level assessment or not
	 *
	 * @var boolean
	 */
	protected $leader_level_assess_status;

	/**
	 * Contains status if we should use user level assessment or not
	 *
	 * @var boolean
	 */
	protected $main_public_class;

	/**
	 * Contains user id.
	 *
	 * @var integer
	 */
	public $user_id;

	/**
	 * Contains group id
	 *
	 * @var integer
	 */
	public $group_id;

	/**
	 * Contains retake assessment status
	 *
	 * @var boolean
	 */
	public $assess_details;

	/**
	 * Contains assessment responses
	 *
	 * @var array
	 */
	public $assess_responses;

	/**
	 * Contains content ids
	 *
	 * @var array
	 */
	public $content_ids;

	/**
	 * Contains assessment version
	 *
	 * @var string
	 */
	public $assess_version;

	/**
	 * Contains assessment report meta data
	 *
	 * @var array
	 */
	public $report_data;

	/**
	 * Contains user email data
	 *
	 * @var array
	 */
	public $user_email_data;

	/**
	 * Contains all current user assessments
	 *
	 * @var array
	 */
	public $all_db_assess;

	/**
	 * Flag to check if send request using general level assessment
	 *
	 * @var boolean
	 */
	public $user_general_ass;

	/**
	 * Array contains report metadata
	 *
	 * @var array
	 */
	public $reoprt_api_data;

	/**
	 * Contains report id
	 *
	 * @var integer
	 */
	public $report_id;

	/**
	 * Contains report api check
	 *
	 * @var integer
	 */
	public $report_api_check;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since     1.7.0
	 * @param int $user_id User ID.
	 * @param int $link_id Link ID.
	 * @param int $assess_id assessment ID.
	 * @param int $version_assess Assessment version.
	 */
	public function __construct( $user_id, $link_id, $assess_id, $version_assess ) {
		$this->report_api_check              = 0;
		$this->assess_version                = $version_assess;
		$this->leader_level_assess_status    = false;
		$this->user_general_ass              = false;
		$this->match_ass_id                  = false;
		$this->content_ids                   = array();
		$this->reoprt_api_data['passwd']     = 'none';
		$this->reoprt_api_data['created_at'] = date( 'Y-m-d H:i:s' ); // phpcs:ignore
		$this->reoprt_api_data['updated_at'] = date( 'Y-m-d H:i:s' ); // phpcs:ignore
		$this->user_id                       = $user_id;
		$this->link_id                       = $link_id;
		$this->assess_id                     = $assess_id;

		// Create API class instance.
		$this->mi_api = new Mi_Assessments_API();

	}

	/**
	 * Function to handle if current user has already assessment instrument before but no leader.
	 *
	 * @since   1.7.0
	 */
	public function has_assessment_instrument_no_leader() {

		$this->user_general_ass = true;
		$this->report_api_check = 1;
		$this->report_data      = unserialize( get_post_meta( $this->assess_id, 'report_metadata', true ) ); // phpcs:ignore
		$this->reportview_id    = $this->report_data->id;

		$res_reinal = $this->tti_hit_api_with_leader_details();

		if ( $res_reinal ) {
			echo wp_json_encode( array( 'status' => '7' ) );
			exit;
		}
	}

	/**
	 * Function to check mapping limitations
	 *
	 * @since   1.7.0
	 */
	public function tti_hit_api_with_leader_details() {

		global $current_user, $wpdb;

		if ( $this->user_general_ass ) {
			$this->assess_details['api_key']              = get_post_meta( $this->assess_id, 'api_key', true );
			$this->assess_details['api_service_location'] = get_post_meta( $this->assess_id, 'api_service_location', true );
			$this->assess_details['link_id']              = get_post_meta( $this->assess_id, 'link_id', true );
			$this->assess_details['account_login']        = get_post_meta( $this->assess_id, 'account_login', true );
			$this->assess_details['survay_location']      = get_post_meta( $this->assess_id, 'survay_location', true );
			$this->assess_details['send_rep_group_lead']  = ( ! empty( get_post_meta( $this->assess_id, 'send_rep_group_lead', true ) ) ) ? get_post_meta( $this->assess_id, 'send_rep_group_lead', true ) : '';
		} else {
			$this->report_api_check = 3;
		}

		// create report with user assessment details and return report id.
		$this->report_id = $this->get_user_report_id( $current_user );

		if ( $this->report_id > 0 ) {

			$url = esc_url( $this->assess_details['api_service_location'] . '/api/v3/reports/' . $this->report_id );

			$api_response = json_decode( $this->mi_api->mi_send_api_request( $url, $this->assess_details['api_key'], 'GET' ) );

			$this->insert_assessment_data( $api_response, $this->report_id );

			return true;

		} else {
			return false;
		}

	}

	/**
	 * Function to create report and return report ID
	 *
	 * @since   1.7.0
	 *
	 * @param int $current_user User ID.
	 *
	 * @return boolean return false or report_id
	 */
	public function get_user_report_id( $current_user ) {

		$position_job = isset( $this->all_db_assess[0]->position_job ) && ! empty( $this->all_db_assess[0]->position_job ) ? $this->all_db_assess[0]->position_job : 'none';
		$company      = isset( $this->all_db_assess[0]->company ) && ! empty( $this->all_db_assess[0]->company ) ? $this->all_db_assess[0]->company : '';
		$gender       = isset( $this->all_db_assess[0]->gender ) && ! empty( $this->all_db_assess[0]->gender ) ? $this->all_db_assess[0]->gender : '';

		// API v3.0 url.
		$url = $this->assess_details['api_service_location'] . '/api/v3/reports?link_login=' . $this->assess_details['link_id'];

		$payload['respondent'] = array(
			'first_name'    => $current_user->user_firstname,
			'last_name'     => $current_user->user_lastname,
			'gender'        => $gender,
			'email'         => $current_user->user_email,
			'company'       => $company,
			'position_job'  => $position_job,
			'reportview_id' => $this->reportview_id,
			'responses'     => $this->assess_responses,
		);

		$response = $this->mi_api->mi_send_api_request( $url, $this->assess_details['api_key'], 'POST', $payload );
		$response = json_decode( $response );

		if ( $response && isset( $response->respondent->passwd ) ) {
			$this->reoprt_api_data['passwd'] = $response->respondent->passwd;
		}
		if ( $response && isset( $response->respondent->created_at ) ) {
			$this->reoprt_api_data['created_at'] = $response->respondent->created_at;
		}
		if ( $response && isset( $response->respondent->updated_at ) ) {
			$this->reoprt_api_data['updated_at'] = $response->respondent->updated_at;
		}

		// User data for email template.
		$this->user_email_data = array(
			'first_name'   => $current_user->user_firstname,
			'last_name'    => $current_user->user_lastname,
			'email'        => $current_user->user_email,
			'company'      => $company,
			'position_job' => $position_job,
			'link_id'      => $this->link_id,
			'gender'       => $gender,
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return $response->id;

	}

	/**
	 * Function to insert assessments data
	 *
	 * @since   1.7.0
	 *
	 * @param int $api_response API response body data.
	 * @param int $report_id User Report ID.
	 */
	public function insert_assessment_data( $api_response, $report_id ) {

		global $current_user, $wpdb;
		$assessment_table = $wpdb->prefix . 'assessments';

		$insert_query = $wpdb->insert( // phpcs:ignore
			$assessment_table,
			array(
				'user_id'           => $this->user_id,
				'first_name'        => $current_user->user_firstname,
				'last_name'         => $current_user->user_lastname,
				'email'             => $current_user->user_email,
				'service_location'  => $this->assess_details['api_service_location'],
				'account_id'        => $this->assess_details['account_login'],
				'link_id'           => $this->link_id,
				'api_token'         => $this->assess_details['api_key'],
				'gender'            => $this->user_email_data['gender'],
				'company'           => $this->user_email_data['company'],
				'status'            => 1,
				'version'           => $this->assess_version,
				'position_job'      => $this->user_email_data['position_job'],
				'password'          => $this->reoprt_api_data['passwd'],
				'report_id'         => (int) $report_id,
				'created_at'        => $this->reoprt_api_data['created_at'],
				'updated_at'        => $this->reoprt_api_data['updated_at'],
				'assess_type'       => $this->report_api_check,
				'assessment_result' => serialize( $api_response ), // phpcs:ignore
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// if assessment results successful inserted.
		if ( $insert_query ) {
			$this->tti_send_report_to_leader();
		}
	}

	/**
	 * Function to send report to leader
	 *
	 * @since   1.7.0
	 */
	public function tti_send_report_to_leader() {

		global $current_user;

		if ( isset( $this->assess_details['send_rep_group_lead'] ) && 'Yes' === $this->assess_details['send_rep_group_lead'] ) {

			if ( isset( $this->user_email_data['position_job'] ) && 'none' === $this->user_email_data['position_job'] ) {
				$this->user_email_data['position_job'] = '';
			}

			initiate_group_leader_email_process(
				$this->report_id,
				$this->assess_details['api_key'],
				$this->assess_details['api_service_location'],
				$this->user_id,
				$this->user_email_data,
				$this->assess_id
			);
		}
	}

	/***********************************************
	 * USER LEVEL RETAKE ASSESSMENT PROCESS - START.
	 * ********************************************/

	/**
	 * Function to handle user level retake assessments
	 *
	 * @since   1.7.0
	 */
	public function start_user_level_retake_assess_process() {

		$user_leader_status = $this->if_user_has_group_leader();

		// if user has Group Leader with a capability to override.
		if ( $user_leader_status ) {

			return $this->tti_return_assessments_curr_user();

		}

		return false;
	}


	/**
	 * Function to get current user leaders
	 *
	 * @since   1.7.0
	 *
	 * @return boolean|array
	 */
	public function if_user_has_group_leader() {

		global $wpdb, $current_user;

		$group_ids = $this->get_group_ids();

		foreach ( $group_ids as $key => $group_id ) {

			$this->get_contents_post_id_by_group_id( $group_id->group_ids );

			if ( false !== $this->match_ass_id ) {
				$this->group_id = $group_id->group_ids;
				break;
			}
		}

		// if group doesn't belongs to current assessment.
		// if current user has any group ids, group id must contain any group leader.
		if ( isset( $this->group_id ) ) {

			$key = 'learndash_group_leaders_' . esc_sql( $this->group_id );

			$group_leader_id = $wpdb->get_col( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					'SELECT `user_id` FROM %i WHERE meta_key = %s', // phpcs:ignore
					$wpdb->usermeta,
					$key
				)
			);

			$time_ass_assign = 0;

			if ( ! empty( $group_leader_id ) && 1 === count( $group_leader_id ) ) {

				// if there is only one group leader.
				$this->group_leader_id = $group_leader_id[0];

				// get current user group leader ids.
				$group_leader_ids = array_unique( $group_leader_id );

			} elseif ( ! empty( $group_leader_id ) && count( $group_leader_id ) > 1 ) {

				// get the group leader with the latest assigned time.
				foreach ( $group_leader_id as $key => $leader_id ) {
					$time_ass_assign_val = get_user_meta( $this->user_id, 'assigned_group_' . $this->group_id . '_' . $leader_id . '_' . $this->assess_id, true );

					// assign with the latest time.
					if ( $time_ass_assign_val > $time_ass_assign ) {
						$time_ass_assign       = $time_ass_assign_val;
						$this->group_leader_id = $leader_id;
					}
				}

				if ( 0 === $time_ass_assign ) {
					$this->group_leader_id = $group_leader_id[0];

					// get current user group leader ids.
					$group_leader_ids = array_unique( $group_leader_id );
				}
			} else {
				// if no group leader found.
				return false;
			}

			set_transient( 'assessmentListenerGroupLeaders' . $current_user->ID, $this->group_leader_id, DAY_IN_SECONDS );

			if ( isset( $this->group_leader_id ) ) {

				// if group leader user has the capability.
				return $this->check_leader_has_capability( $this->group_leader_id );

			} else {

				// check all group leaders, return the first who has the capability.
				return $this->check_any_leader_has_capability( $group_leader_ids );

			}
		}
		return false;
	}

	/**
	 * Function to get group ID's related to current logged in user
	 *
	 * @since   1.7.0
	 */
	public function get_group_ids() {

		global $wpdb;

		$assess_title = get_the_title( $this->assess_id );

		$group_ids = array();

		if ( ! empty( $this->user_id ) ) {

			// phpcs:ignore
			$sql_str = $wpdb->prepare( 'SELECT usermeta.meta_value as group_ids FROM ' . $wpdb->usermeta . ' as usermeta INNER JOIN ' . $wpdb->posts . " as posts ON posts.ID = usermeta.meta_value WHERE user_id = %d  AND meta_key LIKE 'learndash_group_users_%' AND (posts.post_status = 'publish' OR posts.post_status = 'draft') ORDER BY posts.post_date DESC", $this->user_id );

			// phpcs:ignore
			$group_id = $wpdb->get_results( $sql_str );

		}

		if ( ! empty( $group_id ) ) {
			return $group_id;
		}

		return false;

	}

	/**
	 * Get contents post IDs by group id.
	 *
	 * @param integer $group_id Group id.
	 * @since   1.7.0
	 */
	public function get_contents_post_id_by_group_id( $group_id ) {

		global $wpdb;
		$final_results = array();
		$key           = 'ld_course_' . $c_id;

		// get all courses realted to this group.
		$courses = get_the_courses_id_by_group_id( $group_id );

		if ( count( $courses ) > 0 ) {

			foreach ( $courses as $course ) {
				$this->content_ids = array_merge( $this->content_ids, get_the_whole_course_contents_id_by_course_id( $course ) );
			}

			// [take_assessment] shortcode can be in main course post or in the course content. So,
			$this->content_ids = array_merge( $this->content_ids, $courses );

			if ( count( $this->content_ids ) > 0 ) {

				// remove duplicate id if any exist.
				$this->content_ids = array_unique( $this->content_ids );

				foreach ( $this->content_ids as $key => $content_id ) {
					$this->get_links_id_by_content_id( $content_id );
				}
			}
		}
	}

	/**
	 * Get post content.
	 *
	 * @since   1.7.0
	 * @param integer $content_id Course content id.
	 */
	public function get_links_id_by_content_id( $content_id ) {

		$content_post = get_post( $content_id );

		if ( isset( $content_post->post_content ) ) {

			$content = wpautop( $content_post->post_content );

			if ( false === $this->match_ass_id ) {
				$this->match_all_assessment_ids( $content );
			}
		}
	}

	/**
	 * Check if assessment shortcode exists in the content.
	 *
	 * @since   1.7.0
	 * @param integer $content Course content.
	 */
	public function match_all_assessment_ids( $content ) {

		$searc_string = '[take_assessment assess_id="' . $this->assess_id . '"';

		if ( strpos( $content, $searc_string ) !== false ) {
			$this->match_ass_id = true;
		}
	}

	/**
	 * Function to check if given group leader has the capability
	 *
	 * @since   1.7.0
	 * @param integer $leader_id Group Leader ID.
	 *
	 * @return boolean
	 */
	public function check_leader_has_capability( $leader_id ) {

		$user_settings = get_user_meta( $leader_id, 'user_assessment_settings', true );

		if ( ! empty( $user_settings ) ) {

			$user_settings = unserialize( $user_settings ); // phpcs:ignore

			if ( isset( $user_settings['user_capa'] ) && 'Yes' === $user_settings['user_capa'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Function to check if any group leaders has the capability
	 *
	 * @since   1.7.0
	 * @param array $group_leader_ids Array of Group Leaders ID.
	 *
	 * @return boolean
	 */
	public function check_any_leader_has_capability( $group_leader_ids ) {

		foreach ( $group_leader_ids as $key => $leader_id ) {

			$user_settings = get_user_meta( $leader_id, 'user_assessment_settings', true );

			if ( ! empty( $user_settings ) ) {

				$user_settings = unserialize( $user_settings ); // phpcs:ignore

				if ( isset( $user_settings['user_capa'] ) && 'Yes' === $user_settings['user_capa'] ) {

					$this->group_leader_id = $leader_id;
					return true;

				}
			}
		}
		return false;
	}

	/**
	 * Function to map user and site level assessment
	 *
	 * @since   1.7.0
	 */
	public function tti_return_assessments_curr_user() {

		// user level assessment details.
		$data = unserialize( get_user_meta( $this->group_leader_id, 'user_assessment_data', true ) ); // phpcs:ignore

		$this->report_data = unserialize( get_post_meta( $this->assess_id, 'report_metadata', true ) ); // phpcs:ignore

		if ( count( $data ) >= 1 ) {

			foreach ( $data as $key => $value ) {

				if ( $value['report_view_id'] == $this->report_data->id ) { // phpcs:ignore

					// set report view id.
					$this->reportview_id = $this->report_data->id;
					return $value;

				}
			}
		}
		return false;
	}

	/***********************************************
	 * USER LEVEL RETAKE ASSESSMENT PROCESS - END.
	 * ******************************************* */

	/***********************************************
	 * USER LEVEL ASSESSMENT PROCESS - START.
	 * ******************************************* */

	/**
	 * Function to handle user level assessments
	 *
	 * @since   1.7.0
	 * @return void
	 */
	public function start_user_level_assess_process() {

		// New Logic.
		$curre_report_data = $this->check_current_report_meta();

		$this->check_old_report_meta();

		$mapping_status = $this->check_mapping_status();

		// if mapping has the current assessment instrument.
		if ( $curre_report_data ) {

			$this->check_response_tag_current_assessment_data();

			$user_leader_status = $this->if_user_has_group_leader();

			if ( $this->leader_level_assess_status && $mapping_status ) {

				// if user has Group Leader with a capability to override.
				if ( $user_leader_status ) {

					// get group leader assessment details.
					$this->assess_details = $this->tti_return_assessments_curr_user();

					// if Group Leader has an override with current assessment.
					if ( $this->assess_details ) {

						$res_reinal = $this->tti_hit_api_with_leader_details();

						if ( $res_reinal ) {
							echo wp_json_encode( array( 'status' => '7' ) );
							exit;
						}
					} else {
						// if no leader details.
						$this->has_assessment_instrument_no_leader();
					}
				} else {

					// if no leader details.
					$this->has_assessment_instrument_no_leader();

				}
			}
		}

	}

	/**
	 * Function to get current assessment metadata
	 *
	 * @since   1.7.0
	 */
	public function check_current_report_meta() {

		$report_data = unserialize( get_post_meta( $this->assess_id, 'report_metadata', true ) ); // phpcs:ignore

		if ( isset( $report_data ) ) {

			foreach ( $report_data->assessment as $key => $value ) {

				if ( 'instruments' === $key ) {

					// instruments loop.
					foreach ( $value as $innerkey => $innervalue ) {
						$this->current_assess[ $innervalue->id ] = $this->get_word_first_charac( $innervalue->name );
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Function to get words first characters
	 *
	 * @since   1.7.0
	 * @param string $word Instruments name.
	 *
	 * @return string
	 */
	public function get_word_first_charac( $word ) {

		$words   = explode( ' ', $word );
		$acronym = '';

		if ( 2 === count( $words ) ) {
			$counter = 0;

			foreach ( $words as $w ) {

				if ( 2 === $counter ) {
					break;
				}

				$acronym .= $w[0];
				$counter++;

			}
		} elseif ( isset( $words[2] ) ) {
			$acronym = trim( $words[2] );
		}

		return $acronym;
	}

	/**
	 * Function to get old assessments metadata
	 *
	 * @since   1.7.0
	 */
	public function check_old_report_meta() {

		global $wpdb;
		$assessment_table = $wpdb->prefix . 'assessments';

		$assessments = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				'SELECT version, link_id, position_job, gender, company, created_at, assessment_result FROM %i WHERE user_id = %d ORDER BY version DESC', // phpcs:ignore
				$assessment_table,
				$this->user_id
			)
		);

		$this->all_db_assess = $assessments;

		if ( ! empty( $assessments ) ) {

			foreach ( $assessments as $key => $value ) {
				$this->check_assessment_response( unserialize( $value->assessment_result ), $value->created_at ); // phpcs:ignore
			}

			// descending order the array.
			krsort( $this->old_assess );
		}
	}

	/**
	 * Function to get old assessments metadata
	 *
	 * @since   1.7.0
	 *
	 * @param array  $data Assessment result.
	 * @param string $created_at Assessment created date.
	 */
	public function check_assessment_response( $data, $created_at ) {

		if ( isset( $data->report->info->responses ) ) {

			$date_check = $this->check_less_than_six_month( $created_at );

			if ( $date_check ) {
				$this->old_assess[ $created_at ][] = $data->report->info->responses;
			}
		}
	}

	/**
	 * Function to check if given date is less than 6 month old.
	 *
	 * @since   1.7.0
	 * @param string $given_date Assessment created date.
	 *
	 * @return boolean
	 */
	public function check_less_than_six_month( $given_date ) {

		$data_given = explode( 'T', $given_date );

		return ( strtotime( $data_given[0] ) < strtotime( '6 month ago' ) ) ? false : true;
	}

	/**
	 * Function to check mapping limitations
	 *
	 * @since   1.7.0
	 *
	 * @return boolean
	 */
	public function check_mapping_status() {

		$mapping_data = get_option( 'tti_platform_mapping_data' );

		if (
			isset( $this->current_assess ) &&
			! empty( $this->current_assess ) &&
			isset( $mapping_data ) &&
			! empty( $mapping_data )
		) {

			$counter_loop = count( $mapping_data['response_id'] );

			for ( $i = 0; $i < $counter_loop; $i++ ) {

				if (
					array_key_exists( $mapping_data['instrument_id'][ $i ], $this->current_assess ) &&
					in_array( $mapping_data['response_id'][ $i ], $this->current_assess ) // phpcs:ignore
				) {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * Function to find the current assessment instrument in old assessment
	 *
	 * @since   1.7.0
	 */
	public function check_response_tag_current_assessment_data() {

		$date_check = false;

		foreach ( $this->old_assess as $key => $value ) {
			$this->compare_reponse_tags( $value );
		}
	}

	/**
	 * Function to compare the response tag or instrumnet id
	 *
	 * @since   1.7.0
	 * @param array $data Assessment result.
	 */
	public function compare_reponse_tags( $data ) {

		foreach ( $this->current_assess as $key => $value ) {

			$yes_exists = $this->multi_key_exists( $value );

			if ( $yes_exists ) {
				$this->matched_arr[ $key ]        = $value;
				$this->leader_level_assess_status = true;
			}
		}

	}

	/**
	 * Function to check key in multidimensional array
	 *
	 * @since   1.7.0
	 * @param array $key_search Assessment result.
	 *
	 * @return boolean
	 */
	public function multi_key_exists( $key_search ) {

		foreach ( $this->old_assess as $key => $value ) {

			foreach ( $value as $ikey => $ivalue ) {

				if ( array_key_exists( $key_search, $ivalue ) ) {

					$this->assess_responses[ $key_search ] = $ivalue->{$key_search};
					return true;

				}
			}
		}
	}

	/***********************************************
	 * USER LEVEL ASSESSMENT PROCESS - END.
	 * ******************************************* */
}
