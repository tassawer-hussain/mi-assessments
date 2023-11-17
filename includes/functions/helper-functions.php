<?php
/**
 * Helper functions used to debug while development.0
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Function to generate random string given by length.
 *
 * @since   2.0.0
 * @param string $length Length of the string (Default is 30).
 * @return string returns Generated key according to length given.
 */
function generate_random_string( $length = 30 ) {

	$characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );
	$random_string     = '';

	for ( $i = 0; $i < $length; $i++ ) {
		$random_string .= $characters[ rand( 0, $characters_length - 1 ) ]; // phpcs:ignore
	}

	return $random_string;
}

/**
 * Function to fetch all the assessments and cache it for better performance.
 *
 * @since   2.0.0
 * @return array
 */
function fetched_all_mi_assessments_post_type() {

	// get transient.
	$mi_assessments = get_transient( 'mi_assessments_post_type' );

	// check if transient exists.
	if ( false === $mi_assessments ) {

		// define query parameters.
		$args = array(
			'post_type'      => 'tti_assessments',
			'posts_per_page' => -1,
		);

		// query the posts.
		$mi_assessments = new WP_Query( $args );

		// set transient to cache results for 24 hours.
		set_transient( 'mi_assessments_post_type', $mi_assessments, 24 * HOUR_IN_SECONDS );

	}

	return $mi_assessments;
}

/**
 * Format the date (10/04/2019 07:45 PM)
 *
 * @since   1.0.0
 * @param string $date Data string.
 *
 * @return void
 */
function format_the_date( $date ) {

	// Format and display the date based on the provided $date variable, escaping the output for security.
	echo esc_html( date_i18n( 'm/d/Y h:i A', strtotime( $date ) ) );

}

/**
 * Create URL PDF download.
 *
 * @since   1.0.0
 * @param string $link_id Assessment link ID.
 *
 * @return stirng
 */
function get_assessment_post_id_by_link_id( $link_id ) {

	global $wpdb;
	$assessment_id = 0;

	// Attempt to retrieve the assessment ID from the cache.
	$assessment_id = wp_cache_get( 'assessment_id_' . $link_id, 'mi_assessment_cache_group' );

	// If not found in the cache, query the database.
	if ( false === $assessment_id ) {
		$results = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key ='link_id' AND meta_value = %s",
				$link_id
			)
		);

		if ( $results ) {
			$assessment_id = $results->post_id;

			// Cache the result for future use.
			wp_cache_set( 'assessment_id_' . $link_id, $assessment_id, 'mi_assessment_cache_group', 3600 );
		}
	}

	return $assessment_id;

}

/**
 * Check if the user has completed an assessment by passing user_id and link_id.
 *
 * @param int    $user_id The user ID to check.
 * @param string $link_id The link ID to check.
 *
 * @return bool Returns true if the user has completed the assessment, false otherwise.
 */
function check_is_user_completed_assessment( $user_id, $link_id ) {

	global $wpdb;
	$db_table_name = $wpdb->prefix . 'assessments';

	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT * FROM %i WHERE user_id = %s AND link_id = %s', // phpcs:ignore
			$db_table_name,
			$user_id,
			$link_id
		)
	);

	return isset( $results ) ? true : false;
}

/**
 * Check if the user has logged in at least once.
 *
 * @param int $user_id The user ID to check.
 *
 * @return bool Returns true if the user has logged in at least once, false otherwise.
 */
function check_user_last_login_isset( $user_id ) {

	$user_last_login = get_user_meta( $user_id, 'last_login', true );

	if ( empty( $user_last_login ) ) {
		return false; // User has not logged in.
	}

	return true; // User has logged in at least once.
}

