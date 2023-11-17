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
class Mi_Assessments_Pdf_Report2 {

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
	 * Date
	 *
	 * @var string
	 */
	public $created_at_date;


	/**
	 * Contains output array
	 *
	 * @var array
	 */
	public $output_arr;

	/**
	 * Number of sections
	 *
	 * @var string
	 */
	public $no_of_sections;

	/**
	 * Define the core functionality of the plugin for frontend.
	 *
	 * @since       1.0.0
	 *
	 * @param string  $report_type Type of the report.
	 * @param string  $assess_id Assessment post ID.
	 * @param string  $cdate Date of assessment completed/created by user.
	 * @param boolean $user_id User ID.
	 */
	public function __construct( $report_type, $assess_id, $cdate, $user_id = 0 ) {

		$this->assess_id       = $assess_id;
		$this->created_at_date = $cdate;
		$this->report_type     = $report_type;
		$this->user_id         = $user_id;
		$this->mhtml           = '';

	}

	/**
	 * Initiziale type two PDF development.
	 *
	 * @since       1.0.0
	 * @param array $output_arr Assessment data.
	 */
	public function init_pdf_process( $output_arr ) {

		$this->output_arr = $output_arr;
		$this->create_report_html();
		$this->download_pdf();

	}

	/**
	 * Function to create HTML for PDF.
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function create_report_html() {

		if ( 'quick_strength' === $this->report_type ) {
			$this->mhtml = '<div style="padding-bottom: 5px;"></div>';
		} else {
			$this->mhtml = '<div style="padding-bottom: 10px;"></div>';
		}

		$this->mhtml .= '<div class="body">';

		if ( isset( $this->output_arr['text'] ) ) {

			$this->mhtml .= '<div class="float-left left-section" >';

			$this->no_of_sections = count( $this->output_arr['text'] );

			if ( 'quick_screen' === $this->report_type ) {
				$this->no_of_sections = $this->no_of_sections - 2;
			}

			$count = 1;
			foreach ( $this->output_arr['text'] as $key => $value ) {

				if ( 'quick_screen' === $this->report_type && ( 'DONTS' === $key || 'DOS' === $key ) ) {
					continue;
				}

				if ( 1 === $count || $this->no_of_sections <= 3 ) {

					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

				} elseif ( ( 2 === $count || 3 === $count ) && $this->no_of_sections >= 4 && 'quick_strength' === $this->report_type ) {

					if ( 2 === $count ) {
						$this->mhtml .= '<div>';
						$this->mhtml .= '<div class="float-left left-section-small" >';
					}

					if ( 3 === $count ) {
						$this->mhtml .= '<div class="float-right" >';

					}
					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					$this->mhtml .= '</div>';

					if ( 3 === $count ) {
						$this->mhtml .= '</div>';
					}
				} elseif ( 2 === $count && $this->no_of_sections >= 4 && 'quick_screen' === $this->report_type ) {

					$this->mhtml .= '<div>';
					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					$this->mhtml .= '</div>';

				} elseif ( ( 4 === $count || 5 === $count ) && $this->no_of_sections > 4 ) {

					if ( 4 === $count ) {
						$this->mhtml .= '<div>';
						$this->mhtml .= '<div class="float-left left-section-small" >';
					}

					if ( 5 === $count ) {
						$this->mhtml .= '<div class="float-right" >';

					}

					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					$this->mhtml .= '</div>';

					if ( 5 === $count ) {
						$this->mhtml .= '</div>';
					}
				} elseif ( 4 === $count && 4 === $this->no_of_sections && 'quick_strength' === $this->report_type ) {

					$this->mhtml .= '<div>';
					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					$this->mhtml .= '</div>';

				} elseif ( ( 3 === $count || 4 === $count ) && 4 === $this->no_of_sections && 'quick_screen' === $this->report_type ) {

					if ( 3 === $count ) {
						$this->mhtml .= '<div>';
						$this->mhtml .= '<div class="float-left left-section-small" >';
					}

					if ( 4 === $count ) {
						$this->mhtml .= '<div class="float-right" >';
					}

					$this->create_report_heading( $key );

					$this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
					foreach ( $value as $innerkey => $innervalue ) {
						$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
					}
					$this->mhtml .= '</ul>';

					$this->mhtml .= '</div>';

					if ( 4 === $count ) {
						$this->mhtml .= '</div>';
					}
				}

				$count++;

			} // main loop ends here.

			$this->mhtml .= '</div>';
		}

		$this->initiate_right_section();

		$this->mhtml .= '</div>';

	}

	/**
	 * Function to create heading for PDF.
	 *
	 * @since       1.6.7
	 * @param string $key Heading of the section.
	 */
	public function create_report_heading( $key ) {

		$heading = '';

		if ( 'GENCHAR' === $key ) {
			$heading = 'General Characteristics';
		} elseif ( 'DOS' === $key ) {
			$heading = 'Communication Tips';
		} elseif ( 'IDEALENV' === $key ) {
			$heading = 'Ideal Work Environment';
		} elseif ( 'DONTS' === $key ) {
			$heading = 'Communication Barriers';
		} elseif ( 'MOT' === $key ) {
			$heading = 'Keys to Motivating';
		} elseif ( 'MAN' === $key ) {
			$heading = 'Keys to Leading';
		}

		$this->mhtml .= '<p style="margin-bottom:-25px;font-size: 12pt;font-family:exo;color:#227ABE;">' . $heading . '</p>';
	}

