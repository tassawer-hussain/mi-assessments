<?php
/**
 * Group settings tab template (Procedural Programming)
 *
 * @since      1.6.5
 * @package    TTI_Platform
 * @subpackage TTI_Platform/includes
 * @author     Presstigers
 */

$leader = get_group_leader_id_from_group_id( $group_id );

if ( ! $leader ) {
	$leader = get_current_user_id();
}

$setting_block_email = get_group_leader_enrollment_email_setting_meta( $leader );

if ( 'false' === $setting_block_email ) {
	$setting_block_email_checkout = 'checked';
	$setting_block_email_class    = 'switch-gp-settings-left-on';
} elseif ( 'true' === $setting_block_email || '' === $setting_block_email ) {
	$setting_block_email_checkout = '';
	$setting_block_email_class    = 'switch-gp-settings-left-off';
} else {
	$setting_block_email_checkout = 'checked';
	$setting_block_email_class    = 'switch-gp-settings-left-on';
}

?>
<div id="tab-4" class="tab-content tab-group-settings">
	<form id="pt-group-form-settings-form">
		<h4>Settings</h4>
		<div class="group-setting-one">
			<div class="pt-left">
				<h5>Send User Enrollment Emails</h5>
			</div>
			<div class="pt-middle">
				<p>Turn on or off the sending of the enrollment emails to your client. You will always get a copy of the enrollment email no mater how this option is selected.</p>
			</div>
			<div class="pt-right">
				<input type="checkbox" id="toggle" class="checkbox-gp-settings" <?php echo esc_attr( $setting_block_email_checkout ); ?> />  
				<div class="enroll-user-btn">
					<span>Off</span>
					<label for="toggle" class="switch-gp-settings <?php echo esc_attr( $setting_block_email_class ); ?>"></label>
					<span>On</span>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>

		<input type="hidden" class="group-leader-gp-settings-glid" data-group_leader_id="<?php echo esc_attr( $leader ); ?>"  value="" />

		<input type="hidden" class="group-leader-gp-settings-gid" data-group_id="<?php echo esc_attr( $group_id ); ?>" value="" />
	</form>
</div>

