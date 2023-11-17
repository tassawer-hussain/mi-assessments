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
class Mi_Assessments_History {

	/**
	 * Contains user id.
	 *
	 * @var integer
	 */
	public $user_id;

	/**
	 * Contains assessment link id.
	 *
	 * @var string
	 */
	public $link_id;

	/**
	 * Contains assessment id.
	 *
	 * @var integer
	 */
	public $assess_id;

	/**
	 * Contains user assessment data.
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Contains if link show or not.
	 *
	 * @var boolean
	 */
	public $show_as_link;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since       1.6
	 * @param integer $user_id contains user id.
	 * @param string  $link_id contains assessment link id.
	 * @param integer $assess_id contains assessment id.
	 * @param boolean $show_link contains if link show or not.
	 */
	public function __construct( $user_id, $link_id, $assess_id, $show_link ) {

		$this->user_id      = $user_id;
		$this->link_id      = $link_id;
		$this->assess_id    = $assess_id;
		$this->show_as_link = $show_link;
		$this->data         = array();

		if ( ! is_admin() ) {

			/* General CSS and JS */
			wp_enqueue_style(
				'mi-assessments-history',
				MI_PUBLIC_URL . 'css/mi-assessments-history.css',
				array(),
				generate_random_string(),
				'all'
			);

			wp_enqueue_script(
				'mi-assessments-history',
				MI_PUBLIC_URL . 'js/mi-assessments-history.js',
				array( 'jquery' ),
				generate_random_string(),
				true
			);

		}

	}

	/**
	 * Function to process the assessment history layout and data.
	 *
	 * @since       1.6
	 */
	public function show_assessment_history() {

		if ( isset( $this->user_id ) && isset( $this->link_id ) ) {

			$this->data = $this->get_user_details();

			$data         = $this->data;
			$assess_id    = $this->assess_id;
			$show_as_link = $this->show_as_link;

			ob_start();                      // start capturing output.
			require_once MI_PUBLIC_PATH . 'partials/mi-assessments-history.php';
			$content = ob_get_contents();    // get the contents from the buffer.
			ob_end_clean();                  // stop buffering and discard contents.

			return $content;

		}
	}

	/**
	 * Function to get user assessment details by user id.
	 *
	 * @since   1.6
	 * @access  public
	 * @return  array|boolean returns assessment history data
	 */
	public function get_user_details() {

		global $wpdb;
		$db_table_name = $wpdb->prefix . 'assessments';

		// Query to get all assessment versions.
		// phpcs:ignore
		$results = $wpdb->get_results(
			$wpdb->prepare( // phpcs:ignore
				// phpcs:ignore
				'SELECT created_at, first_name, last_name, email, gender, company, position_job, version FROM %i
				WHERE `user_id` = %d
				AND `link_id` = %s
				AND `status` = 1
				ORDER BY version DESC',
				$db_table_name,
				$this->user_id,
				$this->link_id
			)
		);

		if ( isset( $results ) && count( $results ) > 0 ) {
			return $results;
		}
		return false;

	}

}
