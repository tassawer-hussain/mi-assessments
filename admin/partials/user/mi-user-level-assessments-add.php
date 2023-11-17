<?php
/**
 * User add assessment tab content
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/admin/partials/user
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!-- add tab content -->

<div class="user-add-assess-form">

	<h3>Add Assessment</h3>

	<label for="organization_user">
		<strong>Title </strong>
		<span style="color: #929292; display: inline-block"> (optional)</span>
	</label>
	<input type="text" name="organization_user" id="organization_user" class="">

	<label for="api_key_user">
		<strong>API Key</strong>
		<span id="api-info" class="ttiinfo"></span>
	</label>
	<input type="text" name="api_key_user" id="api_key_user" class="demoInputBox">
   
	<label for="account_login_user">
		<strong>Account Login</strong>
		<span id="account-info" class="ttiinfo"></span>
	</label>
	<input type="text" name="account_login_user" id="account_login_user" class="demoInputBox">
   
	<label for="api_service_location_user">
		<strong>API Service Location</strong>
		<span id="service-info" class="ttiinfo"></span>
	</label>
	<input type="text" name="api_service_location_user" id="api_service_location_user">

	<label for="survay_location_user">
		<strong>Survey Location</strong>
		<span id="survay-info" class="ttiinfo"></span>
	</label>
	<input type="text" name="survay_location_user" id="survay_location_user" class="demoInputBox">
   
	<label for="tti_link_id_user">
		<strong>Link ID</strong>
		<span id="link-info" class="ttiinfo"></span>
	</label>
	<input type="text" name="tti_link_id_user" id="tti_link_id_user" value="">
   
	<input type="hidden" name="tti_user_id" id="tti_user_id" value="<?php echo esc_attr( $user_id ); ?>">

	<button class="button button-primary button-large" id="validate_assessment_user">Validate Data</button>

	<span id="status-ok"></span>
	<span id="status-error">This Link Login cannot be added. Please provide a valid details.</span>
	<span id="loader"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20"></span>
   
	<div id="afterResponse">

		<!-- Assessment name -->
		<div class="assessment_name_block" id="assessment_name_block" style="display: none;">
			<h3><span id="assessment_name_span_head">Assessment Name :</span> <span id="assessment_name_span"></span></h3>
		</div>

		<!-- Assessment locked status -->
		<div class="assessment_locked_status" id="assessment_locked_status" style="display: none;">
			<h3><span id="assessment_locked_status_head">Assessment Locked Status :</span> <span id="assessment_locked_status_span"></span></h3>
		</div>

		<div class="print_report" id="print_report_settings">
			<h3>Can Print Report?</h3>
			<input type="radio" name="print_report" id="print_report_yes" value="Yes"> <label for="print_report_yes">Yes</label>
			<input type="radio" name="print_report" id="print_report_no" value="No"> <label for="print_report_no">No</label>
		</div>

		<!-- Send report to group leaders -->
		<div class="send_report_to_leader" id="send_report_to_leader">
			<h3>Send report to group leader</h3>
			<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_yes" value="Yes"> <span for="send_rep_group_lead" style="margin-right: 25px;">Yes</span>
			<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_no" value="No"> <span for="send_rep_group_lead">No</span>
		</div>
		<!-- ---------------------------- -->

		<!-- Download report using API -->
		<div class="report_api_check" id="report_api_check">
			<h3>Download report using API</h3>
			<input type="radio" name="report_api_check" id="report_api_check_yes" value="Yes"> <span for="report_api_check" style="margin-right: 25px;">Yes</span>
			<input type="radio" name="report_api_check" id="report_api_check_no" value="No"> <span for="report_api_check">No</span>
		</div>
		<!-- ---------------------------- -->

		<div class="print_report">
			<div class="add_record_assessment">
				<button type="button" class="button button-primary button-large" id="add_user_assessment">Save</button>
				<span id="record_inserted"></span>
				<span id="loader_insert_assessment"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20"></span>
			</div>
		</div>

	</div>

</div>
