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

<!-- Change Add New button permalink -->
<script type="text/javascript">
	jQuery('#wpbody-content .wrap h1+a').attr("href", "<?php echo esc_url_raw( admin_url( 'edit.php?post_type=tti_assessments&page=ttiplatform_api', 'https' ), 'https' ); ?>");
</script>

<div class="assessment-wrap">
	<?php
	/**
	 * Fires before assessment admin meta page
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_assessment_meta_box_admin_before' );
	?>

	<label for="organization"><strong><?php esc_html_e( 'Title', 'tti-platform' ); ?></strong></label>
	<input type="text" name="organization" id="organization" value="<?php echo esc_attr( $organization ); ?>" >

	<label for="api_key"><strong><?php esc_html_e( 'API Key', 'tti-platform' ); ?></strong></label>
	<input type="text" name="api_key" id="api_key" value="<?php echo esc_attr( $api_key ); ?>" >

	<label for="account_login"><strong><?php esc_html_e( 'Account Login', 'tti-platform' ); ?></strong></label>
	<input type="text" name="account_login" id="account_login" value="<?php echo esc_attr( $account_login ); ?>" >

	<label for="api_service_location"><strong><?php esc_html_e( 'API Service Location', 'tti-platform' ); ?></strong></label>
	<input type="text" name="api_service_location" id="api_service_location" value="<?php echo esc_attr( $api_service_location ); ?>" >

	<label for="survay_location"><strong><?php esc_html_e( 'Survey Location', 'tti-platform' ); ?></strong></label>
	<input type="text" name="survay_location" id="survay_location" value="<?php echo esc_attr( $survay_location ); ?>"  />

	<label for="link_id"><strong><?php esc_html_e( 'Link ID', 'tti-platform' ); ?></strong></label>
	<input type="text" name="link_id" id="link_id" value="<?php echo esc_attr( $link_id ); ?>"  />

	<label for="assessment_status"><strong><?php esc_html_e( 'Assessment Status', 'tti-platform' ); ?></strong></label>
	<input type="text" name="assessment_status" id="assessment_status" value="<?php echo esc_attr( $status_assessment ); ?>" disabled="disabled" />

	<?php
	/**
	 * Fires after assessment admin meta box last field
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_assessment_meta_box_admin_after_input_boxes' );
	?>

	<?php if ( 'true' === $can_print_assessment ) { ?>

	<label><?php esc_html_e( 'Can Print Report?', 'tti-platform' ); ?> </label>
	<input type="radio" name="print_report" id="print_report_yes" value="Yes" 
		<?php echo esc_attr( ( 'Yes' === $print_report ) ? 'checked' : '' ); ?>
		/>
	<span for="print_report_yes" style="margin-right: 10px;"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></span>

	<input type="radio" name="print_report" id="print_report_no" value="No" 
		<?php echo esc_attr( ( 'No' === $print_report ) ? 'checked' : '' ); ?>
		/>
	<span for="print_report_no"><?php esc_html_e( 'No', 'tti-platform' ); ?></span>

	<br /> <br />

	<?php } ?>

	<!-- Send report to group leaders -->
	<label><?php esc_html_e( 'Send report to group leader', 'tti-platform' ); ?> </label>
	<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_yes" value="Yes" 
	<?php echo esc_attr( ( 'Yes' === $send_rep_group_lead ) ? 'checked' : '' ); ?>
		/>
	<span for="send_rep_group_lead" style="margin-right: 10px;"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></span>

	<input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_no" value="No" 
	<?php echo esc_attr( ( 'No' === $send_rep_group_lead ) ? 'checked' : '' ); ?>
		/>
	<span for="send_rep_group_lead"><?php esc_html_e( 'No', 'tti-platform' ); ?></span>
	<!-- ---------------------------- -->

	<br /> <br />
	<!-- Send report to group leaders -->

	<label><?php esc_html_e( 'Download report using API', 'tti-platform' ); ?> </label>
	<input type="radio" name="report_api_check" id="send_rep_group_lead_yes" value="Yes" 
	<?php echo esc_attr( ( 'Yes' === $report_api_check ) ? 'checked' : '' ); ?>
		/>
	<span for="report_api_check" style="margin-right: 10px;"><?php esc_html_e( 'Yes', 'tti-platform' ); ?></span>

	<input type="radio" name="report_api_check" id="send_rep_group_lead_no" value="No" 
	<?php echo esc_attr( ( 'No' === $report_api_check ) ? 'checked' : '' ); ?>
		/>
	<span for="report_api_check"><?php esc_html_e( 'No', 'tti-platform' ); ?></span>
	<!-- ---------------------------- -->

	<br /> <br /> <br />

	<?php
	/**
	 * Fires after assessment admin meta box last field
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_assessment_meta_box_admin_after' );

	/**
	 * Fires after assessment admin meta page before print response
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_assessment_meta_box_admin_before_print_response' );

	/**
	 * Fires after assessment admin meta page after print response
	 *
	 * @since   1.2
	 */
	do_action( 'ttisi_platform_assessment_meta_box_admin_after_print_response' );
	?>

</div>
