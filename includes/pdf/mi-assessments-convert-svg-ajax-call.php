<?php
/**
 * Fired to convert SVG imag to JPG.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

$posted_data = filter_input_array( INPUT_POST );

if ( isset( $posted_data['uri'] ) ) {
	$base64 = $posted_data['uri'];
}

if ( isset( $posted_data['keyname'] ) ) {
	$keyname = $posted_data['keyname'];
}

$filename_path = $keyname . '.jpg';

$base64_string = str_replace( 'data:image/png;base64,', '', $base64 );
$base64_string = str_replace( ' ', '+', $base64_string );

// phpcs:ignore
$decoded = base64_decode( $base64_string );

// phpcs:ignore
file_put_contents( $filename_path, $decoded );

exit;
