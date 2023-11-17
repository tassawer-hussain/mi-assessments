<?php
/**
 * Edit Assessment template
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

<?php
$back_to_url = add_query_arg(
	array(
		'page'    => 'tti-profile-assessment-page',
		'user_id' => $user_id,
	),
	admin_url( 'users.php', 'https' )
);
?>

<div class="tti-plat-user-ass-edit">

	<a href="<?php echo esc_attr( esc_url_raw( $back_to_url, 'https' ) ); ?>">Back To Assessments</a>

	<p>Please edit the assessment here</p>

	<div class="user-add-assess-form">
		<h3>Edit Assessment</h3>

		<label for="organization_user">
			<strong>Title </strong>
			<span style="color: #929292; display: inline-block"> (optional)</span>
		</label>
		<input type="text" name="organization_user" id="organization_user" class="" value="<?php echo esc_html( $list_data['title'] ); ?>">

		<label for="api_key_user">
			<strong>API Key</strong>
			<span id="api-info" class="tti-info"></span>
		</label>
		<input type="text" name="api_key_user" id="api_key_user" class="demoInputBox" value="<?php echo esc_html( $list_data['api_key'] ); ?>" >

		<label for="account_login_user">
			<strong>Account Login</strong>
			<span id="account-info" class="tti-info"></span>
		</label>
		<input type="text" name="account_login_user" id="account_login_user" class="demoInputBox" value="<?php echo esc_html( $list_data['account_login'] ); ?>">

		<label for="api_service_location_user">
			<strong>API Service Location</strong>
			<span id="service-info" class="tti-info"></span>
		</label>
		<input type="text" name="api_service_location_user" id="api_service_location_user" value="<?php echo esc_html( $list_data['api_service_location'] ); ?>" >

		<label for="survay_location_user">
			<strong>Survey Location</strong>
			<span id="survay-info" class="tti-info"></span>
		</label>
		<input type="text" name="survay_location_user" id="survay_location_user" class="demoInputBox" value="<?php echo esc_html( $list_data['survey_location'] ); ?>" >

		<label for="tti_link_id_user">
			<strong>Link ID</strong>
			<span id="link-info" class="tti-info"></span>
		</label>
		<input type="text" name="tti_link_id_user" id="tti_link_id_user" value="<?php echo esc_html( $list_data['link_id'] ); ?>">

		<input type="hidden" name="tti_user_id" id="tti_user_id" value="<?php echo esc_attr( $user_id ); ?>">
		<input type="hidden" name="assessment_name" id="assessment_name" value="<?php echo esc_attr( $list_data['name'] ); ?>">
		<input type="hidden" name="assessment_status_hidden" id="assessment_status_hidden" value="<?php echo esc_attr( $list_data['status_assessment'] ); ?>">
		<input type="hidden" name="report_view_id" id="report_view_id" value="<?php echo esc_attr( $list_data['report_view_id'] ); ?>">
		<input type="hidden" name="assessment_locked_status_hidden" id="assessment_locked_status_hidden" value="<?php echo esc_attr( $list_data['status_locked'] ); ?>">

		<div id="afterResponse" style="display: block;">
			<!-- Assessment locked status -->
			<div class="assessment_locked_status" id="assessment_locked_status" >
				<h3>
					<span id="assessment_locked_status_head">Assessment Locked Status :</span>
					<span id="assessment_locked_status_span"><?php echo esc_html( $list_data['status_locked'] ); ?></span>
				</h3>
			</div>

			<div class="print_report" id="print_report_settings">
				<h3>Can Print Report?</h3>
				<input
					type="radio"
					name="print_report" 
					<?php echo esc_attr( ( 'Yes' === $list_data['print_report'] ) ? 'checked ' : '' ); ?>
					id="print_report_yes"
					value="Yes">
				<label for="print_report_yes">Yes</label>

				<input
					type="radio"
					name="print_report" 
					<?php echo esc_attr( ( 'No' === $list_data['print_report'] ) ? 'checked ' : '' ); ?>
					id="print_report_no" value="No">
				<label for="print_report_no">No</label>
			</div>

			<!-- Send report to group leaders -->
			<div class="send_report_to_leader" id="send_report_to_leader">
				<h3>Send report to group leader</h3>
				<input
					type="radio"
					name="send_rep_group_lead"
					id="send_rep_group_lead_yes"
					value="Yes"
					<?php echo esc_attr( ( 'Yes' === $list_data['send_rep_group_lead'] ) ? 'checked ' : '' ); ?>>
				<span for="send_rep_group_lead" style="margin-right: 25px;" >Yes</span>

				<input
					type="radio"
					name="send_rep_group_lead"
					id="send_rep_group_lead_no"
					value="No"
					<?php echo esc_attr( ( 'No' === $list_data['send_rep_group_lead'] ) ? 'checked ' : '' ); ?>>
				<span for="send_rep_group_lead" >No</span>
			</div>
			<!-- ---------------------------- -->

			<!-- Send report to group leaders -->
			<div class="report_api_check" id="report_api_check">
				<h3>Download report using API</h3>
				<input
					type="radio"
					name="report_api_check"
					id="report_api_check_yes"
					value="Yes"
					<?php echo esc_attr( ( 'Yes' === $list_data['report_api_check'] ) ? 'checked ' : '' ); ?>>
				<span for="report_api_check" style="margin-right: 25px;" >Yes</span>

				<input
					type="radio"
					name="report_api_check"
					id="report_api_check_no"
					value="No"
					<?php echo esc_attr( ( 'No' === $list_data['report_api_check'] ) ? 'checked ' : '' ); ?>>
				<span for="report_api_check" >No</span>
			</div>
			<!-- ---------------------------- -->
		</div>

		<button class="button button-primary button-large" id="update_assessment_user">Update</button>
		<span id="status-ok"></span>
		<span id="status-error">Error. Please provide a valid details.</span>
		<span id="status-success">Assessment details updated successfully.</span>
		<span id="loader"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20"></span>
	</div>

</div>
