<?php
/**
 * Fired during plugin activation
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_TinyMCE_Shortcode {

	/**
	 * Array contains text list feedback types
	 *
	 * @var array
	 */
	public $list_text_feed_array;

	/**
	 * Array contains graphic list feedback types
	 *
	 * @var array
	 */
	public $both_text_grpahic_array;

	/**
	 * Constructor function to class initialize properties and hooks.
	 *
	 * @since       2.0.0
	 */
	public function __construct() {

		// Create an object of the assessment api class.
		$this->mi_api = new Mi_Assessments_API();

		add_action( 'admin_init', array( $this, 'init_feed_array' ) );

		add_action( 'init', array( $this, 'assessment_buttons' ) );

		// Ajax Hook initialization to Get All Assessments as list.
		add_action( 'wp_ajax_list_assessments', array( $this, 'list_assessments' ) );
		add_action( 'wp_ajax_nopriv_list_assessments', array( $this, 'list_assessments' ) );

		// Ajax Hook Initialization Assessment Feedback.
		add_action( 'wp_ajax_list_assessments_for_feedback', array( $this, 'list_assessments_for_feedback' ) );
		add_action( 'wp_ajax_nopriv_list_assessments_for_feedback', array( $this, 'list_assessments_for_feedback' ) );

		// Ajax Hook Initialization PDF Feedback.
		add_action( 'wp_ajax_list_assessments_for_pdf', array( $this, 'list_assessments_for_pdf' ) );
		add_action( 'wp_ajax_nopriv_list_assessments_for_pdf', array( $this, 'list_assessments_for_pdf' ) );

		// Ajax Hook List Opened Assessments List.
		add_action( 'wp_ajax_list_opened_assessments_list', array( $this, 'list_opened_assessments_list' ) );
		add_action( 'wp_ajax_nopriv_list_opened_assessments_list', array( $this, 'list_opened_assessments_list' ) );

		// Ajax hook initialization to get all metadata values assigned to the assessment.
		add_action( 'wp_ajax_get_assessments_metadeta', array( $this, 'get_assessments_metadeta' ) );
		add_action( 'wp_ajax_nopriv_get_assessments_metadeta', array( $this, 'get_assessments_metadeta' ) );

		// Ajax Hook Initialization to Get Assessment Metadata Checklist.
		add_action( 'wp_ajax_get_assessments_metadeta_checklist', array( $this, 'get_assessments_metadeta_checklist' ) );
		add_action( 'wp_ajax_nopriv_get_assessments_metadeta_checklist', array( $this, 'get_assessments_metadeta_checklist' ) );

		add_action( 'after_wp_tiny_mce', array( $this, 'assessment_tinymce_extra_vars' ) );

	}

	/**
	 * Initialize list feedback array which contains indexes which we need from ttisi api response.
	 *
	 * @since 1.2.0
	 */
	public function init_feed_array() {

		$this->list_text_feed_array = array(
			'INTRO',
			'TITLE',
			'GENCHAR',
			'VAL',
			'DOS',
			'DONTS',
			'COMMTIPS',
			'PERCEPT',
			'BEHAVIOR_AVOIDANCE',
			'NASTYLE',
			'ADSTY',
			'TWASTERS',
			'INTRO12',
			'AREA',
			'MOTGENCHAR',
			'DFSTRWEAK',
			'TRICOACHINTRO2',
			'DFENGSTRESS',
			'INTEGRATIONINTRO_DF',
			'POTENTIALSTR_DR',
			'POTENTIALCONFLIT_DR',
			'MOTIVATINGDR',
			'MANAGINGDR',
			'IDEALENVDR',
			'BLENDINGSADFEQ',
			'BLENDING_DF_INTRO',
			'EQ_INTRO',
			'EQGENCHAR',
			'MOT',
			'MAN',
			'AREA',
			'BFI',
			'IDEALENV',
			'ACTION2',
		);

		$this->both_text_grpahic_array = array(
			// 'DES',
			'EQTABLES2',
		);
	}

	/**
	 * Function to add shortcode button in WYSISYG.
	 *
	 * @since   1.0.0
	 */
	public function assessment_buttons() {

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( $this, 'assessment_add_buttons' ) );
		add_filter( 'mce_buttons', array( $this, 'assessment_register_buttons' ) );
	}

	/**
	 * Function to include script.
	 *
	 * @since   1.0.0
	 * @param array $plugin_array contains plugins data.
	 * @return array return updated plugins data
	 */
	public function assessment_add_buttons( $plugin_array ) {
		$plugin_array['mybutton'] = MI_ADMIN_URL . 'js/mi-assessments-admin.js';

		/**
		 * Filter to update assessment add buttons
		 *
		 * @since  1.2
		 */
		$plugin_array = apply_filters( 'ttisi_platform_assessment_add_buttons', $plugin_array );

		return $plugin_array;
	}

	/**
	 * Function to register the button
	 *
	 * @since   1.0.0
	 * @param array $buttons contains buttons.
	 * @return array
	 */
	public function assessment_register_buttons( $buttons ) {
		array_push( $buttons, 'mybutton' );
		/**
		 * Filter to registering assessment buttons
		 *
		 * @since  1.2
		 */
		$buttons = apply_filters( 'ttisi_platform_assessment_register_buttons', $buttons );
		return $buttons;
	}

	/**
	 * Function to render all assessments in the shortcode generator in the editor.
	 *
	 * @since    1.0.0
	 */
	public function list_assessments() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$loop = fetched_all_mi_assessments_post_type();

		$assessmenr_arr = array();

		while ( $loop->have_posts() ) :

			$loop->the_post();
			$assesment_id      = get_the_ID();
			$status_assessment = get_post_meta( $assesment_id, 'status_assessment', true );
			$status_assessment = ( ! empty( $status_assessment ) ) ? $status_assessment : '';

			/* check suspend status */
			if ( 'Suspended' !== $status_assessment ) {
				$assessmenr_arr[] = array(
					'id'    => $assesment_id,
					'title' => html_entity_decode( get_the_title() ),
				);
			}
		endwhile;

		if ( count( $assessmenr_arr ) > 0 ) {

			/**
			 * Filter to update listing assessments
			 *
			 * @since  1.2
			 */
			$assessmenr_arr = apply_filters( 'ttisi_platform_list_assessments', $assessmenr_arr );

			echo wp_json_encode( $assessmenr_arr );
		} else {
			echo wp_json_encode( 'none' );
		}

		exit;
	}

	/**
	 * Function to render all assessments for text feedback in the shortcode generator in the editor.
	 *
	 * @since    1.0.0
	 */
	public function list_assessments_for_feedback() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$loop = fetched_all_mi_assessments_post_type();

		$assessmenr_arr = array();

		while ( $loop->have_posts() ) :

			$loop->the_post();
			$assesment_id      = get_the_ID();
			$status_assessment = get_post_meta( $assesment_id, 'status_assessment', true );
			$status_assessment = ( ! empty( $status_assessment ) ) ? $status_assessment : '';

			$status_locked = get_post_meta( $assesment_id, 'status_locked', true );

			/* check suspend status */
			if ( 'Suspended' !== $status_assessment && 'true' === $status_locked ) {
				$assessmenr_arr[] = array(
					'id'    => $assesment_id,
					'title' => html_entity_decode( get_the_title() ),
				);
			}

		endwhile;

		if ( count( $assessmenr_arr ) > 0 ) {

			/**
			 * Filter to update listing feedback assessments
			 *
			 * @since  1.2
			 */
			$assessmenr_arr = apply_filters( 'ttisi_platform_list_feedback_assessments', $assessmenr_arr );

			echo wp_json_encode( $assessmenr_arr );
		} else {
			echo wp_json_encode( 'none' );
		}

		exit;
	}

	/**
	 * Function to render all assessments for PDF report in the shortcode generator in the editor.
	 *
	 * @since    1.0.0
	 */
	public function list_assessments_for_pdf() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$loop = fetched_all_mi_assessments_post_type();

		$assessmenr_arr = array();

		while ( $loop->have_posts() ) :

			$loop->the_post();
			$assesment_id      = get_the_ID();
			$status_assessment = get_post_meta( $assesment_id, 'status_assessment', true );
			$status_assessment = ( ! empty( $status_assessment ) ) ? $status_assessment : '';

			$status_locked = get_post_meta( $assesment_id, 'status_locked', true );

			$report_status = get_post_meta( $assesment_id, 'print_report', true );

			if ( 'Suspended' !== $status_assessment && 'true' === $status_locked && 'Yes' === $report_status ) {
				$assessmenr_arr[] = array(
					'id'    => $assesment_id,
					'title' => html_entity_decode( get_the_title() ),
				);
			}

		endwhile;

		if ( count( $assessmenr_arr ) > 0 ) {

			/**
			 * Filter to update listing pdf assessments
			 *
			 * @since  1.2
			 */
			$assessmenr_arr = apply_filters( 'ttisi_platform_list_pdf_assessments', $assessmenr_arr );

			echo wp_json_encode( $assessmenr_arr );
		} else {
			echo wp_json_encode( 'none' );
		}
		exit;
	}

	/**
	 * Function to render all opened assessments for PDF report in the shortcode generator in the editor.
	 *
	 * @since    1.0.0
	 */
	public function list_opened_assessments_list() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$loop = fetched_all_mi_assessments_post_type();

		$assessmenr_arr = array();

		while ( $loop->have_posts() ) :
			$loop->the_post();
			$assesment_id  = get_the_ID();
			$status_locked = get_post_meta( $assesment_id, 'status_locked', true );

			/* check suspend status */
			if ( 'false' === $status_locked ) {
				$assessmenr_arr[] = array(
					'id'    => $assesment_id,
					'title' => html_entity_decode( get_the_title() ),
				);
			}

		endwhile;

		if ( count( $assessmenr_arr ) > 0 ) {

			/**
			 * Filter to update listing assessments
			 *
			 * @since  1.2
			 */
			$assessmenr_arr = apply_filters( 'ttisi_platform_list_locked_assessments', $assessmenr_arr );

			echo wp_json_encode( $assessmenr_arr );
		} else {
			echo wp_json_encode( 'none' );
		}

		exit;
	}

	/**
	 * Function to render all assessments metadata in the shortcode generator.
	 *
	 * @since    1.0.0
	 */
	public function get_assessments_metadeta() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$assessment_id = isset( $_POST['assessment_text_feedback_id'] ) ? sanitize_text_field( wp_unslash( $_POST['assessment_text_feedback_id'] ) ) : 0;
		$type          = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'text';

		$link_id              = get_post_meta( $assessment_id, 'link_id', true );
		$api_service_location = get_post_meta( $assessment_id, 'api_service_location', true );
		$api_key              = get_post_meta( $assessment_id, 'api_key', true );
		$report_metadata      = ( ! empty( get_post_meta( $assessment_id, 'report_metadata', true ) ) ) ? get_post_meta( $assessment_id, 'report_metadata', true ) : '';

		$report_data = unserialize( $report_metadata ); // phpcs:ignore

		$assessmenr_arr = array();
		if ( isset( $report_data->report_page_metadata->metadata ) && count( $report_data->report_page_metadata->metadata ) > 0 ) {
			foreach ( $report_data->report_page_metadata->metadata as $array_response ) {

				/* Skip full intro parts */
				if ( 'TITLE' === $array_response->ident
					|| 'TRICOACHINTRO2' === $array_response->ident
					|| 'INTRO' === $array_response->ident
					|| 'INTEGRATIONINTRO_DF' === $array_response->ident
					|| 'EQ_INTRO' === $array_response->ident
					|| 'BLENDING_DF_INTRO' === $array_response->ident
				) {
					continue;
				}

				if ( 'TITLE' === $array_response->ident
					|| 'PIAVWHEEL12' === $array_response->ident
					|| 'PIAVWHEEL12_2' === $array_response->ident
					|| 'DES' === $array_response->ident
				) {
					continue;
				}

				/* if request is thrown by graphic */
				if ( 'graphic' === $type ) {

					if ( ! in_array( $array_response->ident, $this->list_text_feed_array, true ) ||
						in_array( $array_response->ident, $this->both_text_grpahic_array, true )
					) {

						if ( 'EQTABLES2' === $array_response->ident ) {

							$this->handle_eqtables_section(
								$array_response,
								$link_id,
								$assessmenr_arr // Pass by refrence.
							);

						} else {

							/* list graphic feedbacks */
							$assessmenr_arr[] = array(
								'link_id' => $link_id,
								'title'   => html_entity_decode( $array_response->title ),
								'ident'   => html_entity_decode( $array_response->ident ),
							);

						}
					}
				} else {
					/* if request is thrown by text */
					if ( in_array( $array_response->ident, $this->list_text_feed_array, true ) ||
						in_array( $array_response->ident, $this->both_text_grpahic_array, true )
					) {

						if ( 'EQTABLES2' === $array_response->ident ) {

							$this->handle_eqtables_section(
								$array_response,
								$link_id,
								$assessmenr_arr // Pass by refrence.
							);

						} else {

							/* list text feedbacks */
							$assessmenr_arr[] = array(
								'link_id' => $link_id,
								'title'   => html_entity_decode( $array_response->title ),
								'ident'   => html_entity_decode( $array_response->ident ),
							);

						}
					}
				}
			}
		}

		/**
		 * Filter to update assessment metadata
		 *
		 * @since  1.2
		 */
		$assessmenr_arr = apply_filters( 'ttisi_platform_get_assessments_metadeta', $assessmenr_arr );

		echo wp_json_encode( $assessmenr_arr );
		exit;
	}

	/**
	 * Function to handle the EQ Tables section.
	 *
	 * @param array  $array_response contains array get from API response.
	 * @param string $link_id contains assessment link ID.
	 * @param array  $assessmenr_arr contains assessment data.
	 */
	public function handle_eqtables_section( $array_response, $link_id, &$assessmenr_arr ) {
		if ( isset( $array_response->content ) ) {
			foreach ( $array_response->content as $key => $value ) {
				$assessmenr_arr[] = array(
					'title'   => html_entity_decode( $value->title ),
					'ident'   => 'EQTABLES2-' . html_entity_decode( $value->ident ),
					'link_id' => $link_id,
				);

			}
		}
	}

	/**
	 * Function to render all feedback metadata checklist in the shortcode generator in the editor.
	 *
	 * @since    1.0.0
	 */
	public function get_assessments_metadeta_checklist() {

		// check nonce.
		check_ajax_referer( 'mi_assessment_popup_tabs', 'nonce' );

		$assessmenr_arr            = array();
		$assessment_feedback_value = isset( $_POST['assessment_feedback_value'] ) ? sanitize_text_field( wp_unslash( $_POST['assessment_feedback_value'] ) ) : '';
		$type                      = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'text';
		$assessment_id             = isset( $_POST['assess_id'] ) ? sanitize_text_field( wp_unslash( $_POST['assess_id'] ) ) : '';
		$api_service_location      = get_post_meta( $assessment_id, 'api_service_location', true );
		$api_key                   = get_post_meta( $assessment_id, 'api_key', true );
		$link_id                   = ( ! empty( get_post_meta( $assessment_id, 'link_id', true ) ) ) ? get_post_meta( $assessment_id, 'link_id', true ) : '';
		$report_metadata           = ( ! empty( get_post_meta( $assessment_id, 'report_metadata', true ) ) ) ? get_post_meta( $assessment_id, 'report_metadata', true ) : '';

		$report_data = unserialize( $report_metadata ); // phpcs:ignore

		if ( isset( $report_data->report_page_metadata->metadata ) && count( $report_data->report_page_metadata->metadata ) > 0 ) {

			$assessmenr_arr = array();
			foreach ( $report_data->report_page_metadata->metadata as $response_data ) {

				if ( $response_data->ident === $assessment_feedback_value && 'EQTABLES2' !== $response_data->ident ) {
					$title            = $response_data->title;
					$content          = $response_data->content;
					$intro            = $response_data->intro;
					$assessmenr_arr[] = array(
						'title'   => $title,
						'content' => $content,
						'intro'   => $intro,
					);
				} elseif ( 'EQTABLES2' === $response_data->ident ) {
					$this->handle_eqtables_section_metachecklist( $response_data, $link_id, $assessment_feedback_value, $type, $assessmenr_arr );
				}
			}
		}

		/**
		* Filter to update assessments metadeta checklist
		*
		* @since  1.2
		*/
		$assessmenr_arr = apply_filters( 'ttisi_platform_assessments_metadeta_checklist', $assessmenr_arr );

		echo wp_json_encode( $assessmenr_arr );
		exit;
	}

	/**
	 * Function to handle the EQ Tables section meta checklist section.
	 *
	 * @since    1.0.0
	 * @param array  $response_data contains api response array.
	 * @param string $link_id contains link id.
	 * @param array  $assessment_feedback_value contains assessment feedback value.
	 * @param string $type contains assessment type.
	 * @param array  $assessmenr_arr returns assessment data array related to EQ tables.
	 */
	public function handle_eqtables_section_metachecklist( $response_data, $link_id, $assessment_feedback_value, $type, &$assessmenr_arr ) {

		if ( isset( $response_data->content ) ) {

			$get_page = explode( '-', $assessment_feedback_value );

			foreach ( $response_data->content as $key => $value ) {

				if ( $value->ident === $get_page[1] ) {

					if ( 'text' === $type ) {

						// Unset description and scorebar for text.
						unset( $value->content[0] );
						unset( $value->content[2] );
						$intro = 1;

					} else {

						// Unset description and bullets for graphics.
						unset( $value->content[0] );
						unset( $value->content[1] );
						$intro = 0;

					}

					$assessmenr_arr[] = array(
						'title'   => $value->title,
						'content' => $value->content,
						'intro'   => $intro,
					);
				}
			}
		}
	}

	/**
	 * Custom sanitization function that will take the incoming input, and sanitize
	 * the input before handing it back to WordPress to save to the database.
	 *
	 * @since    1.0.0
	 *
	 * @param array $input contains array.
	 * @return array contains sanitized array
	 */
	public function sanitize_the_array( $input ) {

		$new_input = array();

		foreach ( $input as $key => $val ) {
			$new_input[ $key ] = sanitize_text_field( $val );
		}

		return $new_input;
	}

	/**
	 * Function to add assessment shortcode generator icon in WYSISYG. SEEMS ITS USELESS FUNCTION. NOT DOING ANY FUNCTIONALITY.
	 *
	 * @since    1.0.0
	 */
	public function assessment_tinymce_extra_vars() { ?>
		<script type="text/javascript">
			var tinyMCE_object = 
			<?php
			echo wp_json_encode(
				array(
					'button_name' => esc_html__( 'TH TTI Shortcodes', 'tti-platform' ),
				)
			);
			?>
			;
		</script>
		<?php
	}

}

// Initialize the TinyMCE Button ShortCode class.
new Mi_TinyMCE_Shortcode();
