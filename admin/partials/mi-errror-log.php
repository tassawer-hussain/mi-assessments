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

<div class="wrap">

	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
</div>

<button class="button refresh-tti-el button-primary button-large" id="publish" value="Update">Refresh Logs</button>

<?php Mi_Error_Log::mi_platform_read_error_log_files(); ?>

<br />

<button class="button refresh-tti-el button-primary button-large" id="publish" value="Update">Refresh Logs</button>
