<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="assessment-wrap-left">
	<div class="assessment-wrap">

		<h2><?php esc_html_e( 'Add Assessment', 'tti-platform' ); ?></h2>

		<?php
			/**
			 * Fires before add assessment form first field
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_add_assessment_form_before_first_field' );
		?>

		<label for="organization">
			<strong><?php esc_html_e( 'Title ', 'tti-platform' ); ?></strong>
			<span style="color: #929292; display: inline-block"> (optional)</span>
		</label>
		<input type="text" name="organization" id="organization" />

		<label for="api_key">
			<strong><?php esc_html_e( 'API Key', 'tti-platform' ); ?></strong>
			<span id="api-info" class="ttiinfo"></span>
		</label>
		<input type="text" name="api_key" id="api_key" class="demoInputBox" />

		<label for="account_login"><strong>
			<?php esc_html_e( 'Account Login', 'tti-platform' ); ?></strong>
			<span id="account-info" class="ttiinfo"></span>
		</label>
		<input type="text" name="account_login" id="account_login" class="demoInputBox" />

		<label for="api_service_location">
			<strong><?php esc_html_e( 'API Service Location', 'tti-platform' ); ?></strong>
			<span id="service-info" class="ttiinfo"></span>
		</label>
		<input type="text" name="api_service_location" id="api_service_location" />

		<label for="survay_location"><strong>
			<?php esc_html_e( 'Survey Location', 'tti-platform' ); ?></strong>
			<span id="survay-info" class="ttiinfo"></span>
		</label>
		<input type="text" name="survay_location" id="survay_location" class="demoInputBox" />

		<label for="tti_link_id">
			<strong><?php esc_html_e( 'Link ID', 'tti-platform' ); ?></strong>
			<span id="link-info" class="ttiinfo"></span>
		</label>
		<input type="text" name="tti_link_id" id="tti_link_id" value=""  />  

		<?php
			/**
			 * Fires after add assessment form last field (before Validate button)
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_add_assessment_form_after_last_field' );
		?>

		<button class="button button-primary button-large" id="validate_assessment">
			<?php esc_html_e( 'Validate Data', 'tti-platform' ); ?>
		</button>
		<span id="status-ok"></span>
		<span id="status-error"><?php esc_html_e( 'This Link Login cannot be added. Please provide a valid details.', 'tti-platform' ); ?></span>
		<span id="loader"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20" /></span>
	</div>

	<div id="afterResponse">
		<?php
			/**
			 * Fires before assessments links dropdown
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_before_assessments_links' );
		?>

		<?php
			/**
			 * Fires after assessments links dropdown
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_after_assessments_links' );
		?>

		<!-- Assessment name -->
		<div class="assessment_name_block" id="assessment_name_block" style="display: none;">
			<h3><span id="assessment_name_span_head">Assessment Name :</span> <span id="assessment_name_span"></span></h3>
		</div>

		<!-- Assessment locked status -->
		<div class="assessment_locked_status" id="assessment_locked_status" style="display: none;">
			<h3><span id="assessment_locked_status_head">Assessment Locked Status :</span> <span id="assessment_locked_status_span"></span></h3>
		</div>

		<div class="print_report" id="print_report_settings">
			<h3><?php esc_html_e( 'Can Print Report?', 'tti-platform' ); ?></h3>

			<input type="radio" name="print_report" id="print_report_yes" value="Yes" />
			<label for="print_report_yes"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></label>

			<input type="radio" name="print_report" id="print_report_no" value="No" />
			<label for="print_report_no"><?php esc_html_e( 'No', 'tti-platform' ); ?></label>
		</div>

		<!-- Send report to group leaders -->
		<div class="send_report_to_leader" id="send_report_to_leader" >
			<h3><?php esc_html_e( 'Send report to group leader', 'tti-platform' ); ?></h3>

			<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_yes" value="Yes" />
			<span for="send_rep_group_lead" style="margin-right: 25px;"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></span>

			<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_no" value="No" />
			<span for="send_rep_group_lead"><?php esc_html_e( 'No', 'tti-platform' ); ?></span>
		</div>
		<!-- ---------------------------- -->

		<!-- Report download option -->
		<div class="report_api_check" id="report_api_check" >
			<h3><?php esc_html_e( 'Download report using API', 'tti-platform' ); ?></h3>

			<input type="radio" name="report_api_check" id="report_api_check_yes" value="Yes" />
			<span for="report_api_check" style="margin-right: 25px;"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></span>

			<input type="radio" name="report_api_check" id="report_api_check_no" value="No" />
			<span for="report_api_check"><?php esc_html_e( 'No', 'tti-platform' ); ?></span>
		</div>
		<!-- ---------------------------- -->

		<div class="print_report">
			<?php
			/**
			 * Fires before Save assessment button
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_before_assessments_save_button' );
			?>
			<div class="add_record_assessment">
				<button class="button button-primary button-large" id="add_assessment"><?php esc_html_e( 'Save', 'tti-platform' ); ?></button>
				<span id="record_inserted"></span>
				<span id="loader_insert_assessment"><img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20" /></span>
			</div>
		</div>

	</div>
</div>

<div class="assessment-wrap-right">
	<?php
	/**
	 * Fires before assessments result
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_before_assessments_result' );
	?>
	<!-- <div id="assessment-result"></div> -->

	<?php
	/**
	 * Fires after assessments result
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_after_assessments_result' );
	?>
</div>
