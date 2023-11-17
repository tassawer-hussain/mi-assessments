<?php
/**
 * Class to contain functionality of group leader plugin emails customizations.
 *
 * @link       https://ministryinsights.com/
 * @since      1.6.3
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * LearnDash Group Registration Group Leader Email Customization.
 *
 * Class to contain functionality of group leader plugin emails customizations.
 *
 * @since      1.6.3
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_LDGR_Group_Leader_Emails {

	/**
	 * The email body subject for the CC (Carbon Copy) of the invite send to the Group Leader.
	 *
	 * @var string
	 */
	public $subject_invite_cc;

	/**
	 * The email body content for the CC (Carbon Copy) of the invite send to the Group Leader.
	 *
	 * @var string
	 */
	public $body_invite_cc;

	/**
	 * The email subject for notifications sent to new users in the CC (Carbon Copy).
	 *
	 * @var string
	 */
	public $subject_new_user_cc;

	/**
	 * The email body content for notifications sent to new users in the CC (Carbon Copy).
	 *
	 * @var string
	 */
	public $body_new_user_cc;

	/**
	 * The group leader user id.
	 *
	 * @var int
	 */
	public $leader_id;

	/**
	 * Define the constructor
	 *
	 * @since  1.6.3
	 */
	public function __construct() {

		$this->leader_id = get_current_user_id();

		add_filter( 'wdm_reinvite_email_subject', array( $this, 'mi_assessments_grab_reinvite_email_subject' ), 10, 4 );
		add_filter( 'wdm_reinvite_email_body', array( $this, 'mi_assessments_grab_reinvite_email_body' ), 10, 4 );
		add_filter( 'ldgr_filter_enroll_user_emails', array( $this, 'ldgr_filter_enroll_user_emails_func' ), 10, 2 );

		// Update Leader name to display as it is.
		add_filter( 'wdm_group_email_subject', array( $this, 'th_wdm_group_email_subject' ), 999, 3 );
		add_filter( 'wdm_group_email_body', array( $this, 'th_wdm_group_email_body' ), 999, 3 );

	}

	/**
	 * Function to get email subject.
	 *
	 * @since  1.6.3
	 * @param string  $subject contains reinvite email subject.
	 * @param array   $group_id contains group id.
	 * @param integer $current_id contains current course id.
	 * @param integer $user_id contains user id.
	 * @return string returns final subject
	 */
	public function mi_assessments_grab_reinvite_email_subject( $subject, $group_id, $current_id, $user_id ) {

		$this->subject_invite_cc = $subject . ' [COPY]';

		return $subject;
	}

	/**
	 * Function to get email body.
	 *
	 * @since  1.6.3
	 * @param string  $body contains reinvite email body.
	 * @param array   $group_id contains group id.
	 * @param integer $current_id contains current course id.
	 * @param integer $user_id contains user id.
	 * @return string return final reinvite body.
	 */
	public function mi_assessments_grab_reinvite_email_body( $body, $group_id, $current_id, $user_id ) {

		$this->body_invite_cc = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $body;

		$this->cc_the_leader_reinvite_email( $group_id );

		return $body;
	}

	/**
	 * Send bulk upload emails
	 *
	 * @param array $all_emails_list     List of all emails to send emails to.
	 * @param int   $group_id            ID of the group.
	 */
	public function ldgr_filter_enroll_user_emails_func( $all_emails_list, $group_id ) {

		foreach ( $all_emails_list as $user_id => $details ) {

			if ( $details['new'] ) {

				if ( apply_filters( 'is_ldgr_default_user_add_action', true ) ) {
					$this->pt_new_registration(
						$user_id,
						$details['user_data']['first_name'],
						$details['user_data']['last_name'],
						$details['user_data']['user_email'],
						$details['user_data']['user_pass'],
						$details['courses'],
						$details['lead_user'],
						$details['group_id']
					);
				}
			} else {

				global $wpdb;

				// Define the meta key based on the group ID.
				$meta_key = 'learndash_group_leaders_' . $group_id;

				// Prepare the SQL query with parameterization.
				$sql_str = $wpdb->prepare(
					"SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s",
					$meta_key
				);

				// Execute the query to get the group leaders.
				$group_leaders = $wpdb->get_results( $sql_str ); // phpcs:ignore

				$send_to = array();
				if ( $group_leaders ) :
					foreach ( $group_leaders as $leader ) :
						$user_info = get_userdata( $leader->user_id );
						$send_to[] = $user_info->user_email;
					endforeach;

					$send_to = implode( ',', $send_to );

					ldgr_send_group_mails(
						$send_to,
						$details['subject'] . ' [COPY]',
						'<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $details['body'],
						array(),
						array(),
						array(
							'email_type' => 'WDM_U_ADD_GR_BODY',
							'group_id'   => $group_id,
						)
					);

				endif;
			}
		}

		return $all_emails_list;
	}

	/**
	 * Function to show email options.
	 *
	 * @param int $group_id            ID of the group.
	 * @since  1.6.3
	 */
	public function cc_the_leader_reinvite_email( $group_id ) {

		$user_leader_data = get_user_by( 'id', $this->leader_id );

		if ( isset( $user_leader_data->user_email ) && ! empty( $user_leader_data->user_email ) ) {
			$send_to = $user_leader_data->user_email;

			$message     = $this->body_invite_cc;
			$subject     = $this->subject_invite_cc;
			$attachments = '';
			$headers     = '';

			if ( class_exists( 'WooCommerce' ) ) { // If WooCommerce.
				global $woocommerce;
				$mailer  = $woocommerce->mailer();
				$message = $mailer->wrap_message( $subject, $message );
				$mailer->send( $send_to, $subject, $message, $headers, $attachments );

			} elseif ( class_exists( 'EDD_Emails' ) ) { // If EDD.
				EDD()->emails->send( $send_to, $subject, $message, $attachments );
			} else {
				$sent = wp_mail( $send_to, $subject, $message, $headers, $attachments );
			}
		}
	}

	/**
	 * Register new user and enroll in group
	 *
	 * @param int    $member_user_id   ID of the user to register and enroll.
	 * @param string $f_name           First name of the user.
	 * @param string $l_name           Last name of the user.
	 * @param string $val              Email of the user.
	 * @param string $password         Password of the new user.
	 * @param array  $courses          List of courses to enroll in.
	 * @param obj    $lead_user        Group leader.
	 * @param int    $group_id         ID of the group.
	 *
	 * @return void
	 */
	public function pt_new_registration( $member_user_id, $f_name, $l_name, $val, $password, $courses, $lead_user, $group_id ) {

		if ( ! is_wp_error( $member_user_id ) ) {

			// Set email subject to send group leader.
			$subject = get_option( 'wdm-u-ac-crt-sub' );
			if ( empty( $subject ) ) {
				$subject = WDM_U_AC_CRT_SUB;
			}
			$subject = stripslashes( $subject );
			$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
			$subject = str_replace( '{site_name}', get_bloginfo(), $subject );
			$subject = str_replace( '{user_first_name}', '', $subject );
			$subject = str_replace( '{user_last_name}', '', $subject );
			$subject = str_replace( '{user_email}', '', $subject );
			$subject = str_replace( '{user_password}', '', $subject );
			$subject = str_replace( '{course_list}', '', $subject );
			$subject = str_replace( '{group_leader_name}', $lead_user->first_name . ' ' . $lead_user->last_name, $subject );
			$subject = str_replace( '{login_url}', '', $subject );

			$enrolled_course = array();
			foreach ( $courses as $key => $value ) {
				$enrolled_course[] = get_the_title( $value );
			}

			$tbody = get_option( 'wdm-u-ac-crt-body' );
			if ( empty( $tbody ) ) {
				$tbody = WDM_U_AC_CRT_BODY;
			}
			$body = stripslashes( $tbody );

			$body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
			$body = str_replace( '{site_name}', get_bloginfo(), $body );
			$body = str_replace( '{user_first_name}', ucfirst( $f_name ), $body );
			$body = str_replace( '{user_last_name}', ucfirst( $l_name ), $body );
			$body = str_replace( '{user_email}', $val, $body );
			$body = str_replace( '{user_password}', $password, $body );
			$body = str_replace( '{course_list}', $this->pt_get_course_list_html( $enrolled_course ), $body );
			$body = str_replace( '{group_leader_name}', $lead_user->first_name . ' ' . $lead_user->last_name, $body );
			$body = str_replace( '{login_url}', wp_login_url(), $body );

			$this->body_new_user_cc    = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $body;
			$this->subject_new_user_cc = $subject . ' [COPY]';

			$this->cc_the_leader_new_user();
		}
	}

	/**
	 * Function to sent email to Group Leader on new user registration.
	 *
	 * @since  1.6.3
	 */
	public function cc_the_leader_new_user() {

		$user_leader_data = get_user_by( 'id', $this->leader_id );

		if ( isset( $user_leader_data->user_email ) && ! empty( $user_leader_data->user_email ) ) {
			$send_to = $user_leader_data->user_email;

			$message     = $this->body_new_user_cc;
			$subject     = $this->subject_new_user_cc;
			$attachments = '';
			$headers     = '';

			if ( class_exists( 'WooCommerce' ) ) { // If WooCommerce.
				global $woocommerce;
				$mailer  = $woocommerce->mailer();
				$message = $mailer->wrap_message( $subject, $message );
				$mailer->send( $send_to, $subject, $message, $headers, $attachments );
			} elseif ( class_exists( 'EDD_Emails' ) ) { // If EDD.
				EDD()->emails->send( $send_to, $subject, $message, $attachments );
			} else {
				$sent = wp_mail( $send_to, $subject, $message, $headers, $attachments );
			}
		}
	}

	/**
	 * Get course list HTML
	 *
	 * @param array $course_list    List of courses to display.
	 * @return string               HTML list of courses.
	 */
	public function pt_get_course_list_html( $course_list ) {

		$return = '';

		if ( ! empty( $course_list ) ) {

			$return = '<ul>';
			foreach ( $course_list as $course ) {
				$return .= '<li>' . $course . '</li>';
			}
			$return .= '</ul>';

		}

		return $return;
	}

	/**
	 * Update email subject to reflect the leader name as it is.
	 *
	 * @param string $subject Subject of the email.
	 * @param int    $group_id Group ID.
	 * @param int    $member_user_id Enrolled user ID.
	 * @return string
	 */
	public function th_wdm_group_email_subject( $subject, $group_id, $member_user_id ) {
		global $wpdb;

		// phpcs:ignore
		$mylink = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->usermeta WHERE meta_key = %s",
				'learndash_group_leaders_' . $group_id
			)
		);

		$group_leader_id = $mylink->user_id;

		$leader_data = get_user_by( 'id', $group_leader_id );

		$subject = str_replace( ucfirst( strtolower( $leader_data->first_name ) ), $leader_data->first_name, $subject );
		$subject = str_replace( ucfirst( strtolower( $leader_data->last_name ) ), $leader_data->last_name, $subject );

		return $subject;
	}

	/**
	 * Update email body to reflect the leader name as it is.
	 *
	 * @param string $body Body of the email.
	 * @param int    $group_id Group ID.
	 * @param int    $member_user_id Enrolled user ID.
	 * @return string
	 */
	public function th_wdm_group_email_body( $body, $group_id, $member_user_id ) {
		global $wpdb;

		// phpcs:ignore
		$mylink = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->usermeta WHERE meta_key = %s",
				'learndash_group_leaders_' . $group_id
			)
		);

		$group_leader_id = $mylink->user_id;

		$leader_data = get_user_by( 'id', $group_leader_id );

		$body = str_replace( ucfirst( strtolower( $leader_data->first_name ) ), $leader_data->first_name, $body );
		$body = str_replace( ucfirst( strtolower( $leader_data->last_name ) ), $leader_data->last_name, $body );

		return $body;
	}

}