/**
 * STEPS TO FIND OUT THE LINK IDs.
 * 1 - First find the courses using Group ID
 * 2 - Find all the contents post_ids within a course.
 * 3 - Get the content of each course content and findout the take_assessment shortcode inside it.
 * 4 - If found, return the current assessment post id.
 * 5 - From assessment post_id, get the link_id useing get_post_meta() function.
 *
 * $links_id = get_the_link_ids_from_group_id( $group_id );
 */

/**
 * Get the link ID related to the current Group ID. - MAIN FUNCTION TO USE WITH THE GROUP ID.
 *
 * @param int $group_id Group ID.
 * @return array Array of found links ID matched against the take_assessment short code.
 */
function get_the_link_ids_from_group_id( $group_id ) {

	$content_ids = array();
	$links_id    = array();

	// get all courses realted to this group.
	$courses = get_the_courses_id_by_group_id( $group_id );

	// early abil. no course found.
	if ( empty( $courses ) ) {
		return $links_id;
	}

	foreach ( $courses as $course ) {
		$content_ids = array_merge( $content_ids, get_the_whole_course_contents_id_by_course_id( $course ) );
	}

	// [take_assessment] shortcode can be in main course post or in the course content. So,
	$content_ids = array_merge( $courses, $content_ids );

	// remove duplicate id if any exist.
	$content_ids = array_unique( $content_ids );

	// early abil. no course content found.
	if ( empty( $content_ids ) ) {
		return $links_id;
	}

	// fetched all assessment posts.
	$loop = fetched_all_mi_assessments_post_type();

	foreach ( $content_ids as $key => $content_id ) {

		// get course content post.
		$content_post = get_post( $content_id );

		if ( isset( $content_post->post_content ) ) {

			$content = wpautop( $content_post->post_content );

			while ( $loop->have_posts() ) :

				$loop->the_post();

				$searc_string  = '[take_assessment assess_id="' . get_the_ID();
				$searc_string2 = "[take_assessment assess_id='" . get_the_ID();

				if (
					strpos( $content, $searc_string ) !== false ||
					strpos( $content, $searc_string2 ) !== false
				) {
					$links_id[] = get_post_meta( get_the_ID(), 'link_id', true );
				}

			endwhile;

		}
	}

	return $links_id;

}

/**
 * Get the link ID related to the current courses and its content.
 *
 * @param int $courses Array of courses IDs..
 * @return array Array of found links ID matched against the take_assessment short code.
 */
function get_the_link_ids_from_courses_id( $courses ) {

	$content_ids = array();
	$links_id    = array();

	// early abil. if empty course array passesd.
	if ( empty( $courses ) ) {
		return $links_id;
	}

	foreach ( $courses as $course ) {
		$content_ids = array_merge( $content_ids, get_the_whole_course_contents_id_by_course_id( $course ) );
	}

	// [take_assessment] shortcode can be in main course post or in the course content. So,
	$content_ids = array_merge( $courses, $content_ids );

	// remove duplicate id if any exist.
	$content_ids = array_unique( $content_ids );

	// early abil. no course content found.
	if ( empty( $content_ids ) ) {
		return $links_id;
	}

	// fetched all assessment posts.
	$loop = fetched_all_mi_assessments_post_type();

	foreach ( $content_ids as $key => $content_id ) {

		// get course content post.
		$content_post = get_post( $content_id );

		if ( isset( $content_post->post_content ) ) {

			$content = wpautop( $content_post->post_content );

			while ( $loop->have_posts() ) :

				$loop->the_post();

				$searc_string  = '[take_assessment assess_id="' . get_the_ID();
				$searc_string2 = "[take_assessment assess_id='" . get_the_ID();

				if (
					strpos( $content, $searc_string ) !== false ||
					strpos( $content, $searc_string2 ) !== false
				) {
					$links_id[] = get_post_meta( get_the_ID(), 'link_id', true );
				}

			endwhile;

		}
	}

	return $links_id;

}

/**
 * Get the link ID related to the current Group ID. - MAIN FUNCTION TO USE WITH THE GROUP ID.
 *
 * @param int $group_id Group ID.
 * @return array Array of found links ID matched against the take_assessment short code.
 */
