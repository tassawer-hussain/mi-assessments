<?php
/**
 * Class to handle error log.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Class to handle error log.
 *
 * This class defines all code necessary to log the error handling.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_LDGR_Admin_Functionality {

	/**
	 * Define the constructor
	 *
	 * @since  1.6.1
	 */
	public function __construct() {

		// Retake assessment email configuration in admin.
		add_action( 'ldgr_action_email_settings_form_end', array( $this, 'show_retake_assess_email_configuration_in_admin' ) );

		// filter the $_POST global array.
		$posted_data = filter_input_array( INPUT_POST );
		if ( isset( $posted_data['sbmt_wdm_gr_email_setting'] ) ) {
			$this->save_retake_assess_email_configuration_in_admin_option();
		}
	}

	/**
	 * Function to show retake email options in Learndash board email settings. In admin panel.
	 *
	 * @since  1.6.1
	 */
	public function show_retake_assess_email_configuration_in_admin() {

		$gl_retake_assess_enable     = get_option( 'wdm-gr-retake-assessment-enable' );
		$gl_retake_assess_email_sub  = get_option( 'wdm-gr-retake-assessment' );
		$gl_retake_assess_email_body = get_option( 'wdm-u-add-gr-body-retake-assess' );

		require_once MI_ADMIN_PATH . 'partials/ld-group-registration/retake-assessment-email-settings.php';

	}

	/**
	 * Function to save retake email settings.
	 *
	 * @since  1.6.1
	 */
	public function save_retake_assess_email_configuration_in_admin_option() {

		// filter the $_POST global array.
		$posted_data = filter_input_array( INPUT_POST );

		$gl_retake_assess_enable     = isset( $posted_data['wdm-gr-retake-assessment-enable'] ) ? 'on' : 'off';
		$gl_retake_assess_email_sub  = $posted_data['wdm-gr-retake-assessment'];
		$gl_retake_assess_email_body = $posted_data['wdm-u-add-gr-body-retake-assess'];

		update_option( 'wdm-gr-retake-assessment-enable', trim( $gl_retake_assess_enable ) );
		update_option( 'wdm-gr-retake-assessment', trim( $gl_retake_assess_email_sub ) );
		update_option( 'wdm-u-add-gr-body-retake-assess', trim( $gl_retake_assess_email_body ) );
	}

}
