<?php
/**
 * Class to download interview data of specific user into PDF
 *
 * @link       https://ministryinsights.com/
 * @since      1.7.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 *
 * Class to download interview data of specific user into PDF
 *
 * @since      1.7.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/pdf
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Pdf_Report {

	/**
	 * Report type
	 *
	 * @var string
	 */
	public $report_type;

	/**
	 * Assessment id
	 *
	 * @var integer
	 */
	public $assess_id;

	/**
	 * User ID
	 *
	 * @var integer
	 */
	public $user_id;

	/**
	 * Mpdf library object
	 *
	 * @var objet
	 */
	public $mpdf;

	/**
	 * Mpdf html
	 *
	 * @var string
	 */
	public $mhtml;

	/**
	 * Contains output array
	 *
	 * @var array
	 */
	public $output_arr;

	/**
	 * Contains string
	 *
	 * @var string
	 */
	public $created_at_date;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since       1.0.0
	 * @access   public
	 */
	public function __construct() {

		$this->mhtml = '';

	}

	/**
	 * Function to convert interview data into PDF using mpdf library
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function download_pdf() {

		$current_user = wp_get_current_user();

		// Require composer autoload.
		require_once MI_INCLUDES_PATH . 'pdf/mpdf/vendor/autoload.php';

		$mpdf_config = array(
			'mode'          => 'utf-8',
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 25,
			'margin_bottom' => 12,
			'margin_header' => 5,
			'margin_footer' => 5,
			'orientation'   => 'L',
			'format'        => array( 215.9, 279.4 ),
		);

		$this->mpdf = new \Mpdf\Mpdf( $mpdf_config );

		// Set header of report.
		$this->set_header();

		// Set footer of report.
		$this->set_footer();

		$this->mpdf->useActiveForms             = true;
		$this->mpdf->curlAllowUnsafeSslRequests = true;
		$this->mpdf->autoPageBreak              = true;
		$this->mpdf->use_kwt                    = true; // Default value: false.
		$this->mpdf->useKerning                 = true; // set this to improve appearance of Circular text.
		$this->mpdf->setAutoTopMargin           = 'stretch';

		// phpcs:ignore
		// $this->mpdf->mirrorMargins = 1;

		ob_get_clean();

		$this->mpdf->WriteHTML( $this->mhtml );

		$file_name = 'consolidation report.pdf';

		if ( isset( $current_user->display_name ) ) {

			$file_name = ucwords( $current_user->display_name ) . ' - Consolidation Report.pdf';

		}

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		if ( isset( $_get_data['user_id'] ) ) {

			$user_id = sanitize_text_field( $_get_data['user_id'] );
			$user    = get_userdata( $user_id );

			$file_name = $user->display_name . ' - Consolidation Report.pdf';

		}

		$this->mpdf->Output( $file_name, 'D' );

		// Delete converted image.
		if ( isset( $_get_data['keyname'] ) ) {
			$keyname_old_check = $_get_data['keyname'];
			unlink( plugin_dir_path( __FILE__ ) . $keyname_old_check . '.jpg' );
		}
	}

	/**
	 * Function to set the header of PDF report.
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function set_header() {

		$current_user = wp_get_current_user();
		$display_name = '';

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		if ( isset( $_get_data['user_id'] ) ) {

			$user_id = sanitize_text_field( $_get_data['user_id'] );

			// Get user data by user id.
			$user         = get_userdata( $user_id );
			$display_name = $user->display_name;

		}

		if ( isset( $display_name ) && ! empty( $display_name ) ) {
			$this->mpdf->SetHTMLHeader(
				'<div style="text-align:right;border-bottom:2px solid #000;padding-bottom:5px;font-family:montserrat;">
					<img src="https://ucarecdn.com/6e313bca-ab43-4bbc-83e1-11e35ea8f54c/Wordmark__Primary.png" width="280" style="float: left;" />
					<div style="float: right; text-align:right;">
						<span style="font-size: 18px;color:#000;">' . esc_html( ucwords( $display_name ) ) . '</span><br>
						<span style="font-size: 12px;color:#000;">' . $this->created_at_date . '</span><br>
						<span style="font-size: 12px;color:#000;">' . __( 'Consolidation Report', 'tti-platform' ) . '</span><br>
					</div>
            	</div>'
			);
		} else {
			$this->mpdf->SetHTMLHeader(
				'<div style="text-align:right;border-bottom:2px solid #000;padding-bottom:5px;font-family:montserrat;">
					<img src="https://ucarecdn.com/6e313bca-ab43-4bbc-83e1-11e35ea8f54c/Wordmark__Primary.png" width="280" style="float: left;" />
					<div style="float: right; text-align:right;">
						<span style="font-size: 18px;color:#000;">' . esc_html( ucwords( $current_user->display_name ) ) . '</span><br>
						<span style="font-size: 12px;color:#000;">' . $this->created_at_date . '</span><br>
						<span style="font-size: 12px;color:#000;">' . __( 'Consolidation Report', 'tti-platform' ) . '</span><br>
					</div>
            	</div>'
			);
		}

	}

	/**
	 * Function to set the footer of PDF report.
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function set_footer() {

		$this->mpdf->SetHTMLFooter(
			'<table width="100%">
			   	<tr>
					<td width="50%" style="font-size:10px;text-align: left;font-family:montserrat;font-weight:300;">' . __( 'Copyright © 2004-2021. Insights International, Inc', 'tti-platform' ) . '</td>
					<td width="25%" align="center"></td>
					<td width="25%" style="text-align: right;font-family:montserrat;font-weight:300;font-size:10px;">{PAGENO}</td>
			   	</tr>
		   	</table>'
		);
	}

	/**
	 * Function to download the report.
	 *
	 * @since 1.6.3
	 * @param integer $assess_id contains assessment id.
	 * @param string  $report_type contains report type.
	 * @access   public
	 */
	public function download_report( $assess_id, $report_type ) {

		$this->assess_id   = $assess_id;
		$this->report_type = $report_type;

		if ( 'type_one' === $report_type ) {

			// Initiate process number one.
			$this->init_pdf_type_process();
			$this->create_report_html();
			$this->download_pdf();

		} elseif ( 'quick_strength' === $report_type || 'quick_screen' === $report_type ) {

			// Initiate process.
			require_once MI_INCLUDES_PATH . 'pdf/class-mi-assessments-pdf-report2.php';

			$this->init_pdf_type_process();

			if ( 'quick_strength' === $report_type ) {
				$pdf_report = new TTI_Platform_Public_PDF_Report2( $report_type, $assess_id, $this->created_at_date, $this->user_id );
			} elseif ( 'quick_screen' === $report_type ) {
				$pdf_report = new TTI_Platform_Public_PDF_Report2( $report_type, $assess_id, $this->created_at_date );
			}

			$pdf_report->init_pdf_process( $this->output_arr );

		} else {
			esc_html_e( 'No Report Type Specified.', 'tti-platform' );
		}

	}

	/**
	 * Function to initiate type one process.
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function init_pdf_type_process() {

		if ( isset( $this->assess_id ) && isset( $this->report_type ) ) {
			$this->get_report_one_data();
		} else {
			esc_html_e( 'No PDF Data Available.', 'tti-platform' );
		}

	}

	/**
	 * Function to get report data.
	 *
	 * It includes following information (GENCHAR, DOS, DONTS, IDEALENV)
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function get_report_one_data() {

		global $wpdb;

		$current_user     = wp_get_current_user();
		$user_id          = $current_user->ID;
		$assessment_table = $wpdb->prefix . 'assessments';
		$link_id          = get_post_meta( $this->assess_id, 'link_id', true );

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		if ( isset( $_get_data['user_id'] ) ) {
			$user_id = sanitize_text_field( $_get_data['user_id'] );
		}

		// Get assessment version.
		if ( isset( $_get_data['version'] ) ) {
			$asses_version = sanitize_text_field( $_get_data['version'] );
		} else {
			$asses_version = get_current_user_assess_version( $user_id, $link_id );
		}

		$this->user_id = $user_id;

		$columns = 'selected_all_that_apply, assessment_result, updated_at';
		$results = get_user_latest_completed_assessment_result( $user_id, $link_id, $asses_version );

		$selected_all_that_apply  = unserialize( $results->selected_all_that_apply ); // phpcs:ignore
		$assessment_result        = unserialize( $results->assessment_result ); // phpcs:ignore

		$this->created_at_date = gmdate( 'M d, Y', strtotime( $results->updated_at ) );

		if ( count( $selected_all_that_apply ) > 0 ) {
			$this->create_output_array_text( $selected_all_that_apply );
			$this->create_output_array_images( $assessment_result );
		}
	}

	/**
	 * Function to create report html.
	 *
	 * It includes following information (GENCHAR, DOS, DONTS, IDEALENV)
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function create_report_html() {

		$this->mhtml .= '<div>';

		$this->mhtml .= '
            <style>
               @media print {
                    #break-after {
                        page-break-after: always;
                    }
                }
            </style>
        ';

		if ( isset( $this->output_arr['text'] ) ) {

			$this->mhtml .= '<div style="font-family:montserrat;font-size:11px;width:100%;text-align:left;">';

			$this->output_charts();

			$this->mhtml .= '<div style="padding-right:25px;float:left;">';

			foreach ( $this->output_arr['text'] as $key => $value ) {

				if ( 'GENCHAR' === $key ) {

					$this->mhtml .= '<h2 style="font-family:montserrat;font-size:17px;text-align:left;"><b>General Characteristics</b></h2>';

				} elseif ( 'DOS' === $key ) {

					if ( isset( $this->output_arr['text']['DONTS'] ) && isset( $this->output_arr['text']['DOS'] ) ) {
						$this->mhtml .= '<div><div style="width:50%;float:left;text-align:left;">';
					}

					$this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Communication Tips</b></h2>';

				} elseif ( 'DONTS' === $key ) {

					if ( isset( $this->output_arr['text']['DONTS'] ) && isset( $this->output_arr['text']['DOS'] ) ) {
						$this->mhtml .= '<div style="width:50%;float:right;">';
					}

					$this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Communication Barriers </b></h2>';

				} elseif ( 'IDEALENV' === $key ) {

					if ( isset( $this->output_arr['text']['MOT'] ) && isset( $this->output_arr['text']['IDEALENV'] ) ) {

						$this->mhtml .= '<div><div style="width:50%;float:left;">';
						$this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Ideal Environment</b></h2>';

					} else {

						$this->mhtml .= '<div><h2 style="font-family:montserrat;font-size:17px;text-align:left;"><b>Ideal Environment</b></h2>';

					}
				} elseif ( 'MOT' === $key ) {

					if ( isset( $this->output_arr['text']['MOT'] ) && isset( $this->output_arr['text']['IDEALENV'] ) ) {
						$this->mhtml .= '<div style="width:50%;float:right;">';

					}
					$this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Keys to Motivating Me</b></h2>';

				} elseif ( 'MAN' === $key ) {

					$this->mhtml .= '<div><h2 style=margin-top:-4px;font-family:monts"errat;font-size:17px;text-align:left;"><b>Keys to Leading Me</b></h2>';

				}

				if ( isset( $value ) && ! empty( $value ) ) {

					$this->mhtml .= '<ul style="font-family:montserrat;font-size:11px;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					if ( 'DOS' === $key && isset( $this->output_arr['text']['DONTS'] ) && isset( $this->output_arr['text']['DOS'] ) ) {
						$this->mhtml .= '</div>';
					}

					if ( 'DONTS' === $key && isset( $this->output_arr['text']['DONTS'] ) && isset( $this->output_arr['text']['DOS'] ) ) {
						$this->mhtml .= '</div>';
					}

					if ( 'DONTS' === $key && isset( $this->output_arr['text']['DONTS'] ) && isset( $this->output_arr['text']['DOS'] ) ) {
						$this->mhtml .= '<div style="clear:both;"></div></div>';
					}

					if ( 'IDEALENV' === $key && isset( $this->output_arr['text']['MOT'] ) && isset( $this->output_arr['text']['IDEALENV'] ) ) {
						$this->mhtml .= '</div>';
					}

					if ( 'MOT' === $key && isset( $this->output_arr['text']['MOT'] ) && isset( $this->output_arr['text']['IDEALENV'] ) ) {
						$this->mhtml .= '</div><div style="clear:both;"></div>';
					}

					if ( 'MAN' === $key ) {
						$this->mhtml .= '</div>';
					}
				}
			}

			$this->mhtml .= '</div>';
			$this->mhtml .= '</div>';
		}

		$this->mhtml .= '</div>';

	}

	/**
	 * Function to handle outputing the charts.
	 *
	 * @since       1.6.3
	 * @access   public
	 */
	public function output_charts() {

		$svg_url = '';

		$this->mhtml .= '<div style="padding-left:20px;float:right;width: 30%;height:200px;" ><br>';

		if ( isset( $this->output_arr['images'] ) ) {

			foreach ( $this->output_arr['images'] as $key => $value ) {

				if ( ! empty( $value ) ) {

					if ( 'WHEEL' === $key ) {
						$svg_url = $value;
					} else {
						$this->mhtml .= '<img src="' . $value . '" width="auto" />';
					}

					$this->mhtml .= '<br><br><br>';
				}
			}
		}

		$random_numer = wp_rand( 502343055, 1032430550 );
		$svg_url      = $this->strip_param_from_url( $svg_url, 'adaptedpos' );

		$hit_url = MI_INCLUDES_URL . 'pdf/mi-assessments-convert-svg-to-jpg.php?report_type=' . $this->report_type . '&key_name=' . $random_numer . '&assess_id=' . $this->assess_id . '&svg_url=' . rawurlencode( $svg_url );

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		$keyname_old_check = $_get_data['keyname'];
		if ( $this->check_file_exists_here( MI_INCLUDES_URL . 'pdf/' . $keyname_old_check . '.jpg' ) ) {
			$this->mhtml .= '<img src="' . MI_INCLUDES_URL . 'pdf/' . $keyname_old_check . '.jpg" width="auto" />';
		} else {
			header( 'Location: ' . $hit_url );
		}

		$this->mhtml .= '</div>';

	}

	/**
	 * Function to strip parameters from URL.
	 *
	 * @since       1.6.3
	 * @param string $url contains url.
	 * @param string $param contains parameter want to remove.
	 * @access public
	 * @return string returns updated url
	 */
	public function strip_param_from_url( $url, $param ) {

		$base_url   = strtok( $url, '?' );             // Get the base url.
		$parsed_url = wp_parse_url( $url );            // Parse it.
		$query      = $parsed_url['query'];            // Get the query string.

		parse_str( $query, $parameters );              // Convert Parameters into array.
		unset( $parameters[ $param ] );                // Delete the one you want.

		$new_query = http_build_query( $parameters );  // Rebuilt query string.

		return $base_url . '?' . $new_query;           // Finally url is ready.

	}

	/**
	 * Function to check if file exists.
	 *
	 * @since       1.6.3
	 * @param string $url contains file url.
	 * @access   public
	 * @return boolean contains true/false
	 */
	public function check_file_exists_here( $url ) {

		$result = get_headers( $url );
		return stripos( $result[0], '200 OK' ) ? true : false; // check if $result[0] has 200 OK.

	}

}
