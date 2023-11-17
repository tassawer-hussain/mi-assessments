<?php
/**
 * Helper functions used to debug while development.0
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Redirect the user to home page on logout.
 *
 * @return void
 */
function auto_redirect_after_logout() {

	wp_safe_redirect( get_home_url() );
	exit();

}
add_action( 'wp_logout', 'auto_redirect_after_logout' );