function get_link_assessment_enrolled_course_ids_from_group_id( $group_id ) {

	$content_ids        = array();
	$links_id           = array();
	$assessments_id     = array();
	$enrolled_course_id = '';

	// get all courses realted to this group.
	$courses = get_the_courses_id_by_group_id( $group_id );

	// early abil. no course found.
	if ( empty( $courses ) ) {
		return array( $links_id, $assessments_id, $enrolled_course_id );
	}

	foreach ( $courses as $course ) {
		$content_ids = array_merge( $content_ids, get_the_whole_course_contents_id_by_course_id( $course ) );
	}

	// [take_assessment] shortcode can be in main course post or in the course content. So,
	$content_ids = array_merge( $courses, $content_ids );

	// remove duplicate id if any exist.
	$content_ids = array_unique( $content_ids );

	// early abil. no course content found.
	if ( empty( $content_ids ) ) {
		return array( $links_id, $assessments_id, $enrolled_course_id );
	}

	// fetched all assessment posts.
	$loop = fetched_all_mi_assessments_post_type();

	foreach ( $content_ids as $key => $content_id ) {

		// get course content post.
		$content_post = get_post( $content_id );

		if ( isset( $content_post->post_content ) ) {

			$content = wpautop( $content_post->post_content );

			while ( $loop->have_posts() ) :

				$loop->the_post();

				$searc_string  = '[take_assessment assess_id="' . get_the_ID();
				$searc_string2 = "[take_assessment assess_id='" . get_the_ID();

				if (
					strpos( $content, $searc_string ) !== false ||
					strpos( $content, $searc_string2 ) !== false
				) {
					$links_id[]       = get_post_meta( get_the_ID(), 'link_id', true );
					$assessments_id[] = get_the_ID();

					if ( in_array( $content_id, $courses ) ) { // phpcs:ignore
						$enrolled_course_id = $content_id;
					} else {
						$enrolled_course_id = get_post_meta( $content_id, 'course_id', true );
					}
				}

			endwhile;

		}
	}

	return array( $links_id, $assessments_id, $enrolled_course_id );

}

/**
 * Findout either the Current Group course contents have assessment shortocde or not.
 *
 * @param int $group_id Group ID.
 * @return bool Returns true if found, false otherwise.
 */
function is_group_has_assessment_shortcode( $group_id ) {

	$content_ids = array();

	// get all courses realted to this group.
	$courses = get_the_courses_id_by_group_id( $group_id );

	// early abil. no course found.
	if ( empty( $courses ) ) {
		return false;
	}

	foreach ( $courses as $course ) {
		$content_ids = array_merge( $content_ids, get_the_whole_course_contents_id_by_course_id( $course ) );
	}

	// [take_assessment] shortcode can be in main course post or in the course content. So,
	$content_ids = array_merge( $courses, $content_ids );

	// remove duplicate id if any exist.
	$content_ids = array_unique( $content_ids );

	// early abil. no course content found.
	if ( empty( $content_ids ) ) {
		return false;
	}

	foreach ( $content_ids as $key => $content_id ) {

		// get course content post.
		$content_post = get_post( $content_id );

		if ( isset( $content_post->post_content ) ) {

			$content = wpautop( $content_post->post_content );

			$searc_string = '[take_assessment assess_id=';

			if ( strpos( $content, $searc_string ) !== false ) {
				return true;
			}
		}
	}

	return false;

}

/**
 * Get current group courses id.
 *
 * @param int $group_id Group ID.
 * @return array Array of courses id.
 */
function get_the_courses_id_by_group_id( $group_id ) {

	global $wpdb;
	$courses_id = array();

	$key = 'learndash_group_enrolled_' . $group_id;

	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT `post_id` FROM %i WHERE meta_key = %s', // phpcs:ignore
			$wpdb->postmeta,
			$key
		)
	);

	foreach ( $results as $key => $value ) {
		if ( isset( $value->post_id ) && ! empty( $value->post_id ) ) {
			$courses_id[] = $value->post_id;
		}
	}

	return $courses_id;

}

