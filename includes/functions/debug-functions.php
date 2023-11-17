<?php
/**
 * Helper functions used to debug while development.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Print our the value using pre element.
 *
 * @param mixed $print_it value that need to be print.
 * @return void
 */
function mip( $print_it ) {
	echo '<pre>';
	print_r( $print_it ); // phpcs:ignore
	echo '</pre>';
}

/**
 * Print our the value using pre element and stop the function execution using die.
 *
 * @param mixed $print_it value that need to be print.
 * @return void
 */
function mipd( $print_it ) {
	echo '<pre>';
	print_r( $print_it ); // phpcs:ignore
	echo '</pre>';
	die( 'MI Debug' );
}
