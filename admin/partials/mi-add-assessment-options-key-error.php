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

<div class="error_popup_tti">
	<h2>
		<?php esc_html_e( 'You must has to generate a secret key to add new assessments.', 'mi-assessments' ); ?>
		<a href="<?php echo esc_attr( esc_url_raw( admin_url( 'edit.php?post_type=tti_assessments&page=ttiplatform_settings', 'https' ), 'https' ) ); ?>">
			<?php esc_html_e( 'Click here to generate key', 'mi-assessments' ); ?>
		</a>
	</h2>
</div>