/**
 * Get all the content withing a course by course id.
 *
 * @param int $course_id Course ID.
 * @return array Array of course contents id.
 */
function get_the_whole_course_contents_id_by_course_id( $course_id ) {

	global $wpdb;
	$course_content_posts = array();

	$key = 'ld_course_' . $course_id;

	// fetch results base on 'ld_course_' . $course_id key.
	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT `post_id` FROM %i WHERE meta_key = %s', // phpcs:ignore
			$wpdb->postmeta,
			$key
		)
	);

	foreach ( $results as $key => $value ) {
		if ( isset( $value->post_id ) && ! empty( $value->post_id ) ) {
			$course_content_posts[] = $value->post_id;
		}
	}

	// fetch results base on meta_key='course_id' AND meta_value="$course_id" - This included the quizzes.
	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT `post_id` FROM %i WHERE meta_key = \'course_id\' AND meta_value = %s', // phpcs:ignore
			$wpdb->postmeta,
			$course_id
		)
	);

	foreach ( $results as $key => $value ) {
		if ( isset( $value->post_id ) && ! empty( $value->post_id ) ) {
			$course_content_posts[] = $value->post_id;
		}
	}

	return $course_content_posts;

}

/**
 * Get Group Leader ID from Group ID.
 *
 * @since   2.0.0
 * @param string $group_id Group ID.
 *
 * @return stirng
 */
function get_group_leader_id_from_group_id( $group_id ) {

	global $wpdb;

	$meta_key  = 'learndash_group_leaders_' . $group_id;
	$leader_id = 0;

	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare(
			"SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s",
			$meta_key
		)
	);

	if ( $results ) {
		$leader_id = $results->user_id;
	}

	return $leader_id;

}

/**
 * Get Group Leader Enrollment Email setting meta.
 *
 * @since   2.0.0
 * @param string $leader Leader ID.
 *
 * @return stirng
 */
function get_group_leader_enrollment_email_setting_meta( $leader ) {

	$key = 'group_user_' . $leader . '_settings';

	return get_user_meta( $leader, $key, 'false' );

}

/**
 * Get user limits based on user ID, group ID, and link ID.
 *
 * @param int    $user_id  The user ID.
 * @param int    $group_id The group ID.
 * @param string $link_id  The link ID.
 *
 * @return int|string User limits or a string ('0' or '1').
 */
function get_user_limits( $user_id, $group_id, $link_id ) {

	global $wpdb;

	$users_limit = $wpdb->prefix . 'tti_users_limit';

	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare(
			"SELECT * FROM $users_limit WHERE user_id = %s AND data_link = %s", // phpcs:ignore
			$user_id,
			$link_id
		)
	);

	if ( $results ) {

		if ( strpos( $results->group_id, $group_id ) !== false ) {
			return $results->limits;
		}
	} else {

		$assessment_table = $wpdb->prefix . 'assessments';

		$results = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				'SELECT * FROM %i WHERE user_id = %s AND link_id = %s AND status = 1', // phpcs:ignore
				$assessment_table,
				$user_id,
				$link_id
			)
		);

		return ( $results ) ? '0' : '1';
	}

	return '0';
}

/**
 * Check if the user has a last login record.
 *
 * @param int $user_id The user ID to check.
 *
 * @return bool Returns true if the user has a last login record, false otherwise.
 */
function mi_user_last_login_time( $user_id ) {

	$user_last_login = get_user_meta( $user_id, 'wc_last_active', true );

	return empty( $user_last_login ) ? false : true;

}

/**
 * Check if the current user has completed an assessment.
 *
 * @param string $user_id The user ID to check.
 * @param string $link_id  The link ID to check.
 *
 * @return bool Returns true if the user has completed the assessment, false otherwise.
 */