	/**
	 * Function to create right chart section of PDF.
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function initiate_right_section() {

		$this->mhtml .= '<div class="float-right float-right-pd-left" style="text-align:center;">';

		if ( isset( $this->output_arr['images']['WHEEL'] ) && 'quick_screen' === $this->report_type ) {

			$this->output_charts( $this->output_arr['images']['WHEEL'] );

			if ( isset( $this->output_arr['images']['MICHART1'] ) ) {
				$this->mhtml .= '<img src="' . esc_url( $this->output_arr['images']['MICHART1'] ) . '" width="100%" style="margin-top:20px;text-align:center;" />';
			}
		}

		// decide right section bottom part according to shortcode.
		if ( 'quick_strength' === $this->report_type ) {

			if ( isset( $this->output_arr['images']['MICHART1'] ) ) {
				$this->mhtml .= '<img src="' . esc_url( $this->output_arr['images']['MICHART1'] ) . '" width="100%" style="margin-top:10px;text-align:center;" />';
			}

			if ( isset( $this->output_arr['images']['NaturalWheel'] ) ) {
				$this->output_charts( $this->output_arr['images']['NaturalWheel'] );
			}

			$this->keys_leading_section();
		}

		$this->mhtml .= '</div>';
	}

	/**
	 * Function to handle outputing the charts.
	 *
	 * @since       1.6.7
	 * @param string $url URL of the SVG image.
	 */
	public function output_charts( $url ) {

		$svg_url = $url;

		$random_numer = wp_rand( 502343055, 1032430550 );

		if ( $this->user_id ) {

			$hit_url = MI_INCLUDES_URL . 'pdf/mi-assessments-convert-svg-to-jpg.php?report_type=' . $this->report_type . '&user_id=' . $this->user_id . '&key_name=' . $random_numer . '&assess_id=' . $this->assess_id . '&svg_url=' . rawurlencode( $svg_url );

		} else {

			$hit_url = MI_INCLUDES_URL . 'pdf/mi-assessments-convert-svg-to-jpg.php?report_type=' . $this->report_type . '&key_name=' . $random_numer . '&assess_id=' . $this->assess_id . '&svg_url=' . rawurlencode( $svg_url );

		}

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		$keyname_old_check = $_get_data['keyname'];

		$wheel_width  = '85%';
		$wheel_margin = 'margin-top:20px;';

		if ( 'quick_screen' === $this->report_type ) {
			$wheel_width  = '85%';
			$wheel_margin = 'margin-top:-20px;';
		}

		if ( 'quick_strength' === $this->report_type ) {
			$wheel_width  = '80%';
			$wheel_margin = 'margin-top:-20px;';
		}

		if ( file_exists( MI_INCLUDES_URL . 'pdf/' . $keyname_old_check . '.jpg' ) ) {
			$this->mhtml .= '<img src="' . MI_INCLUDES_URL . 'pdf/' . $keyname_old_check . '.jpg" width="' . $wheel_width . '"  style="' . $wheel_margin . ';"/>';
		} else {
			header( 'Location: ' . $hit_url );
		}

	}

