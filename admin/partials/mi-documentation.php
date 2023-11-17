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


<header class="">
	<!-- Navbar (placed at the bottom of the header image) -->
	<div class="">
		<a href="#shortcodes">Shortcodes</a>
		<a href="#overrided">Overrided</a>
		<a href="#extended">Extended</a>
		<a href="#changelog">Changelog</a>
	</div>
</header>

<div>
	<div class="" id="shortcodes"></div>
	<div class="" id="overrided"></div>
	<div class="" id="extended"></div>
	<div class="" id="changelog">
		<h3>Improvements</h3>
		<ul>
			<li>Implemented 15 days cron to check the status of public assessment and change them according to the API response.</li>
		</ul>
	</div>
</div>