function mi_check_is_current_user_completed_assessment( $user_id, $link_id ) {

	global $wpdb;
	$assessment_table = $wpdb->prefix . 'assessments';

	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT * FROM %i WHERE user_id = %s AND link_id = %s AND status = 1', // phpcs:ignore
			$assessment_table,
			$user_id,
			$link_id
		)
	);

	return ( $results ) ? true : false;

}

/**
 * Get the creation date of a specific assessment by user.
 *
 * @param string $user_id      The user ID to check.
 * @param string $link_id      The link ID to check.
 * @param string $pdf_version  The PDF version to check.
 *
 * @return string|false Returns the creation date or false if not found.
 */
function mi_get_current_assessment_create_date_by_user( $user_id, $link_id, $pdf_version ) {
	global $wpdb;
	$assessment_table = $wpdb->prefix . 'assessments';

	$results = $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT created_at FROM %i WHERE user_id = %s AND link_id = %s AND status = 1 AND version = %s', // phpcs:ignore
			$assessment_table,
			$user_id,
			$link_id,
			$pdf_version
		)
	);

	return ( $results ) ? $results : false;

}


/**
 * Get the count of assessments for a specific user and link.
 *
 * @param string $user_id The user ID to check.
 * @param string $link_id The link ID to check.
 *
 * @return int|false Returns the count of assessments or false if not found.
 */
function mi_get_completed_assessment_counts_by_user( $user_id, $link_id ) {

	global $wpdb;
	$assessment_table = $wpdb->prefix . 'assessments';

	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT * FROM %i WHERE user_id = %s AND link_id = %s AND status = 1', // phpcs:ignore
			$assessment_table,
			$user_id,
			$link_id
		)
	);

	return ( $results ) ? count( $results ) : false;

}

/**
 *  Function to get user assessment latest version.
 *
 * @since   1.6
 *
 * @param integer $user_id contains user id.
 * @param string  $link_id contains assessment link id.
 *
 * @return integer contains count of assessments
 */
function get_current_user_assess_version( $user_id, $link_id ) {

	global $wpdb;

	$assessment_table = $wpdb->prefix . 'assessments';

	// Execute the query to get results.
	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT * FROM %i WHERE user_id = %s AND link_id = %s', // phpcs:ignore
			$assessment_table,
			$user_id,
			$link_id
		)
	);

	// Return the count of results.
	return ( isset( $results ) && count( $results ) > 0 ) ? count( $results ) : 1;

}

/**
 * Function to assessment shortcode for Frontend.
 *
 * @since   1.0.0
 *
 * @param integer $current_user contains current user id.
 * @param string  $link_id contains assessment link id.
 * @return boolean return true or false
 */
function check_user_limit( $current_user, $link_id ) {

	global $wpdb;
	$users_limit = $wpdb->prefix . 'tti_users_limit';

	// Execute the query to get results.
	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			'SELECT * FROM %i WHERE user_id = %s AND data_link = %s', // phpcs:ignore
			$users_limit,
			$current_user,
			$link_id
		)
	);

	if ( $results ) {
		if ( isset( $results->limits ) && $results->limits > 0 ) {
			return true;
		} elseif ( isset( $results->limits ) && $results->limits <= 0 ) {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Function to get the latest completed assessment results.
 *
 * @since   1.0.0
 *
 * @param integer $current_user contains current user id.
 * @param string  $link_id contains assessment link id.
 * @param string  $asses_version Version of completed assessment.
 * @param string  $column_to_return Which column need to fetch the results.
 * @return boolean return true or false
 */
function get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version, $column_to_return = '*' ) {

	global $wpdb;

	$assessment_table = $wpdb->prefix . 'assessments';
	// Prepare and execute the SQL query with parameterization.
	$results = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			"SELECT %s FROM %i WHERE user_id = %s AND link_id = %s AND status = %d AND version = %d", // phpcs:ignore
			$column_to_return,
			$assessment_table,
			$current_user,
			$link_id,
			1,
			$asses_version
		)
	);

	// TASSAWER - Here implement the logic to retrive data again from the API.
	return $results;

}

