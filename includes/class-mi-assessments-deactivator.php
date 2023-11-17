<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Deactivator {

	/**
	 * Delete options, and schedule events on deactivation.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// delete listner page.
		$listener_page_id = get_option( 'listener_page_id' );
		wp_delete_post( $listener_page_id, true );

		// clear schedule cron tasks.
		wp_clear_scheduled_hook( 'assessments_status_checker' );
		wp_clear_scheduled_hook( 'assessments_pdf_files_checker' );

	}

}
