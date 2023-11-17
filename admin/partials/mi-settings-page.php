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

<?php
/**
 * Fires before settings page content.
 *
 * @since   1.2
 */
do_action( 'ttisi_platform_settings_page_after' );
?>

<div class="assessment-wrap-left">
	<div class="assessment-wrap">

		<h2><?php esc_html_e( 'Settings', 'tti-platform' ); ?></h2>

		<div class="ttiplatform_settings">
			<label for="secret_key"><strong><?php esc_html_e( 'Secret Key', 'tti-platform' ); ?></strong></label>
			<input type="text" name="secret_key" id="secret_key" value="<?php echo esc_attr( get_option( 'ttiplatform_secret_key' ) ); ?>" disabled="disabled" />
			<button class="button button-primary button-large" id="generate_secret_key"><?php esc_html_e( 'Generate', 'tti-platform' ); ?></button>
		</div>

		<?php
		/**
		 * Fires after settings page content (before save button).
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_settings_page_before_save_btn' );
		?>

		<button class="button button-primary button-large" id="save_secret_key"><?php esc_html_e( 'Save', 'tti-platform' ); ?></button>

		<span id="loader_insert_assessment" style="float: none;">
			<img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" width="20" />
		</span>

		<span class="secret_key_response"></span>

		<div class="clear"></div>

		<?php
		/**
		 * Fires after settings page content (after save button but before noitification)
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_settings_page_after_save_btn' );
		?>

		<?php
		$mi_listener_url = get_option( 'ttiplatform_secret_key_listener' );
		if ( isset( $mi_listener_url ) && ! empty( $mi_listener_url ) ) {
			?>
			<div class="return_url">
				<label for="secret_key"><strong><?php esc_html_e( 'Use following URL as a Return URL', 'tti-platform' ); ?></strong></label>
				<?php echo esc_url_raw( $mi_listener_url ); ?>
			</div>
		<?php } ?>
	</div>
</div>