/*************************************************************
 * Initiate Email to Group Leader. - START
 ************************************************************/

/**
 * Function to send mail to group leaders.
 *
 * @since    1.2.1
 *
 * @param integer $report_id contains report id.
 * @param string  $api_key contains assessment API key.
 * @param string  $api_service contains service location link.
 * @param integer $current_user_id contains current user id.
 * @param array   $user_email_data User data.
 * @param integer $assessment_id contains assessment id.
 *
 * @return boolean contains result for download PDF status.
 */
function initiate_group_leader_email_process( $report_id, $api_key, $api_service, $current_user_id, $user_email_data, $assessment_id ) {

	// Get group leaders mail associated to by assessment id.
	$leaders_email = get_groupleader_mails_by_asses_id( $current_user_id );
	$leaders_email = array_filter( $leaders_email );

	if ( count( $leaders_email ) > 0 ) {

		// Leader(s) exist. Download the PDF file, save it and send it.
		$url = $api_service . '/api/v3/reports/' . $report_id . '.pdf';

		// Create API class instance.
		$mi_api     = new Mi_Assessments_API();
		$pdf_report = $mi_api->mi_download_pdf_report( $url, $api_key, 'GET' );

		$download_path = save_pdf_file( $pdf_report, $user_email_data, $assessment_id );

		/* Send email to group leaders */
		$error_log = send_mail_to_group_leaders( $leaders_email, $download_path, $current_user_id, $user_email_data, $assessment_id );

	} else {
		$error_log = array(
			'Group Leader' => 'No group leaders found for this user',
			'log_type'     => 'primary',
		);
	}

	return $error_log;

}

/**
 * Function to get group leader emails.
 *
 * @since   1.2
 *
 * @param integer $user_id contains user id.
 * @return array contains group leader emails
 */
function get_groupleader_mails_by_asses_id( $user_id ) {

	$group_leaders    = get_transient( 'assessmentListenerGroupLeaders' . $user_id );
	$user_detail      = get_userdata( $group_leaders );
	$leaders_emails[] = $user_detail->user_email;

	return $leaders_emails;
}

/**
 * Function to save pdf report file in uploads directory.
 *
 * @since   1.0.0
 *
 * @param array   $response contains api response body data.
 * @param string  $user_email_data contains user email address.
 * @param integer $assessment_id contains assessment id.
 *
 * @return string contains downloaded PDF report file path.
 */
