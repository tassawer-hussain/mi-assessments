<?php
/**
 * Class to handle error log.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Class to handle error log.
 *
 * This class defines all code necessary to log the error handling.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Error_Log {

	/**
	 * Function to update error log.
	 *
	 * @since       1.0.0
	 * @param array  $data contains string data for error log.
	 * @param string $data_type contain data type.
	 * @param string $log_type contain log type. error, success, warning, primary.
	 */
	public static function put_error_log( $data, $data_type = 'string', $log_type = '' ) {

		// append time to the array.
		$log_time         = array();
		$log_time['time'] = gmdate( 'g:i a' );

		if ( 'array' === $data_type ) {
			$data = array_merge( $log_time, $data );
		} elseif ( 'string' === $data_type ) {
			$data['message'] = $data;
			$data            = array_merge( $log_time, $data );
		}

		// phpcs:ignore
		// $remove = array( 'Array', '(', ')', '    ' );
		$remove = array( 'Array' );

		$data = str_replace( $remove, '', print_r( $data, true ) ); // phpcs:ignore

		// Remove the blank lines.
		$data       = explode( "\n", $data );
		$data       = array_filter( $data );
		$clean_data = implode( "\n", $data );

		$data = '<pre class="alert alert-' . $log_type . '">' . $clean_data . '</pre>';

		$date            = gmdate( 'd-m-Y', time() );
		$error_log_spath = MI_ERROR_LOG_PATH . 'debug_' . $date . '.log';

		// phpcs:ignore
		error_log( $data.PHP_EOL, 3, $error_log_spath );

	}


	/**
	 * Read the error log file.
	 *
	 * @since   2.0.0
	 * @return void
	 */
	public static function mi_platform_read_error_log_files() {

		$log_directory = MI_ERROR_LOG_PATH . '*.log';

		$files = glob( $log_directory, GLOB_NOSORT );

		if ( count( $files ) > 0 ) {

			// sort files by last modified date.
			usort(
				$files,
				function( $x, $y ) {
					return filemtime( $x ) < filemtime( $y );
				}
			);

			echo '<h4>Time Format : UTC</h4>';

			foreach ( $files as $file ) {

				$file_date = explode( '/', $file );

				$filename  = $file_date[ count( $file_date ) - 1 ];
				$file_date = explode( '_', $filename );
				$file_date = explode( '.', $file_date[1] );
				$file_date = $file_date[0];

				/* translators: %s: Log File Date */
				echo wp_kses_post( sprintf( __( '<h2>Log - [ %s ]', 'mi-assessment' ), gmdate( 'F j, Y', strtotime( $file_date ) ) ) );

				self::read_the_file( $file );

			}
		} else {
			echo wp_kses_post( '<h3>No Log Files</h3>' );
		}

	}

	/**
	 * Read the error log file.
	 *
	 * @param string $file Path to the log file.
	 * @return void
	 */
	public static function read_the_file( $file ) {

		$file_data = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

		$log_data = '';

		// iterate over file() generated array.
		foreach ( $file_data as $key => $dat ) {
			$log_data .= $dat . '<br>';
		}

		echo '<div style="" class="tti_plat_el_textarea" rows="16" readonly="readonly">';
		echo wp_kses_post( $log_data );
		echo '</div>';

	}


}
