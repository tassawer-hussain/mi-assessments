<?php
/**
 * User lists assessment tab content
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

<!-- list tab content -->
<div class="wrap">    

	<h2><?php esc_html_e( 'Assessments List', 'tti-platform' ); ?></h2>

	<div id="tti-platform-wp-list-table-demo">
		<div id="tti-platform-post-body">

			<form id="tti-user-list-form" method="post">
				<?php
					$lists_obj->prepare_items();
					$lists_obj->search_box( __( 'Search', 'tti-platform' ), 'tti-platform' );
					$lists_obj->display();
				?>
			</form>

		</div>			
	</div>

</div>