function save_pdf_file( $response, $user_email_data, $assessment_id ) {

	$first_name = isset( $user_email_data['first_name'] ) ? $user_email_data['first_name'] : 'Ministry';
	$last_name  = isset( $user_email_data['last_name'] ) ? $user_email_data['last_name'] : 'Insights';

	$titles = str_replace( ' ', '_', get_the_title( $assessment_id ) );

	$file_name = $first_name . '_' . $last_name . '_' . $titles;

	$date = date( 'd-m-Y', time() ); // phpcs:ignore

	if ( ! file_exists( WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/' ) ) {
		mkdir( WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/', 0777, true );
	}

	$download_path = WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/' . $file_name . '.pdf';

	$file = fopen( $download_path, 'w+' ); // phpcs:ignore
	fputs( $file, $response );
	fclose( $file ); // phpcs:ignore

	return $download_path;
}

/**
 * Function to send mail to group leaders using WordPress mail function.
 *
 * @since   1.2
 *
 * @param array   $to contains array of emails.
 * @param string  $mail_attachment contains download PDF file attachment.
 * @param string  $current_user contains user id.
 * @param string  $user_email_data contains user email address.
 * @param integer $assessment_id contains assessment id.
 *
 * @return array contains email log details.
 */
function send_mail_to_group_leaders( $to, $mail_attachment, $current_user, $user_email_data, $assessment_id ) {

	$group_leaders = get_transient( 'assessmentListenerGroupLeaders' . (string) $current_user );

	$user        = get_user_by( 'id', $current_user );
	$leader_user = get_user_by( 'id', $group_leaders );

	$first_name = isset( $user_email_data['first_name'] ) ? $user_email_data['first_name'] : 'Ministry';
	$last_name  = isset( $user_email_data['last_name'] ) ? $user_email_data['last_name'] : 'Insights';

	$titles    = str_replace( ' ', '_', get_the_title( $assessment_id ) );
	$site_name = str_replace( ' ', '_', get_bloginfo( 'name' ) );

	$subject         = 'Report (' . $first_name . '_' . $last_name . '_' . $titles . '_' . $site_name . ')';
	$attachment_name = $first_name . ' ' . $last_name . ' ' . $titles;
	$email           = $user->user_email;
	$display_name    = $user->display_name;
	$site_name       = get_bloginfo( 'name' );
	$to              = $to;
	$from            = 'Ministry Insights';
	$admin_email     = get_option( 'admin_email' );
	$headers         = 'MIME-Version: 1.0' . "\r\n";
	$headers        .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers        .= 'From: ' . $site_name . '  <' . $admin_email . '>' . "\r\n";
	$headers        .= 'Reply-To: ' . $leader_user->display_name . ' <' . $leader_user->user_email . '>' . "\r\n";

	$msg = '
		<!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<style>
					table {
						font-size: 17px;
						font-family: arial, sans-serif;
						border-collapse: collapse;
						width: 100%;
					}

					td, th {
						text-align: left;
						padding: 8px;
					}
				</style>
			</head>
			<body>
				<table>
					<tr style="background-color: #dddddd;">
						<td>Link Description:</td>
						<td>' . $title . ' (' . $user_email_data['link_id'] . ')</td>
					</tr>

					<tr>
						<td>Respondent Name: </td>
						<td>' . $user_email_data['first_name'] . ' ' . $user_email_data['last_name'] . '</td>
					</tr>

					<tr style="background-color: #dddddd;">
						<td>Respondent E-mail: </td>
						<td>' . $user_email_data['email'] . '</td>
					</tr>

					<tr>
						<td>Respondent Company: </td>
						<td>' . $user_email_data['company'] . '</td>
					</tr>

					<tr style="background-color: #dddddd;">
						<td>Respondent Position: </td>
						<td>' . $user_email_data['position_job'] . '</td>
					</tr>
				</table>
				<br /><br />

				<div style="font-family: Arial, Helvetica, sans-serif;font-size: 19px;">
					<strong>ATTACHMENT : ' . $attachment_name . '</strong>
				</div>
			</body>
		</html>
	';

	$error_log = array(
		'Sent To'       => 'Group Leader Emails Sent To : ' . wp_json_encode( $to ),
		'Email Subject' => $subject,
	);

	// WordPress mail function.
	$re = wp_mail( $to, $subject, $msg, $headers, $mail_attachment );

	if ( $re ) {

		$error_log['Email Status'] = 'Email Sent Successfully';

		// Delete pdf file.
		delete_pdf_file( $mail_attachment );
		$error_log['PDF Attachemnt'] = 'Deleted the PDF attachment file from the server.';

	} else {

		$error_log['Email Status'] = 'Email Sent Failed';
		$error_log['log_type']     = 'error';

	}

	return $error_log;

}

/**
 * Function to delete file in uploads directory.
 *
 * @since   1.2
 *
 * @param string $download_path contains file path.
 */
function delete_pdf_file( $download_path ) {

	unlink( $download_path );

}

/*************************************************************
 * Initiate Email to Group Leader. - END
 *********************************************************** */

