<?php
/**
 * Class to contain functionality realted to process user retake assessment.
 *
 * @link       https://ministryinsights.com/
 * @since      1.6.3
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * LearnDash Group Registration User Re-take assessment functionality.
 *
 * Class to implement functionality related to retake assessments.
 *
 * @since      1.6.3
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_LDGR_Retake_Assessment {

	/**
	 * User assessment link id.
	 *
	 * @var string
	 */
	public $link_id;

	/**
	 * User email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Current User ID
	 *
	 * @var string
	 */
	public $user_id;

	/**
	 * Current Group ID
	 *
	 * @var string
	 */
	public $group_id;

	/**
	 * Group Leader ID
	 *
	 * @var string
	 */
	public $group_leader_id;

	/**
	 * Number of seats left in the group.
	 *
	 * @var string
	 */
	public $group_limit;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since       1.6
	 * @param int    $user_id contains user id.
	 * @param string $email contains email address.
	 * @param int    $group_id contains group id related to user.
	 * @param string $link_id contain assessment link id.
	 * @param int    $group_leader_id contains geoup leader id.
	 */
	public function __construct( $user_id, $email, $group_id, $link_id, $group_leader_id ) {

		$this->user_id         = $user_id;
		$this->email           = $email;
		$this->group_id        = $group_id;
		$this->link_id         = $link_id;
		$this->group_leader_id = $group_leader_id;
	}

	/**
	 * Function to starts the retake assessment process.
	 *
	 * @since   1.6
	 * @access  public
	 */
	public function retake_assessment_process() {
		global $wpdb;
		$status = 0;

		// Check group limit first.
		$group_leader_limit = $this->check_and_reduce_group_limit_by_one();

		// Proceed if there is user registration limit left.
		if ( $group_leader_limit ) {

			$this->process_users_limit();

			// Process email to user and leader only when leader email setting is set to 'false' and reinvite email set to 'on'.
			if ( $this->is_reinvite_email_and_leader_email_settings_enable() ) {
				$this->send_email_to_user();
			}

			$message = __( 'User can now take assessment again.', 'tti-platform' );
			$status  = 1;

		} else {
			$message = __( "This group don't have any user registration left. Please buy more registrations before allow user to retake assessment.", 'tti-platform' );
		}

		$resp = array(
			'message' => $message,
			'status'  => $status,
		);
		echo wp_json_encode( $resp );

		exit;
	}

	/**
	 * Function to check current group limit.
	 *
	 * @since   1.6
	 * @return boolean contains true|false
	 */
	public function check_and_reduce_group_limit_by_one() {

		global $wpdb;

		$key = 'wdm_group_users_limit_' . $this->group_id;

		// Prepare the SQL query with parameterization and fetch a single row.
		$row = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
				$key
			)
		);

		// record exist.
		if ( $row ) {
			$this->group_limit = $row->meta_value;
		}

		if ( $this->group_limit >= 1 ) {

			// Reduce the available seat by 1.
			$group_limit = get_post_meta( $this->group_id, 'wdm_group_total_users_limit_' . $this->group_id, true );
			if ( '' === $group_limit ) {
				$group_limit = 0;
			} else {
				--$group_limit;
				update_post_meta( $this->group_id, 'wdm_group_total_users_limit_' . $this->group_id, $group_limit );
			}

			return true;

		} else {
			return false;
		}

	}

	/**
	 * Function to update the user take assessment limi.
	 *
	 * @since   1.6
	 */
	public function process_users_limit() {

		global $wpdb;

		$limit        = 0;
		$users_limit  = $wpdb->prefix . 'tti_users_limit';
		$exists_in_db = false;

		// Prepare and execute the SQL query.
		$results = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				"SELECT * FROM %i WHERE user_id = %s AND data_link = %s", // phpcs:ignore
				$users_limit,
				$this->user_id,
				$this->link_id
			)
		);

		// already record exist.
		if ( $results->data_link === $this->link_id ) {

			$exists_in_db = true;
			$limit        = $results->limits;

		} else {
			$limit            = 1;
			$assessment_table = $wpdb->prefix . 'assessments';

			// Prepare and execute the SQL query.
			$results = $wpdb->get_row( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"SELECT * FROM %i WHERE user_id = %d AND link_id = %s AND status = 1", // phpcs:ignore
					$assessment_table,
					$this->user_id,
					$this->link_id
				)
			);

			if ( ! $results ) {
				$limit = 2;
			}
		}

		if ( $exists_in_db && ! empty( $this->link_id ) ) {

			/* Update the limit */
			$group_ids = $results->group_id;
			if ( strpos( $group_ids, $this->group_id ) === false ) {
				$group_ids .= ',' . $this->group_id;
			}

			$this->update_user_limit( $limit, $this->link_id, $group_ids );

		} else {

			/* Insert data with limit */
			$this->add_user_limit( $limit, $this->link_id );

		}
	}

	/**
	 * Function to update user limit by one.
	 *
	 * @since   1.6
	 * @param integer $limit contains user assessment limit.
	 * @param string  $link_id contains assessment link id.
	 * @param array   $group_ids contains group ids related to current selected course.
	 */
	public function update_user_limit( $limit, $link_id, $group_ids ) {

		global $wpdb;
		$users_limit = $wpdb->prefix . 'tti_users_limit';

		$update_data = array(
			'limits'   => $limit,
			'group_id' => $group_ids,
		);

		$conditions = array(
			'user_id'   => $this->user_id,
			'data_link' => $link_id,
		);

		// phpcs:ignore
		$is_updated = $wpdb->update(
			$users_limit,
			$update_data,
			$conditions
		);

		if ( false !== $is_updated && 0 !== $this->group_id ) {
			$this->group_limit--;
			$this->reduce_group_limit();
		}
	}

	/**
	 * Function to add user limit by one.
	 *
	 * @since   1.6
	 * @param integer $limit contains user assessment limit.
	 * @param string  $link_id contains assessment link id.
	 */
	public function add_user_limit( $limit, $link_id ) {

		global $wpdb;
		$users_limit = $wpdb->prefix . 'tti_users_limit';

		// Define the data to be inserted.
		$insert_data = array(
			'user_id'   => $this->user_id,
			'email'     => $this->email,
			'group_id'  => $this->group_id,
			'limits'    => $limit,
			'data_link' => $link_id,
		);

		// Define the data types for each column.
		$data_types = array( '%d', '%s', '%s', '%d', '%s' );

		// phpcs:ignore
		$is_inserted = $wpdb->insert(
			$users_limit,
			$insert_data,
			$data_types
		);

		if ( false !== $is_inserted && 0 !== $this->group_id ) {
			$this->group_limit--;
			$this->reduce_group_limit();
		}
	}

	/**
	 * Function to reduce group limit.
	 *
	 * @since   1.6
	 */
	public function reduce_group_limit() {

		$key               = 'wdm_group_users_limit_' . $this->group_id;
		$this->group_limit = ( isset( $this->group_limit ) && $this->group_limit < 0 ) ? 0 : $this->group_limit;

		update_post_meta( $this->group_id, $key, $this->group_limit );
	}

	/**
	 * Function to send email to user for retaking assessment.
	 *
	 * @since   1.6
	 */
	public function send_email_to_user() {

		if ( isset( $this->user_id ) && isset( $this->email ) && $this->link_id ) {

			$ass_id = get_assessment_post_id_by_link_id( $this->link_id );
			$title  = get_the_title( $ass_id );

			// update the assessment assigned time.
			update_user_meta(
				$this->user_id,
				'assigned_group_' . $this->group_id . '_' . $this->group_leader_id . '_' . $ass_id,
				time(),
				true
			);

			$user_data   = get_user_by( 'id', $this->user_id );
			$leader_data = get_user_by( 'id', $this->group_leader_id );

			$leader_email = $leader_data->user_email;
			$leader_name  = $leader_data->first_name . ' ' . $leader_data->last_name;

			$m_subject = $this->get_mail_subject( $title, $leader_name, $user_data );

			$m_body = $this->get_mail_body( $title, $leader_name, $user_data );

			$this->send_retake_assess_mail( $m_subject, $m_body, $user_data, $leader_data, $leader_email );

		}
	}

	/**
	 * Function to get mail subject.
	 *
	 * @since   1.6
	 * @param string $title contains title for subject.
	 * @param string $group_leader_name contains group leader name.
	 * @param array  $user_data contains user data related to there assessments.
	 * @return string returns subject for email
	 */
	public function get_mail_subject( $title, $group_leader_name, $user_data ) {

		$subject = stripslashes( get_option( 'wdm-gr-retake-assessment' ) );
		if ( empty( $subject ) ) {
			$subject = 'Retake Assessment';
		}

		$subject = str_replace( '{assessment_title}', $title, $subject );
		$subject = str_replace( '{site_name}', get_bloginfo(), $subject );
		$subject = str_replace( '{user_first_name}', '', $subject );
		$subject = str_replace( '{user_last_name}', '', $subject );
		$subject = str_replace( '{user_email}', '', $subject );
		$subject = str_replace( '{group_leader_name}', $group_leader_name, $subject );
		$subject = str_replace( '{login_url}', '', $subject );

		return $subject;
	}

	/**
	 * Function to get mail body.
	 *
	 * @since   1.6
	 * @param string $title  contains assessment title.
	 * @param string $group_leader_name contains group leader name.
	 * @param array  $user_data contains user data.
	 * @return string returns email body.
	 */
	public function get_mail_body( $title, $group_leader_name, $user_data ) {

		$body = stripslashes( get_option( 'wdm-u-add-gr-body-retake-assess' ) );

		$key = get_password_reset_key( $user_data );

		$user_login = $user_data->user_login;

		$reset_arg = array(
			'action' => 'rp',
			'key'    => $key,
			'login'  => rawurlencode( $user_login ),
		);

		$reset_password_link = add_query_arg( $reset_arg, network_site_url( 'wp-login.php', 'login' ) );

		$courses = get_the_courses_id_by_group_id( $this->group_id );

		$enrolled_course_output = '';

		if ( count( $courses ) > 0 ) {
			$enrolled_course_output .= '<ul>';

			foreach ( $courses as $key => $value ) {
				$enrolled_course_output .= '<li>' . get_the_title( $value ) . '</li>';
			}

			$enrolled_course_output .= '</ul>';
		}

		$body = str_replace( '{assessment_title}', $title, $body );
		$body = str_replace( '{reset_password}', $reset_password_link, $body );
		$body = str_replace( '{site_name}', get_bloginfo(), $body );
		$body = str_replace( '{user_first_name}', ucfirst( $user_data->first_name ), $body );
		$body = str_replace( '{user_last_name}', ucfirst( $user_data->last_name ), $body );
		$body = str_replace( '{user_email}', $user_data->user_email, $body );
		$body = str_replace( '{group_leader_name}', $group_leader_name, $body );
		$body = str_replace( '{login_url}', wp_login_url(), $body );
		$body = str_replace( '{course_list}', $enrolled_course_output, $body );

		return $body;
	}

	/**
	 * Function to send mail to user for retake assessment notification.
	 *
	 * @since   1.6
	 * @param string $subject  contains assessment title.
	 * @param string $body contains group leader name.
	 * @param array  $user_data contains user data.
	 * @param array  $user_leader_data contains user data.
	 * @param array  $leader_email contains user data.
	 * @return void
	 */
	public function send_retake_assess_mail( $subject, $body, $user_data, $user_leader_data, $leader_email = '' ) {

		$email = get_option( 'admin_email' );

		if ( isset( $user_data->user_email ) && ! empty( $user_data->user_email ) ) {

			$send_to     = $user_data->user_email;
			$message     = $body;
			$attachments = '';
			$headers[]   = 'Reply-To: ' . $user_leader_data->first_name . ' ' . $user_leader_data->last_name . ' <' . $leader_email . '>';

			if ( class_exists( 'WooCommerce' ) ) {  // If WooCommerce.

				global $woocommerce;
				$mailer  = $woocommerce->mailer();
				$message = $mailer->wrap_message( $subject, $message );
				$mailer->send( $send_to, $subject, $message, $headers, $attachments );

			} elseif ( class_exists( 'EDD_Emails' ) ) { // If EDD.

				EDD()->emails->send( $send_to, $subject, $message, $attachments );

			} else {

				$sent = wp_mail( $send_to, $subject, $message, $headers, $attachments );

			}

			/* CC group leader */
			$headers = '';
			if ( ! empty( $leader_email ) ) {

				$subject = $subject . ' [COPY]';
				$message = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br><br> ' . $body;

				if ( class_exists( 'WooCommerce' ) ) {  // If WooCommerce.

					global $woocommerce;
					$mailer  = $woocommerce->mailer();
					$message = $mailer->wrap_message( $subject, $message );
					$mailer->send( $leader_email, $subject, $message, $headers, $attachments );

				} elseif ( class_exists( 'EDD_Emails' ) ) { // If EDD.

					EDD()->emails->send( $leader_email, $subject, $message, $attachments );

				} else {

					$sent = wp_mail( $leader_email, $subject, $message, $headers, $attachments );

				}
			}
		}
	}

	/**
	 * Check both reinvite email and group leader email settings on.
	 *
	 * @return boolean
	 */
	public function is_reinvite_email_and_leader_email_settings_enable() {

		$keys = 'group_user_' . $this->group_leader_id . '_settings';

		// false means send email to group user.
		$leader_settings = get_user_meta( $this->group_leader_id, $keys, true );

		// 'on' means email is enabled.
		$gl_retake_assess_enable = get_option( 'wdm-gr-retake-assessment-enable' );

		if ( 'false' === $leader_settings && 'on' === $gl_retake_assess_enable ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Process retake assessment on self purchase product.
	 *
	 * @param object $order WooCommerce Order object.
	 * @return void
	 */
	public function process_retake_assessment_on_self_purchase( $order ) {

		foreach ( $order->get_items() as $item_id => $item ) {

			// 1 item bought for this product and it is self purchase
			if ( 'New Group' === $item->get_meta( 'Option Selected' ) && 1 === $item->get_quantity() ) {

				$product_id = $item->get_product_id();
				$course_id  = get_post_meta( $product_id, '_related_course', true );

				$links_id = get_the_link_ids_from_courses_id( $course_id );

				// Check.
				// - User already completed assessment.
				// - User limit record exist.
				global $wpdb;
				$users_limit      = $wpdb->prefix . 'tti_users_limit';
				$assessment_table = $wpdb->prefix . 'assessments';

				foreach ( $links_id as $link_id ) {
					$limit    = 1;
					$group_id = 0;

					$results = $wpdb->get_row( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							"SELECT * FROM %i WHERE user_id = %d AND link_id = %s AND status = 1", // phpcs:ignore
							$assessment_table,
							$this->user_id,
							$link_id
						)
					);

					if ( $results ) {

						// user already completed assessment.
						$results = $wpdb->get_row( // phpcs:ignore
							$wpdb->prepare( // phpcs:ignore
								"SELECT * FROM %i WHERE user_id = %s AND data_link = %s", // phpcs:ignore
								$users_limit,
								$this->user_id,
								$link_id
							)
						);

						if ( $results ) {

							// user limit record exits in database.
							$limit = $results->limits;
							$limit++;

							$group_ids = $results->group_id;
							if ( strpos( $group_ids, $group_id ) === false ) {
								$group_ids .= ',' . $this->group_id;
							}

							// Update the limit.
							$this->update_user_limit( $limit, $link_id, $group_ids );

						} else {

							// user limit record do not exists.
							$this->add_user_limit( $limit, $link_id );

						}
					}
				}
			}
		}

	}



}
