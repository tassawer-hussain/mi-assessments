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

<br />
<div class="accordion">
	<label class="wdm-switch">
		<input
			type="checkbox"
			name="wdm-gr-retake-assessment-enable"
			<?php echo ( 'off' !== $gl_retake_assess_enable ) ? 'checked' : ''; ?>
		>
		<span class="wdm-slider round"></span>
	</label>
	<b><?php esc_html_e( 'When Group Leader allow user to retake assessment (Group Leader)', 'tti-platform' ); ?></b>
</div>

<div class="panel">
	<br>
	<table>
		<tr>
			<td class="wdm-label">
				<label for="wdm-gr-retake-assessment"><?php esc_html_e( 'Subject : ', 'tti-platform' ); ?></label>
			</td>
			<td>
				<input type="text" name="wdm-gr-retake-assessment" id="wdm-gr-retake-assessment" size="50" value="<?php echo esc_attr( $gl_retake_assess_email_sub ); ?>">
				<span class="wdm-help-txt"><?php esc_html_e( 'Enter Subject for Email sent to User allowed to retake assessment <br/> Default : leave blank', 'tti-platform' ); ?></span>
			</td>
		</tr>
		<tr>
			<td class="wdm-label">
				<label for="wdm-u-add-gr-body"><?php esc_html_e( 'Body : ', 'tti-platform' ); ?></label>
			</td>
			<td>
				<?php
				$editor_settings = array(
					'media_buttons'    => false,
					'drag_drop_upload' => false,
					'textarea_rows'    => 15,
					'textarea_name'    => 'wdm-u-add-gr-body-retake-assess',
				);
				wp_editor( stripslashes( $gl_retake_assess_email_body ), 'wdm-u-add-gr-body-retake-assess', $editor_settings );
				?>
				<span class="wdm-help-txt"><?php esc_html_e( 'Enter Body for Email sent to User allowed to retake assessment <br/> Default : leave blank', 'tti-platform' ); ?></span>
			</td>
			<td class="wdm-var-sec">
				<div>
					<span class="wdm-var-head"><?php esc_html_e( 'Available Variables', 'tti-platform' ); ?></span>
					<ul>
						<li><b>{assessment_title}</b> : <?php esc_html_e( 'Displays Assessment Title', 'tti-platform' ); ?></li>
						<li><b>{group_leader_name}</b> : <?php esc_html_e( 'Displays Group Leader Name', 'tti-platform' ); ?></li>
						<li><b>{user_first_name}</b> : <?php esc_html_e( "Displays User's First Name", 'tti-platform' ); ?></li>
						<li><b>{user_last_name}</b> : <?php esc_html_e( "Displays User's Last Name", 'tti-platform' ); ?></li>
						<li><b>{user_email}</b> : <?php esc_html_e( "Displays User's Email", 'tti-platform' ); ?></li>
						<li><b>{login_url}</b> : <?php esc_html_e( 'Displays Login URL', 'tti-platform' ); ?></li>
						<li><b>{reset_password}</b> : <?php esc_html_e( 'Displays Reset Password link for user', 'tti-platform' ); ?></li>
						<li><b>{site_name}</b> : <?php esc_html_e( 'Displays Site Name', 'tti-platform' ); ?></li>
						<li><b>{course_list}</b> : <?php esc_html_e( 'Displays Course List', 'tti-platform' ); ?></li>
					</ul>
				</div>
			</td>
		</tr>
	</table><br>
</div><br>
