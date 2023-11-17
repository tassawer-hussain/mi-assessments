<?php
/**
 * Class handles the mapping page functionality
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
 * This class is used to define main AJAX based functionality in WordPress admin user's profile.
 *
 * @since      1.7.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Mapping_Handler {

	/**
	 * String contains user id
	 *
	 * @var string
	 */
	public $user_id;

	/**
	 * Function to validate given user assessment.
	 *
	 * @since   1.7.0
	 */
	public function tti_update_mapping_data() {

		$params = array();

		// phpcs:ignore
		if ( isset( $_POST['data'] ) ) {

			parse_str( $_POST['data'], $params ); // phpcs:ignore

			/* sanitize array */
			$params = $this->sanitize_array( $params );
			$result = update_option( 'tti_platform_mapping_data', $params );
		}

		$response['status'] = 'success';

		echo wp_json_encode( $response );
		exit;
	}

	/**
	 * Recursive sanitation for text or array
	 *
	 * @param array $array Can be array or string.
	 * @since  1.7.0
	 * @return mixed
	 */
	public function sanitize_array( &$array ) {

		foreach ( $array as &$value ) {

			if ( ! is_array( $value ) ) {
				// sanitize if value is not an array.
				$value = sanitize_text_field( $value );
			} else {
				// go inside this function again.
				$this->sanitize_array( $value );
			}
		}
		return $array;
	}

	/**
	 * Return the mapping saved data
	 *
	 * @since  1.7.0
	 * @return array
	 */
	public function return_mapping_data() {

		$result = get_option( 'tti_platform_mapping_data' );
		return $result;

	}

}
