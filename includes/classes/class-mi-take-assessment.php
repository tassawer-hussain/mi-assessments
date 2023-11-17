<?php
/**
 * Manage Take Assessment Functionality.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Take Assessment.
 *
 * This class is used to define take assessment and related functionality.
 *
 * @since      1.7.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Take_Assessment {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		// Create API class instance.
		$this->mi_api = new Mi_Assessments_API();

		// Ajax hook initialization to Take Assessment Process.
		add_action( 'wp_ajax_take_assessment', array( $this, 'mi_process_take_assessment' ) );
		add_action( 'wp_ajax_nopriv_take_assessment', array( $this, 'mi_process_take_assessment' ) );

		// Ajax Hook Initialization to Save Selected Feedback.
		add_action( 'wp_ajax_insertIsSelectedData', array( $this, 'mi_assessment_insert_is_selected_data' ) );
		add_action( 'wp_ajax_nopriv_insertIsSelectedData', array( $this, 'mi_assessment_insert_is_selected_data' ) );

		// Process download assessment link for user.
		$this->mi_assessment_pdf_download_button();

	}

	/**
	 * Function to start take Assessment process.
	 *
	 * @since    1.0.0
	 */
	public function mi_process_take_assessment() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_public_nonce', 'nonce' );

		global $current_user, $wpdb;

		$version_assess   = 1;
		$report_api_check = 0;

		$retake_status        = isset( $_POST['retake_status'] ) ? sanitize_text_field( wp_unslash( $_POST['retake_status'] ) ) : '';
		$assessment_id        = isset( $_POST['assessment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['assessment_id'] ) ) : '';
		$assessment_permalink = isset( $_POST['assessment_permalink'] ) ? sanitize_text_field( wp_unslash( $_POST['assessment_permalink'] ) ) : '';
		$assessment_locked    = isset( $_POST['assessment_locked'] ) ? sanitize_text_field( wp_unslash( $_POST['assessment_locked'] ) ) : '';

		wp_get_current_user();

		$user_data = array(
			'user_ID'        => $current_user->ID,
			'user_firstname' => $current_user->user_firstname,
			'user_lastname'  => $current_user->user_lastname,
			'user_login'     => $current_user->user_login,
			'user_email'     => $current_user->user_email,
		);

		$result_retake    = false;
		$access_token     = get_post_meta( $assessment_id, 'api_key', true );
		$account_id       = get_post_meta( $assessment_id, 'account_login', true );
		$service_location = get_post_meta( $assessment_id, 'api_service_location', true );
		$survay_location  = get_post_meta( $assessment_id, 'survay_location', true );
		$link_id          = get_post_meta( $assessment_id, 'link_id', true );

		// log the event details.
		$log_details = array(
			'ACTION'        => 'Start Takes Assessment.',
			'Locked Status' => $assessment_locked,
			'Link ID'       => $link_id,
			'User Details:' => $user_data,
		);

		// If assessment opens.
		if ( 'false' === $assessment_locked ) {

			set_transient( 'assessmentListener' . $current_user->ID, $assessment_permalink, DAY_IN_SECONDS );

			Mi_Error_Log::put_error_log( $log_details, 'array', 'success' );

			echo wp_json_encode(
				array(
					'survey_location' => $survay_location,
					'onsite_survey'   => get_site_url() . '/take-assessment-on-site/',
					'link_id'         => $link_id,
					'status'          => '0',
				)
			);
			exit;
		}

		set_transient( 'assessmentListener' . $current_user->ID, $assessment_permalink, DAY_IN_SECONDS );

		$assessment_table = $wpdb->prefix . 'assessments';

		if (
			isset( $user_data['user_firstname'] ) &&
			! empty( $user_data['user_firstname'] ) &&
			isset( $user_data['user_lastname'] ) &&
			! empty( $user_data['user_lastname'] )
			) {

			// Query to count already completed assessment count. refturn false if no record exist.
			$completed_assess = mi_get_completed_assessment_counts_by_user( $current_user->ID, $link_id );
			if ( $completed_assess ) {
				$version_assess = ++$completed_assess;
			}

			// Check user level assessment checks.
			$result_retake = $this->mi_handle_user_level_assessment( $current_user->ID, $link_id, $assessment_id, $retake_status, $version_assess );

			// NOTE: Code will exit if user level assessment condition applies.
			set_transient( 'assessmentListenerRetakeAsseStatus' . $current_user->ID, 'false', DAY_IN_SECONDS );

			$orig_link_id = $link_id;

			if ( false !== $result_retake ) {

				$access_token     = $result_retake['api_key'];
				$account_id       = $result_retake['account_login'];
				$service_location = $result_retake['api_service_location'];
				$survay_location  = $result_retake['survey_location'];
				$link_id          = $result_retake['link_id'];
				$report_api_check = 2;

				set_transient( 'assessmentListenerRetakeAsseStatus' . $current_user->ID, 'true', DAY_IN_SECONDS );

			}

			set_transient( 'assessmentListenerRetakeAsseLink' . $current_user->ID, $orig_link_id, DAY_IN_SECONDS );

			// Query to get assessment detail.
			$results = $wpdb->get_results( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					'SELECT * FROM %i WHERE user_id = %s AND link_id = %s AND status = 0', // phpcs:ignore
					$assessment_table,
					$current_user->ID,
					$orig_link_id
				)
			);

			$results = reset( $results );

			if (
				$wpdb->num_rows > 0 &&
				(
					isset( $results->email ) &&
					! empty( $results->email ) &&
					isset( $results->password ) &&
					! empty( $results->password ) &&
					isset( $results->first_name ) &&
					! empty( $results->first_name ) &&
					isset( $results->last_name ) &&
					! empty( $results->last_name )
				)
			) {

				$log_details['status']   = 'Assessment link exist. Redirecting to assessment successfully.';
				$log_details['Password'] = $results->password;
				$log_details['log_type'] = 'success';

				echo wp_json_encode(
					array(
						'survey_location' => $survay_location,
						'onsite_survey'   => get_site_url() . '/take-assessment-on-site/',
						'link_id'         => $link_id,
						'password'        => $results->password,
						'email'           => $results->email,
						'user_id'         => $current_user->ID,
						'status'          => '2',
					)
				);

			} else {

				/* API v3.0 url */
				if ( false !== $result_retake ) {
					$url = $service_location . '/api/v3/respondents?link_login=' . $link_id;
				} else {
					$url = $service_location . '/api/v3/respondents?link_login=' . $orig_link_id;
				}

				$log_details['status']  = 'Assessment link not exist. Start creating assessment link....';
				$log_details['API URL'] = 'API URL hit to create assessment link: ' . $url;

				$payload = array(
					'first_name'   => $user_data['user_firstname'],
					'last_name'    => $user_data['user_lastname'],
					'gender'       => 'M',
					'email'        => $user_data['user_email'],
					'company'      => '',
					'position_job' => '',
				);

				$response = $this->mi_api->mi_send_api_request( $url, $access_token, 'POST', $payload );
				$response = json_decode( $response );

				if ( is_wp_error( $response ) ) {

					$error_message = $response->get_error_message();

					$log_details['Error Message'] = $error_message;
					$log_details['log_type']      = 'error';

					echo wp_json_encode(
						array(
							'error'  => $error_message,
							'status' => '1',
						)
					);

				} elseif ( isset( $response->passwd ) && ! empty( $response->passwd ) && null !== $response->passwd ) {

					$response_user_id      = $user_data['user_ID'];
					$response_first_name   = $response->first_name;
					$response_last_name    = $response->last_name;
					$response_email        = $response->email;
					$response_password     = $response->passwd;
					$response_company      = $response->company;
					$response_gender       = $response->gender;
					$response_position_job = $response->position_job;
					$response_created_at   = $response->created_at;
					$response_updated_at   = $response->updated_at;
					$response_status       = $response->resp_status;

					$log_details['Password'] = $response_password;

					if ( $results ) {

						$log_details['Query Type'] = 'Updating existing assessment link record.';

						// phpcs:ignore
						$query_result = $wpdb->update(
							$assessment_table,
							array(
								'user_id'          => $response_user_id,
								'first_name'       => $response_first_name,
								'last_name'        => $response_last_name,
								'email'            => $response_email,
								'service_location' => $service_location,
								'account_id'       => $account_id,
								'link_id'          => $orig_link_id,
								'api_token'        => $access_token,
								'gender'           => $response_gender,
								'company'          => $response_company,
								'status'           => $response_status,
								'version'          => $version_assess,
								'position_job'     => $response_position_job,
								'password'         => $response_password,
								'created_at'       => $response_created_at,
								'assess_type'      => $report_api_check,
								'updated_at'       => $response_updated_at,
							),
							array(
								'link_id' => $orig_link_id,
								'user_id' => $current_user->ID,
								'status'  => 0,
							),
							array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
						);

					} else {

						$log_details['Query Type'] = 'Inserting assessment link record.';

						// phpcs:ignore
						$query_result = $wpdb->insert(
							$assessment_table,
							array(
								'user_id'          => $response_user_id,
								'first_name'       => $response_first_name,
								'last_name'        => $response_last_name,
								'email'            => $response_email,
								'service_location' => $service_location,
								'account_id'       => $account_id,
								'link_id'          => $orig_link_id,
								'api_token'        => $access_token,
								'gender'           => $response_gender,
								'company'          => $response_company,
								'status'           => $response_status,
								'version'          => $version_assess,
								'position_job'     => $response_position_job,
								'password'         => $response_password,
								'created_at'       => $response_created_at,
								'assess_type'      => $report_api_check,
								'updated_at'       => $response_updated_at,
							),
							array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
						);
					}

					$log_details['Query Result'] = $query_result;

					if ( $query_result ) {

						echo wp_json_encode(
							array(
								'survey_location' => $survay_location,
								'onsite_survey'   => get_site_url() . '/take-assessment-on-site/',
								'link_id'         => $link_id,
								'password'        => $response_password,
								'email'           => $response_email,
								'user_id'         => $response_user_id,
								'status'          => '3',
							)
						);

					} else {

						echo wp_json_encode(
							array(
								'status'        => '4',
								'url'           => $response,
								'onsite_survey' => get_site_url() . '/take-assessment-on-site/',
							)
						);

					}

					$log_details['End Status'] = 'End creating assessment link. Redirecting to assessment successfully';
					$log_details['log_type']   = 'success';

				} else {

					$log_details['End Status'] = 'No Response From API';
					$log_details['log_type']   = 'error';
					echo wp_json_encode( array( 'status' => '6' ) );

				}
			}
		} else {

			$log_details['End Status'] = 'First Name and Last Name not exists';
			$log_details['log_type']   = 'error';

			echo wp_json_encode(
				array(
					'status'        => '5',
					'onsite_survey' => get_site_url() . '/take-assessment-on-site/',
				)
			);
		}

		Mi_Error_Log::put_error_log( $log_details, 'array', $log_details['log_type'] );

		wp_die();
	}

	/**
	 * Function to save selected feedback.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function mi_assessment_insert_is_selected_data() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_public_nonce', 'nonce' );

		global $current_user, $wpdb;
		wp_get_current_user();

		$is_selected = isset( $_POST['isSelected'] ) ? sanitize_text_field( wp_unslash( $_POST['isSelected'] ) ) : '';
		$link_id     = isset( $_POST['link_id'] ) ? sanitize_text_field( wp_unslash( $_POST['link_id'] ) ) : '';

		$user_id = $current_user->ID;

		$assessment_table = $wpdb->prefix . 'assessments';

		// Get assessment version.
		$asses_version = get_current_user_assess_version( $user_id, $link_id );

		// Get latest completed assessment results.
		$columns = 'selected_all_that_apply';
		$results = get_user_latest_completed_assessment_result( $user_id, $link_id, $asses_version, $columns );

		$array    = unserialize( $results->selected_all_that_apply ); // phpcs:ignore
		$find_val = $is_selected['type'];

		foreach ( $array as $key => $value ) {
			if ( $value['type'] == $find_val ) { // phpcs:ignore
				unset( $array[ $key ] );
				$array = array_values( $array );
			} else {
				$array = $array;
			}
		}

		$array[] = $is_selected;

		if ( isset( $is_selected ) && ! empty( $is_selected ) ) {

			$update_query = $wpdb->update( // phpcs:ignore
				$assessment_table,
				array(
					'selected_all_that_apply' => serialize( $array ), // phpcs:ignore
				),
				array(
					'user_id' => $user_id,
					'link_id' => $link_id,
					'version' => $asses_version,
				)
			);

			if ( false === $update_query ) {

				$err     = __( 'There is somthing wrong.', 'tti-platform' );
				$message = '<p class="error">' . esc_html( $err ) . '</p>';
				echo wp_json_encode(
					array(
						'message' => $message,
						'status'  => '0',
					)
				);

			} else {

				$err     = __( 'Your selections have been saved.', 'tti-platform' );
				$message = '<p class="success">' . esc_html( $err ) . '</p>';
				echo wp_json_encode(
					array(
						'message' => $message,
						'status'  => '1',
						'user'    => $user_id,
					)
				);

			}
		}
		exit;
	}

	/**
	 * Function to handle download PDF report.
	 *
	 * @since   1.0.0
	 */
	public function mi_assessment_pdf_download_button() {

		global $wpdb, $current_usr;

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		if ( isset( $_get_data['assessment_id'] ) && ! empty( $_get_data['assessment_id'] ) ) {

			// Get assessment post ID.
			$assess_id = sanitize_text_field( $_get_data['assessment_id'] );
			$post      = get_post( $assess_id );
			$password  = 'false';

			// Grab assessment title and assessment post meta.
			$slug      = sanitize_title_with_dashes( $post->post_title );
			$post_meta = get_post_custom( $assess_id );

			// Grab assessment API details.
			$link_id              = $post_meta['link_id']['0'];
			$account_login        = $post_meta['account_login']['0'];
			$api_service_location = $post_meta['api_service_location']['0'];
			$api_key              = $post_meta['api_key']['0'];

			// user is logged in. Grab user details.
			if ( is_user_logged_in() ) {

				$current_usr      = wp_get_current_user();
				$u_first_name     = $current_usr->user_firstname;
				$u_last_name      = $current_usr->user_lastname;
				$current_user     = $current_usr->ID;
				$assessment_table = $wpdb->prefix . 'assessments';

				// If opened assessment respondent download request.
				if (
					isset( $_get_data['opened_assessment'] ) &&
					! empty( $_get_data['opened_assessment'] ) &&
					'true' === $_get_data['opened_assessment']
				) {

					$password     = sanitize_text_field( $_get_data['respondent_passwd'] );
					$u_first_name = sanitize_text_field( $_get_data['f_name'] );
					$u_last_name  = sanitize_text_field( $_get_data['l_name'] );
					$version      = sanitize_text_field( $_get_data['version'] );

					$tablename = $wpdb->prefix . 'list_users_assessments';

					// Execute the query to get results.
					$results = $wpdb->get_results( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							'SELECT report_id, service_location, api_token FROM %i WHERE user_id = %s AND version = %s AND password = %s', // phpcs:ignore
							$assessment_table,
							$current_user,
							$version,
							$password
						),
						OBJECT
					);

					$report_id = $results[0]->report_id;

				} elseif (
					(
						isset( $_get_data['cp_page'] ) &&
						! empty( $_get_data['cp_page'] ) &&
						'true' === $_get_data['cp_page'] &&
						( 'close' === $_get_data['assessment_type'] || ! isset( $_get_data['assessment_type'] ) )
					) ||
					(
						isset( $_get_data['assessment_type'] ) &&
						! empty( $_get_data['assessment_type'] ) &&
						'open' === $_get_data['assessment_type']
					)
				) {
					/* Completed profile if section */
					$u_id    = sanitize_text_field( $_get_data['user_id'] );
					$version = sanitize_text_field( $_get_data['version'] );

					// Execute the query to get results.
					$results = $wpdb->get_row( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							// phpcs:ignore
							'SELECT report_id, password, first_name, last_name, service_location, api_token FROM %i
							WHERE user_id = %s
							AND link_id = %s
							AND status = 1
							AND version = %s',
							$assessment_table,
							$u_id,
							$link_id,
							$version
						)
					);

					$report_id    = $results->report_id;
					$u_first_name = $results->first_name;
					$u_last_name  = $results->last_name;

				} elseif ( ! isset( $_get_data['tti_print_consolidation_report'] ) ) {

					if ( isset( $_get_data['user_id'] ) ) {
						$current_user = $_get_data['user_id'];
					}

					$version = sanitize_text_field( $_get_data['version'] );

					// Execute the query to get results.
					$results = $wpdb->get_row( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							// phpcs:ignore
							'SELECT report_id, password, first_name, last_name, service_location, api_token FROM %i
							WHERE user_id = %s
							AND link_id = %s
							AND status = 1
							AND version = %s',
							$assessment_table,
							$current_user,
							$link_id,
							$version
						)
					);

					$report_id    = $results->report_id;
					$password     = $results->password;
					$u_first_name = $results->first_name;
					$u_last_name  = $results->last_name;
				}

				$api_service_location = $results->service_location;
				$api_key              = $results->api_token;

				// API v3.0 url.
				$url = $api_service_location . '/api/v3/reports/' . $report_id . '.pdf';

				$data = $this->mi_api->mi_download_pdf_report( $url, $api_key, 'GET' );

				$path = $u_first_name . '-' . $u_last_name . '-' . $slug . '.pdf';
				file_put_contents( $path, $data ); // phpcs:ignore
				$content = file_get_contents( $path ); // phpcs:ignore
				header( 'Content-Type: application/pdf' );
				header( 'Content-Length: ' . strlen( $content ) );
				header( 'Content-Disposition: attachment; filename="' . $u_first_name . '-' . $u_last_name . '-' . $slug . '.pdf"' );
				header( 'Cache-Control: private, max-age=0, must-revalidate' );
				header( 'Pragma: public' );
				ini_set( 'zlib.output_compression', '0' ); // phpcs:ignore
				ob_get_clean();
				unlink( $path );
				die( $content ); // phpcs:ignore
			}
		}
	}

	/**
	 * Handle user level assessment process.
	 *
	 * @since    1.7.0
	 *
	 * @param int     $user_id User ID.
	 * @param string  $link_id Link ID.
	 * @param int     $assess_id assessment ID.
	 * @param boolean $retake_status assessment ID.
	 * @param int     $version_assess Assessment version.
	 */
	public function mi_handle_user_level_assessment( $user_id, $link_id, $assess_id, $retake_status, $version_assess ) {

		// Include Take assessment class.
		require_once MI_INCLUDES_PATH . 'classes/user/class-mi-user-assessments.php';
		$user_assessment = new Mi_User_Assessments( $user_id, $link_id, $assess_id, $version_assess );

		if ( 'true' === $retake_status ) {

			// Retake assessment function.
			return $user_assessment->start_user_level_retake_assess_process();

		} else {

			// Take assessment function.
			$user_assessment->start_user_level_assess_process();

		}
	}

}
