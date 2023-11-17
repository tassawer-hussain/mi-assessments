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
 * This is the best way to include the WordPress files. NEED REWORK - TASSAWER.
 *
 * WILL CHECK IT LATER.
 *
 * require_once ABSPATH . 'wp-load.php';
 * require_once ABSPATH . 'wp-config.php';
 * require_once ABSPATH . 'wp-includes/load.php';
 * require_once ABSPATH . 'wp-includes/plugin.php';
 */

require_once '../../../../../wp-load.php';
require_once '../../../../../wp-config.php';
require_once '../../../../../wp-includes/load.php';
require_once '../../../../../wp-includes/plugin.php';

wp_head();
?>

<script type="text/javascript">
	jQuery( document ).ready(function() {
		var cl = $('#ttisi-shortcode-generator-block').clone();
		jQuery('body').empty();
		jQuery('body').append(cl);
		jQuery('jdiv').remove();
	});
</script>

<body style="background: transparent !important;">

<!-- TTISI shortcode generator block -->
<div id="ttisi-shortcode-generator-block">

	<!-- TTISI CSS Style -->
	<style>
		#ttisi-shortcode-generator-block button, 
		#ttisi-shortcode-generator-block h3,
		#ttisi-shortcode-generator-block strong {
			font-family: Arial !important;
		}
		#ttisi-shortcode-generator-block strong {
			font-size: 13px;
		}
		jdiv { display: none !important; }
	</style>

	<div class="tti-platform-loader-admin">
		<img src="<?php echo esc_attr( esc_url_raw( MI_ADMIN_URL . 'images/loader.gif', 'https' ) ); ?>" alt="" />
	</div>

	<div class="tti-platform-tab">

		<?php
			/**
			 * Fires before settings page content.
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_shortcode_generator_popup_left_menu_before' );
		?>

		<button class="tti-platform-tablinks active" onclick="openTab(event, 'Assessment')"><?php esc_html_e( 'Assessment', 'tti-platform' ); ?></button>
		<button class="tti-platform-tablinks" onclick="openTab(event, 'Text')"><?php esc_html_e( 'Text Feedback', 'tti-platform' ); ?></button>
		<button class="tti-platform-tablinks" onclick="openTab(event, 'Graphic')"><?php esc_html_e( 'Graphic Feedback', 'tti-platform' ); ?></button>
		<button class="tti-platform-tablinks" onclick="openTab(event, 'PDF')"><?php esc_html_e( 'PDF Download', 'tti-platform' ); ?></button>
		<button class="tti-platform-tablinks" onclick="openTab(event, 'Tti_Cons_Report')"><?php esc_html_e( 'Consolidation Report', 'tti-platform' ); ?></button>

		<?php
			/**
			 * Fires after settings page content.
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_shortcode_generator_popup_left_menu_after' );
		?>

	</div>

	<div class="tab-detail">

		<!-- Tab 1 - Assessment -->
		<div id="Assessment" class="tabcontent" style="display: block;">
			<?php
				/**
				 * Fires before shortcode generator popup assessment block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_block_before' );
			?>
			<h3><?php esc_html_e( 'Assessment', 'tti-platform' ); ?></h3>
			<p><?php esc_html_e( 'To have a participant take an assessment select the assessment below and click Insert Code.', 'tti-platform' ); ?></p>

			<div id="assessment_list">
				<p><strong><?php esc_html_e( 'Select Assessment:', 'tti-platform' ); ?></strong> <select><option></option></select></p>
			</div>

			<?php
				/**
				 * Fires after shortcode generator popup assessment block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_block_after' );
			?>

		</div>

		<!-- Tab 2 - Text Feedback -->
		<div id="Text" class="tabcontent">

			<?php
				/**
				 * Fires before shortcode generator popup assessment text feedback_ block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_text_feedback_before' );
			?>

			<h3><?php esc_html_e( 'Text Feedback', 'tti-platform' ); ?></h3>
			<p><?php esc_html_e( 'To text feedback complete the options below and click Insert Code.', 'tti-platform' ); ?></p>

			<div id="assessment_list_text">
				<p><strong><?php esc_html_e( 'Select Assessment:', 'tti-platform' ); ?></strong> <select id="assessment_list_for_text"><option></option></select></p>
				<p><strong><?php esc_html_e( 'Select Feedback:', 'tti-platform' ); ?></strong> <select id="assessment_list_text_feedback"><option></option></select></p>
			</div>

			<div id="assessment_checklist">
				<div class="rowTitles"></div>
				<div class="checklist_feedback"></div>
			</div>

			<?php
				/**
				 * Fires after shortcode generator popup assessment text feedback_ block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_text_feedback_after' );
			?>

		</div>

		<!-- Tab 3 - Graphic Feedback -->
		<div id="Graphic" class="tabcontent">

			<?php
				/**
				 * Fires before shortcode generator popup assessment text graphic block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_graphic_feedback_before' );
			?>

			<h3><?php esc_html_e( 'Graphic', 'tti-platform' ); ?></h3>
			<p><?php esc_html_e( 'To graphic feedback complete the options below and click Insert Code.', 'tti-platform' ); ?></p>

			<div id="assessment_list_graphic">
				<p><strong><?php esc_html_e( 'Select Assessment:', 'tti-platform' ); ?></strong> <select id="assessment_list_for_graphic"><option></option></select></p>
				<p><strong><?php esc_html_e( 'Select Feedback:', 'tti-platform' ); ?></strong> <select id="assessment_list_graphic_feedback"><option></option></select></p>
			</div>

			<div id="assessment_checklist_for_graphic">
				<div class="rowTitles"></div>
				<div class="checklist_feedback_graphic"></div>
			</div>

			<?php
				/**
				 * Fires after shortcode generator popup assessment text graphic block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_graphic_feedback_after' );
			?>

		</div>

		<!-- Tab 4 - PDF Download -->
		<div id="PDF" class="tabcontent">

			<?php
				/**
				 * Fires before shortcode generator popup assessment text graphic block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_pdf_feedback_before' );
			?>

			<h3><?php esc_html_e( 'PDF Download', 'tti-platform' ); ?></h3>
			<p><?php esc_html_e( 'To PDF download, click Insert Code.', 'tti-platform' ); ?></p>

			<div id="assessment_list_pdf">
				<p>
					<strong><?php esc_html_e( 'Select Assessment:', 'tti-platform' ); ?></strong>
					<select id="assessment_list_for_pdf">
						<option></option>
					</select>
				</p>
			</div>

			<?php
				/**
				 * Fires after shortcode generator popup assessment text graphic block
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_pdf_feedback_after' );
			?>

		</div>

		<!-- Tab 5 - Consolidation Report -->
		<div id="Tti_Cons_Report" class="tabcontent">

			<?php
				/**
				 * Fires before shortcode generator popup assessment consilidation report
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_pdf_consilidation_report_before' );
			?>

			<h3><?php esc_html_e( 'Consolidation PDF Report', 'tti-platform' ); ?></h3>
			<p><?php esc_html_e( 'To PDF download, click Insert Code.', 'tti-platform' ); ?></p>

			<div id="assessment_list_for_cons_report_block">
				<p><strong><?php esc_html_e( 'Select Assessment:', 'tti-platform' ); ?></strong> <select id="assessment_list_for_cons_report"><option></option></select></p>
				<p><strong><?php esc_html_e( 'Select Report Type:', 'tti-platform' ); ?></strong>
					<select id="assessment_list_for_cons_report_type">
						<option value="0"></option>
						<option value="type_one">Type One</option>
						<option value="quick_strength">Quick Strength</option>
						<option value="quick_screen">Quick Screen</option>
					</select>
				</p>
			</div>

			<?php
				/**
				 * Fires after shortcode generator popup assessment consilidation report
				 *
				 * @since   1.2
				 */
				do_action( 'ttisi_platform_shortcode_generator_assessment_pdf_consilidation_report_after' );
			?>
		</div>

		<?php
			/**
			 * Fires after shortcode generator popup assessment text graphic block
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_shortcode_generator_assessment_list_action' );
		?>

	</div>

</div>

<?php wp_footer(); ?>

</body>
