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
 * API URL: https://api.ttiadmin.com/api/documentation
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_API {

	/**
	 * Listener URL.
	 *
	 * @var string
	 */
	private $mi_listener_url;

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       2.0.0
	 */
	public function __construct() {

		// set the listener url.
		$this->mi_listener_url = get_option( 'ttiplatform_secret_key_listener' );
	}

	/**
	 * Get more details about the API response code.
	 *
	 * @param int $code API response code.
	 * @return string
	 */
	private function mi_api_response_code_description( $code ) {

		$message = 'Response Code: ';

		switch ( $code ) {
			case 301:
				$message .= $code . ' - Resource was moved permanently.';
				break;
			case 302:
				$message .= $code . ' - Resource was moved temporarily.';
				break;
			case 403:
				$message .= $code . ' - Forbidden. Usually due to an invalid authentication.';
				break;
			case 404:
				$message .= $code . ' - Resource not found.';
				break;
			case 500:
				$message .= $code . ' - Internal server error.';
				break;
			case 503:
				$message .= $code . ' - Service unavailable.';
				break;
			default:
				$message .= 'Unknown - No response from the API.';
		}

		return esc_html( $message );

	}

	/**
	 * Function to get the assessment by link ID.
	 *
	 * @since   1.3.2
	 * @param array  $url contains api service location url.
	 * @param string $api_key contains access token for api.
	 * @param string $method contain api method type GET|POST|PUT.
	 * @param string $payload contain boday content.
	 *
	 * @return array Array of call status, message and content if received.
	 */
	public function mi_send_api_request( $url, $api_key, $method, $payload = '' ) {

		$headers = array(
			'Authorization' => $api_key,
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
		);

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 25000,
		);

		if ( ! empty( $payload ) ) {
			$args['body']        = wp_json_encode( $payload );
			$args['data_format'] = 'body';
		}

		$api_response = array();
		$response     = wp_safe_remote_request( $url, $args );

		// log event info.
		$log_details = array(
			'ACTION' => 'Sending request to API.',
			'URL'    => $url,
			'ARGs'   => $this->mi_encrypt_api_key_before_using_in_log( $args ),
		);

		// early bail if we encounter an error in API call.
		if ( is_wp_error( $response ) ) {

			$api_response['status']  = 'error';
			$api_response['message'] = $response->get_error_message();

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'error' );

			return wp_json_encode( $api_response );

		}

		// response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// API response is other than 200 OK – Request was successful.
		if ( isset( $response_code ) && 200 !== $response_code && 201 !== $response_code && 204 !== $response_code ) {

			$api_response['status']  = 'error';
			$api_response['message'] = $this->mi_api_response_code_description( $response_code );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'error' );

			return wp_json_encode( $api_response );

		}

		// Get response body.
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		// Some calls return empty body (no content).
		if ( isset( $response_body ) ) {

			$api_response['status']  = 'success';
			$api_response['message'] = __( 'Received content.', 'mi-assessments' );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'success' );

			return wp_json_encode( $response_body );

		} else {

			$api_response['status']  = 'success';
			$api_response['message'] = __( 'Empty body content.', 'mi-assessments' );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'success' );

			return wp_json_encode( $api_response );

		}

	}

	/**
	 * Function to get the assessment by link ID.
	 *
	 * @since   1.3.2
	 * @param array  $url contains api service location url.
	 * @param string $api_key contains access token for api.
	 * @param string $method contain api method type GET|POST|PUT.
	 *
	 * @return array Array of call status, message and content if received.
	 */
	public function mi_download_pdf_report( $url, $api_key, $method ) {

		$headers = array(
			'Authorization'             => $api_key,
			'Accept'                    => 'application/pdf',
			'Content-Type'              => 'application/pdf',
			'Content-Transfer-Encoding' => 'binary',
		);

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 25000,
		);

		$api_response = array();
		$response     = wp_safe_remote_request( $url, $args );

		// log event info.
		$log_details = array(
			'ACTION' => 'Downlaoding PDF Report.',
			'URL'    => $url,
			'ARGs'   => $this->mi_encrypt_api_key_before_using_in_log( $args ),
		);

		// early bail if we encounter an error in API call.
		if ( is_wp_error( $response ) ) {

			$api_response['status']  = 'error';
			$api_response['message'] = $response->get_error_message();

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'error' );

			return $response;

		}

		// response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// API response is other than 200 OK – Request was successful.
		if ( isset( $response_code ) && 200 !== $response_code && 201 !== $response_code && 204 !== $response_code ) {

			$api_response['status']  = 'error';
			$api_response['message'] = $this->mi_api_response_code_description( $response_code );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'error' );

			return $response;

		}

		// Get response body.
		$response_body = wp_remote_retrieve_body( $response );

		// Some calls return empty body (no content).
		if ( isset( $response_body ) ) {

			$api_response['status']  = 'success';
			$api_response['message'] = __( 'Received content.', 'mi-assessments' );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'success' );

			return $response_body;

		} else {

			$api_response['status']  = 'success';
			$api_response['message'] = __( 'Empty body content.', 'mi-assessments' );

			// log the details.
			Mi_Error_Log::put_error_log( array_merge( $log_details, $api_response ), 'array', 'success' );

			return $response_body;

		}

	}


	/**
	 * Function to get the assessment by link ID.
	 *
	 * @since   1.3.2
	 * @param array  $api_service_location contains api service location url.
	 * @param string $api_key contains access token for api.
	 * @param string $link_id contain link id of assessment.
	 */
	public function mi_get_assessment_by_link( $api_service_location, $api_key, $link_id ) {

		/* API v 3.0 url */
		$url = esc_url_raw( $api_service_location . '/api/v3/links/' . $link_id );

		return $this->mi_send_api_request( $url, $api_key, 'GET' );

	}

	/**
	 * Function to get the report meta data by report id.
	 *
	 * @since   2.0.0
	 * @param string $report_id contain reprot id of assessment.
	 * @param array  $api_service_location contains api service location url.
	 * @param string $api_key contains access token for api.
	 */
	public function mi_get_report_metadata( $report_id, $api_service_location, $api_key ) {

		/* API v 3.0 url */
		$url = esc_url_raw( $api_service_location . '/api/v3/reportviews/' . $report_id );

		$api_response = $this->mi_send_api_request( $url, $api_key, 'GET' );

		/**
		 * Filter to update assessment report metadata
		 *
		 * @since  1.2
		 */
		$api_response = apply_filters( 'ttisi_platform_get_report_metadata', $api_response );
		return $api_response;

	}

	/**
	 * Update the return URL of all assessments.
	 *
	 * @return void
	 */
	public function mi_update_return_url_of_all_assessments() {

		// Early bail. If listener URL is not set or empty.
		if ( ! isset( $this->mi_listener_url ) || empty( $this->mi_listener_url ) ) {

			echo wp_json_encode( array( 'status' => 'return_url' ) );
			wp_die();

		}

		// log event info.
		$log_details = array(
			'ACTION' => 'Going to update Return & WebHook URLs of all assessments.',
		);
		Mi_Error_Log::put_error_log( $log_details, 'array', 'primary' );

		// Update Return URL when new assessment added.
		$loop = fetched_all_mi_assessments_post_type();

		while ( $loop->have_posts() ) :

			$loop->the_post();
			$link_id              = get_post_meta( get_the_ID(), 'link_id', true );
			$api_key              = get_post_meta( get_the_ID(), 'api_key', true );
			$api_service_location = get_post_meta( get_the_ID(), 'api_service_location', true );

			// log event info.
			$log_details = array(
				'ACTION' => 'Updating Return & WebHook URLs of ' . get_the_title( get_the_ID() ),
			);
			Mi_Error_Log::put_error_log( $log_details, 'array', 'primary' );

			/* API v3.0 url */
			$url = $api_service_location . '/api/v3/links/' . $link_id;

			$payload = array(
				'return_url'     => $this->mi_listener_url,
				'webhook_url'    => $this->mi_listener_url,
				'express_return' => true,
			);

			// send request to API.
			$this->mi_send_api_request( $url, $api_key, 'PUT', $payload );

		endwhile;

	}

	/**
	 * Set the return URL of newly created assessment.
	 *
	 * @since   2.0.0
	 * @param int $post_id ID of newly created assessment.
	 * @return void
	 */
	public function mi_set_return_url_of_newly_created_assessment( $post_id ) {

		$log_details = array(
			'ACTION' => 'Setting up the Return & WebHook URLs of ' . get_the_title( $post_id ),
		);

		// Early bail. If listener URL is not set or empty.
		if ( ! isset( $this->mi_listener_url ) || empty( $this->mi_listener_url ) ) {

			// log event info.
			$log_details['message'] = 'Secret Key or Return URL is not set. Please set them first.';
			Mi_Error_Log::put_error_log( $log_details, 'array', 'error' );

			echo wp_json_encode( array( 'status' => 'return_url' ) );
			wp_die();

		}

		$link_id              = get_post_meta( $post_id, 'link_id', true );
		$api_key              = get_post_meta( $post_id, 'api_key', true );
		$api_service_location = get_post_meta( $post_id, 'api_service_location', true );

		// log event info.
		Mi_Error_Log::put_error_log( $log_details, 'array', 'primary' );

		/* API v3.0 url */
		$url = $api_service_location . '/api/v3/links/' . $link_id;

		$payload = array(
			'return_url'     => $this->mi_listener_url,
			'webhook_url'    => $this->mi_listener_url,
			'express_return' => true,
		);

		// send request to API.
		$this->mi_send_api_request( $url, $api_key, 'PUT', $payload );

	}

	/**
	 * Update the return URL of all users level assessments.
	 *
	 * @return void
	 */
	public function mi_update_return_url_of_user_level_assessments() {

		// Early bail. If listener URL is not set or empty.
		if ( ! isset( $this->mi_listener_url ) || empty( $this->mi_listener_url ) ) {

			echo wp_json_encode( array( 'status' => 'return_url' ) );
			wp_die();

		}

		$users = get_users(
			array(
				'meta_key' => 'user_assessment_data', // phpcs:ignore
			)
		);

		if ( $users ) {
			// log event info.
			$log_details = array(
				'ACTION' => 'Going to update Return & WebHook URLs of all user level assessments.',
			);
			Mi_Error_Log::put_error_log( $log_details, 'array', 'primary' );
		}

		foreach ( $users as $user ) {

			$assess_user_details = get_user_meta( $user->ID, 'user_assessment_data', true );

			if ( $assess_user_details ) {

				// log event info. log the details.
				$log_details = array(
					'ACTION' => 'Updating Return & WebHook URLs for user level assessments of following user.',
					'user'   => $user->data,
				);
				Mi_Error_Log::put_error_log( $log_details, 'array', 'primary' );

				$assess_user_details = unserialize( $assess_user_details ); // phpcs:ignore

				foreach ( $assess_user_details as $key => $value ) {

					$link_id              = $value['link_id'];
					$api_key              = $value['api_key'];
					$api_service_location = $value['api_service_location'];

					/* API v3.0 url */
					$url = $api_service_location . '/api/v3/links/' . $link_id;

					$payload = array(
						'return_url'     => $this->mi_listener_url,
						'webhook_url'    => $this->mi_listener_url,
						'express_return' => true,
					);

					// send request to API.
					$this->mi_send_api_request( $url, $api_key, 'PUT', $payload );
				}
			}
		}

	}

	/**
	 * Encrypt the API key to use in the log.
	 *
	 * @since   2.0.0
	 * @param array $args API arguments array.
	 * @return array
	 */
	public function mi_encrypt_api_key_before_using_in_log( $args ) {

		$string = $args['headers']['Authorization'];
		$length = strlen( $string );

		// Keep the first 4 characters.
		$masked_string = substr( $string, 0, 4 );

		// Mask characters between the first 4 and last 4.
		for ( $i = 4; $i < $length - 4; $i++ ) {
			$masked_string .= '*';
		}

		// Keep the last 4 characters.
		$masked_string .= substr( $string, -4 );

		// UPDATE: 1RPZ****************************d687.
		$args['headers']['Authorization'] = $masked_string;

		return $args;

	}

}