	/**
	 * Function to create keys to leading.
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function keys_leading_section() {

		$count = 0;

		foreach ( $this->output_arr['text'] as $key => $value ) {

			if ( 'MAN' === $key ) {

				foreach ( $value as $innerkey => $innervalue ) {

					if ( ! empty( $innervalue ) && 0 === $count ) {

						$this->mhtml .= '<div class="heading-block" >Keys to Leading Rodney</div>';
						$this->mhtml .= '<div class="keys-leading-block" >';
						$this->mhtml .= '<ul>';

						$count = 1;
					}

					$this->mhtml .= '<li>' . stripslashes( $innervalue ) . '</li>';
				}
			}
		}

		if ( 1 === $count ) {
			$this->mhtml .= '</ul>';
			$this->mhtml .= '</div>';
		}
	}

	/**
	 * Function to convert interview data into PDF using mpdf library
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function download_pdf() {

		$current_user = wp_get_current_user();

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		if ( isset( $_get_data['user_id'] ) ) {

			$user_id = sanitize_text_field( $_get_data['user_id'] );
			$user    = get_userdata( $user_id ); // Get user data by user id.

			$user_display_name = $user->display_name;

		} else {
			$user_display_name = $current_user->display_name;
		}

		$user_display_name = ucwords( $user_display_name );

		// Require composer autoload.
		require_once MI_INCLUDES_PATH . 'pdf/mpdf/vendor/autoload.php';

		$mpdf_config = array(
			'mode'          => 'utf-8',
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 25,
			'margin_bottom' => 12,
			'margin_header' => 3,
			'margin_footer' => 5,
			'orientation'   => 'L',
			'format'        => array( 215.9, 279.4 ),
		);

		$this->mpdf = new \Mpdf\Mpdf( $mpdf_config );

		$this->mpdf->img_dpi = 96;

		$this->set_style();

		if ( 'quick_strength' === $this->report_type ) {

			// Set header of report quick strength.
			$this->set_header2( $user_display_name );

		} else {

			// Set header of report quick screen.
			$this->set_header( $user_display_name );

		}

		// Set footer of report.
		$this->set_footer();

		$this->mpdf->useActiveForms             = true;
		$this->mpdf->curlAllowUnsafeSslRequests = true;
		$this->mpdf->autoPageBreak              = true;
		$this->mpdf->use_kwt                    = true; // Default value: false.
		$this->mpdf->useKerning                 = true; // set this to improve appearance of Circular text.

		$this->mpdf->setAutoTopMargin = 'stretch';

		ob_get_clean();

		$this->mpdf->WriteHTML( $this->mhtml );

		$file_name = $user_display_name . ' - ' . get_the_title( $this->assess_id ) . '.pdf';

		$this->mpdf->Output( $file_name, 'D' );

		/* delete converted image */
		if ( isset( $_get_data['keyname'] ) ) {
			$keyname_old_check = $_get_data['keyname'];
			unlink( plugin_dir_path( __FILE__ ) . $keyname_old_check . '.jpg' );
		}

