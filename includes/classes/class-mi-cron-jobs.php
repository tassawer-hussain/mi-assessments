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
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Cron_Jobs {

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       2.0.0
	 */
	public function __construct() {

		// Create an object of the assessment api class.
		$this->mi_api = new Mi_Assessments_API();

		// Schedule 15 minutes cron.
		add_action( 'assessments_status_checker', array( $this, 'assessments_status_checker_function' ) );

		// Schedule 3 days cron.
		add_action( 'assessments_pdf_files_checker', array( $this, 'assessments_pdf_files_checker_function' ) );

	}

	/**
	 * Check assessment status.
	 *
	 * @since  1.0.0
	 */
	public function assessments_status_checker_function() {

		$loop = fetched_all_mi_assessments_post_type();

		while ( $loop->have_posts() ) :

			$loop->the_post();
			$assesment_id         = get_the_ID();
			$status_assessment    = get_post_meta( $assesment_id, 'status_assessment', true );
			$status_locked        = get_post_meta( $assesment_id, 'status_locked', true );
			$api_service_location = get_post_meta( $assesment_id, 'api_service_location', true );
			$api_key              = get_post_meta( $assesment_id, 'api_key', true );
			$link_id              = get_post_meta( $assesment_id, 'link_id', true );

			// get the assessment by link id.
			$response_body = json_decode( $this->mi_api->mi_get_assessment_by_link( $api_service_location, $api_key, $link_id ) );

			// determine assessment lock status.
			if ( isset( $response_body->locked ) && 1 === $response_body->locked ) {
				$ass_lock_status = 'true';
			} elseif ( isset( $response_body->locked ) && 0 === $response_body->locked ) {
				$ass_lock_status = 'false';
			} else {
				$ass_lock_status = 'true';
			}

			// determine assessment suspend status.
			if ( isset( $response_body->disabled ) && 1 === $response_body->disabled ) {
				$api_link_status = 'Suspended';
			} else {
				$api_link_status = 'Active';
			}

			/* check locked status */
			if ( $status_locked !== $ass_lock_status ) {
				update_post_meta( $assesment_id, 'status_locked', $ass_lock_status );
			}

			/* check suspend status */
			if ( $status_assessment !== $api_link_status ) {
				update_post_meta( $assesment_id, 'status_assessment', $api_link_status );
			}

		endwhile;
	}

	/**
	 * Function to schedule the CRON job.
	 *
	 * @since  1.0.0
	 */
	public function assessments_pdf_files_checker_function() {

		$log_directory = WP_CONTENT_DIR . '/uploads/tti_assessments/*';

		$files = array_filter( glob( $log_directory ), 'is_dir' );

		foreach ( $files as $file ) {

			$original_dir     = $file;
			$link_array       = explode( '/', $file );
			$folder_name_date = end( $link_array );

			// check is 3 day old? If yes, delete it.
			if ( strtotime( $folder_name_date ) < strtotime( '-3 day' ) ) {
				/* Delete that folder */
				$dirname = $original_dir;
				$this->mi_delete_directory( $dirname );
			}
		}
	}

	/**
	 * Function to delete directory function.
	 *
	 * @since  1.0.0
	 * @param string $dir_path contains directory path.
	 */
	public function mi_delete_directory( $dir_path ) {

		if ( is_dir( $dir_path ) ) {

			$objects = scandir( $dir_path );

			foreach ( $objects as $object ) {

				if ( '.' !== $object && '..' !== $object ) {

					if ( 'dir' === filetype( $dir_path . DIRECTORY_SEPARATOR . $object ) ) {
						$this->mi_delete_directory( $dir_path . DIRECTORY_SEPARATOR . $object );
					} else {
						unlink( $dir_path . DIRECTORY_SEPARATOR . $object );
					}
				}
			}

			reset( $objects );
			rmdir( $dir_path );
		}
	}

}