		exit();
	}

	/**
	 * Function to set the style of PDF report.
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function set_style() {

		$ul_margin_bottom = 'margin-bottom: -13px;';
		$ul_padd_bottom   = 'padding-bottom: 2px;';

		if ( 'quick_screen' === $this->report_type ) {
			$ul_margin_bottom = 'margin-bottom: 15px;';
			$ul_padd_bottom   = 'padding-bottom: 3px;';
		}

		$this->mhtml .= ' 
		<style>
			@media print {
				#break-after {
					page-break-after: always;
				}
			}

			body {
				font-family:OpenSans;
			}

			ul {
				padding-left: 0px;
				list-style-position: outside;
				
			}

			.move-fifteen-up {
				' . $ul_margin_bottom . '
			}

			ul li {
				' . $ul_padd_bottom . '
				font-family:OpenSans;
				padding-left: 0px;
				list-style-position: inside;
				list-style-type: disc;
				list-style-position: inside;
				text-indent: -1em;
				padding-left: 1em;
				font-size: 9pt;
			}

			.float-left {
				float: left;
				padding-left: 0px;
			}

			.float-right {
				float: right;
			}

			.float-right-pd-left {
				padding-left : 40px;
			}

			.left-section {
				width: 58%;
			}

			.left-section-small {
				width: 53%;
				margin-right: 20px;
			}

			.heading-block {
				text-align:left;
				color:#fff;
				margin-top: 15px;
				font-family:exo;
				font-size: 12pt;
				border-radius: 20px 20px 0px 0px;
				background: 
					linear-gradient(
						to right, 
						#14A0D8 0%,
						#217EBB 35%,
						#226FAD 65%,
						#1F5D97 100%
					)
					left 
					bottom
					#777    
					no-repeat; 
					padding-bottom: 15px;
					padding-top: 15px;
					padding-left: 30px;
					margin-top: 20px;
			}

			.keys-leading-block {
				text-align:left;
				padding-top: 10px;
				padding-left: 30px;
				padding-bottom: 10px;
				margin-bottom: 10px;
				border-top: 0px;
				border-bottom: 2px solid #1F5D97;
				border-left: 2px solid #1F5D97;
				border-right: 2px solid #1F5D97;
				border-radius: 0px 0px 20px 20px;
			}

			.footer img {
				margin-top: 15px;
			}

			.footer p {
				font-size: 8pt; 
				font-family: OpenSans;
				margin-left: 30px;
				margin-top: -6px;
				padding-left: 50px;
			}                
		</style>';
	}

	/**
	 * Function to set the header of PDF report.
	 *
	 * @since       1.6.7
	 * @param string $created_for User display name.
	 */
	public function set_header( $created_for ) {

		$this->mpdf->SetHTMLHeader(
			'<div style="width:64%;padding-right: 0px;">
				<div style="float:left;width:50%;text-align: left;">
					<img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="margin-left: -10px;" /><br />
					<div style="margin-left: 55px;margin-top: -25px;"><span style="font-size: 12pt;color:#050505;">' . $this->created_at_date . '</span></div>
				</div>
				<div style="float: right;width:50%;text-align: right;padding-top: 40px;">
					<div style="font-size: 18pt;color:#227ABF;text-align: right;font-style:bold;"><b>' . esc_html( $created_for ) . '</b></div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<div style="text-align:center;
		  		padding-bottom:5px;
		  		margin-top: 1px;
				margin-bottom: 10px;
				width:64%;
				background: 
					linear-gradient(
						to left, 
						#14A0D8 0%,
						#2097D2 12%,
						#218ECC 47%,
						#2684C6 100%
					)
					left 
					bottom
					#777    
					no-repeat; 
				background-size:100% 2px;">
			</div>'
		);

	}

	/**
	 * Function to set the header of PDF report.
	 *
	 * @since       1.6.7
	 * @param string $created_for User display name.
	 */
	public function set_header2( $created_for ) {

		$this->mpdf->SetHTMLHeader(
			'<div style="text-align:right;padding-bottom:0px;">
				<img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="float: left;margin-left:-10px;" />
				<div style="float: right;text-align:right;padding-top:10px;">
					<span style="font-size: 18pt;color:#227ABF;font-style:bold;"><b>' . esc_html( $created_for ) . '</b></span><br>
					<span style="font-size: 12pt;color:#050505;">' . $this->created_at_date . '</span><br>
				</div>
			</div>
			<div style="text-align:center;
				padding-bottom:10px;
				background: 
					linear-gradient(
						to left, 
						#14A0D8 0%,
						#2097D2 12%,
						#218ECC 47%,
						#2684C6 100%
					)
					left 
					bottom
					#777    
					no-repeat; 
				background-size: 100% 1px;">
			</div>'
		);

	}

	/**
	 * Function to set the footer of PDF report.
	 *
	 * @since       1.6.7
	 * @access   public
	 */
	public function set_footer() {

		$width = ( 'quick_strength' === $this->report_type ) ? '80' : '100';

		$this->mpdf->SetHTMLFooter(
			'<div class="footer">
				<div style="width:40%;float: left;"></div>
				<div style="width:20%;float: left;"></div>
				<div style="width:40%;float: right;" >
					<img src="https://ucarecdn.com/fc56b4b3-372f-4d66-9ead-80e3d0350607/Hnetcomimage.png" style="width: ' . $width . '%;"  />
					<p>Copyright ©2004-2021. Insights International, Inc.</p>
				</div>
			</div>'
		);

	}
}
