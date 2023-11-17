<?php
/**
 * Registered all shortcode.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Manage all shortcodes.
 *
 * This class is used to registered shortcodes.
 *
 * @since      1.7.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Shortcodes {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Create API class instance.
		$this->mi_api = new Mi_Assessments_API();

		// assessment history shortcode.
		add_shortcode( 'tti_assessment_show_user_assessment_history', array( $this, 'mi_assessment_show_user_assessment_history_sccb' ) );

		// completed profile shortocde.
		add_shortcode( 'tti_assessment_show_group_users', array( $this, 'mi_assessment_completed_profiles_sccb' ) );

		// Take Assessment Shortcode.
		add_shortcode( 'take_assessment', array( $this, 'mi_assessments_take_assessment_sccb' ) );

		// Add shortcode to take assessment on site.
		add_shortcode( 'take_assessment_on_site', array( $this, 'mi_take_assessment_on_site_sccb' ) );

		// Text Feedback Assessment Shortcode init.
		add_shortcode( 'assessment_text_feedback', array( $this, 'mi_assessment_text_feedback_sccb' ) );

		// Graphic Feedback Assessment Shortcode init.
		add_shortcode( 'assessment_graphic_feedback', array( $this, 'mi_assessment_graphic_feedback_sccb' ) );

		// PDF Download Shortcode init.
		add_shortcode( 'assessment_pdf_download', array( $this, 'mi_assessments_pdf_download_sccb' ) );

		// Print PDF download button.
		add_shortcode( 'assessment_print_pdf_button_download_report', array( $this, 'mi_assessment_print_pdf_button_sccb' ) );

		add_shortcode( 'assessment_listener', array( $this, 'mi_assessments_listener_sccb' ) );

	}

	/**
	 * Function to output assessments history shortcode function.
	 *
	 * @since   1.6
	 * @param array  $atts contains shortcode attributes.
	 * @param string $content contains shortcode content.
	 * @param string $tag contains shortcode tags.
	 */
	public function mi_assessment_show_user_assessment_history_sccb( $atts = array(), $content = null, $tag = '' ) {

		global $current_user;
		wp_get_current_user();

		/**
		 * Fires before completed assessment history
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_before_assessment_ah_shortcode' );

		// include completed assessment history profile class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-assessments-history.php';

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$assessment_atts = shortcode_atts(
			array(
				'assess_id'    => '',
				'show_as_link' => 'no',
			),
			$atts,
			$tag
		);

		$assess_id = $assessment_atts['assess_id'];
		$show_link = $assessment_atts['show_as_link'];
		$link_id   = get_post_meta( $assess_id, 'link_id', true );

		// Initialize assessment history class functionality.
		$assess_history = new Mi_Assessments_History( $current_user->ID, $link_id, $assess_id, $show_link );

		/**
		 * Fires after completed assessment history list
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_after_assessment_ah_shortcode' );

		// start capturing output.
		ob_start();
		/* include completed profile class */
		echo wp_kses_post( $assess_history->show_assessment_history() );
		$content = ob_get_contents(); // get the contents from the buffer.
		ob_end_clean();

		return $content;
	}

	/**
	 * Function to add completed profile PHP file.
	 *
	 * @since   1.0.0
	 */
	public function mi_assessment_completed_profiles_sccb() {
		/**
		 * Fires before completed file functionality
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_before_assessment_cp_shortcode' );

		ob_start(); // start capturing output.

		// include completed assessment profile class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-assessments-completed-profiles.php';

		// get the contents from the buffer.
		$content = ob_get_contents();

		// stop buffering and discard contents.
		ob_end_clean();

		/**
		 * Fires after completed file functionality
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_after_assessment_cp_shortcode' );

		return $content;
	}

	/**
	 * Function to assessment shortcode for Frontend.
	 *
	 * @since   1.0.0
	 *
	 * @param array  $atts contains shortcode attributes.
	 * @param string $content contains shortcode content.
	 * @param string $tag contains shortcode tags.
	 * @return string returns final assessment shortcode output.
	 */
	public function mi_assessments_take_assessment_sccb( $atts = array(), $content = null, $tag = '' ) {

		// Early bail if user is not logged in.
		if ( ! is_user_logged_in() ) {
			$msg = __( 'You must logged in to take this assessment.', 'tti-platform' );
			$o   = '';
			$o  .= '<div class="assessment_button">';
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o  .= '</div>';
			return $o;
		}

		global $wpdb, $current_user_info;

		// Enqueue style and script.
		$this->mi_assessments_enqueue_styles_scripts();

		$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
		$assessment_atts = shortcode_atts(
			array(
				'assess_id'   => '',
				'button_text' => '',
			),
			$atts,
			$tag
		);

		$current_user_info = wp_get_current_user();
		$current_user      = $current_user_info->ID;
		$assess_id         = sanitize_text_field( $assessment_atts['assess_id'] );

		// Get the current assessment locked status.
		$status_locked = get_post_meta( $assess_id, 'status_locked', true );

		// Get the current assessment status.
		$asses_status = get_post_meta( $assess_id, 'status_assessment', true );

		/**
		 * Fires before assessment shortcode called
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_before_assessments_shortcode' );

		if ( 'Suspended' !== $asses_status ) {

			$link_id = get_post_meta( $assess_id, 'link_id', true );

			// Get assessment version.
			$asses_version = get_current_user_assess_version( $current_user, $link_id );

			// Get latest completed assessment results.
			$columns = 'password';
			$results = get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version, $columns );

			/* Chcek user limit */
			$user_limit = check_user_limit( $current_user, $link_id );

			if ( ( isset( $results->password ) && ! empty( $results->password ) ) && ( ! $user_limit ) ) {

				$msg = __( 'Assessment Completed.', 'tti-platform' );
				$o   = '';
				$o  .= '<div class="assessment_button">';
				$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
				$o  .= '</div>';
				/* Show assessment history */
				$o .= do_shortcode( '[tti_assessment_show_user_assessment_history show_as_link="yes" assess_id="' . $assessment_atts['assess_id'] . '"]' );

			} else {

				$retake_ass_att = '';
				if ( $asses_version >= 1 && $user_limit ) {
					$assessment_atts['button_text'] = 'Retake Assessment';
					$retake_ass_att                 = 'data-retake = "true"';
				}

				$o  = '';
				$o .= '<div class="assessment_button">';
				$o .= '<button id="assessment_button" ' . $retake_ass_att . ' class="closed-assessment" assessment-locked="' . $status_locked . '"  assessment-id="' . esc_attr( $assessment_atts['assess_id'] ) . '" assessment-permalink="' . get_the_permalink() . '">' . esc_html( $assessment_atts['button_text'] ) . '</button><img id="take_loader_front" src="' . esc_attr( esc_url_raw( MI_PUBLIC_URL . 'images/loader.gif', 'https' ) ) . '" alt="" />';
				$o .= '</div>';
				$o .= '<div class="tti-platform-user-level-loading">
					<div class="preloader-wrap">
						<div id="precent" class="percentage"></div>
						<div class="loader">
							<div class="trackbar">
								<div class="loadbar"></div>
							</div>
							<p>Scoring Assessment Please Wait</p>
						</div>
					</div>
				</div>';
				/* Show assessment history */
				$o .= do_shortcode( '[tti_assessment_show_user_assessment_history show_as_link="yes" assess_id="' . $assessment_atts['assess_id'] . '"]' );
			}
		} elseif ( 'Suspended' !== $asses_status && 'false' === $status_locked ) {

			// phpcs:ignore
			if ( in_array( 'tti-platform-application-screening/tti-platform-application-screening.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

				/**
				 * Fires after assessment shortcode called
				 *
				 * @since   1.4.2
				 */
				do_action( 'tti_assessment_open_link_take_assessment_btn', $assess_id, $assessment_atts, $status_locked );

			} else {

				$ass_title = get_the_title( $assess_id );

				$o .= '<div class="assessment_message">';
				$o .= '<h3>Please install and activate <u>TTI Assessment Application Screening</u> addon to take this assessment (' . esc_html( $ass_title ) . ').</h3>';
				$o .= '</div>';

			}
		} else {
			$msg = __( 'This assessment has been suspended.', 'tti-platform' );
			$o   = '';
			$o  .= '<div class="assessment_disabled">';
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o  .= '</div>';
		}

		/**
		 * Fires after assessment shortcode called
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_after_assessments_shortcode' );

		return $o;
	}

	/**
	 * Display onsite assessment.
	 *
	 * @return string
	 */
	public function mi_take_assessment_on_site_sccb() {

		$posted_data = filter_input_array( INPUT_GET );

		$link_id  = isset( $posted_data['link_id'] ) ? sanitize_text_field( wp_unslash( $posted_data['link_id'] ) ) : null;
		$password = isset( $posted_data['password'] ) ? sanitize_text_field( wp_unslash( $posted_data['password'] ) ) : null;
		$user_id  = isset( $posted_data['user_id'] ) ? sanitize_text_field( wp_unslash( $posted_data['user_id'] ) ) : null;

		$user_info  = get_userdata( $user_id );
		$user_email = $user_info->user_email;

		if ( ! empty( $link_id ) ) {

			wp_enqueue_script( 'mi-assessments-onsite' );

			$html .= '
            <script>
                function configure_ttisi_survey(config) {
                    config.logoUrl = "https://adorable-narwhal-1a3181.netlify.app/1x1-00000000.png";
                    config.homeUrl = "https://communicationprofile.com/";
                    config.credentials.code =  "' . $link_id . '";
                    config.credentials.password = "' . $password . '";
                    config.credentials.email = "' . $user_email . '";
                }
            </script>
            <style>
                #ttisi-survey .ttisi-logo {
                    height: inherit !important;
                    width: 10px !important;
                    margin-bottom: 0 !important;
                }
                #ttisi-assessment .btn-primary{
                    color: #fff !important;
                }
                #ttisi-survey > footer {
                    visibility: hidden;
                }
                #data-entry-mode {
                    display: none !important;
                }
                </style>
                <div id="ttisi-survey"></div>           
            ';

			return $html;
		}

	}

	/**
	 * Function to enqueue the styles.
	 *
	 * @since       1.4.2
	 */
	public function mi_assessments_enqueue_styles_scripts() {

		// enqueue public style.
		wp_enqueue_style( 'mi-assessments' );
		wp_enqueue_style( 'mi-assessments-sweetalert' );

		// enqueue public script.
		wp_enqueue_script( 'mi-assessments' );
		wp_enqueue_script( 'mi-assessments-sweetalert' );
	}

	/**
	 * Function to handle assessment text shortcode for Frontend.
	 *
	 * @since   1.0.0
	 * @param array  $atts contains shortcode attributes.
	 * @param string $content contains shortcode content.
	 * @param string $tag contains shortcode tag.
	 * @return string
	 */
	public function mi_assessment_text_feedback_sccb( $atts = array(), $content = null, $tag = '' ) {

		global $wpdb, $current_usr;

		// Early bail if user is not logged-in.
		if ( ! is_user_logged_in() ) {

			$msg = __( 'Please logged-in to complete the feedback.', 'tti-platform' );
			$o   = '<div class="ttisi-content-block">';
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o  .= '</div>';
			return $o;
		}

		// Enqueue style and script.
		$this->mi_assessments_enqueue_styles_scripts();

		/**
		 * Fires before assessment feedback shortcode called
		*
		* @since   1.2
		*/
		do_action( 'ttisi_platform_before_assessments_feedback_shortcode' );

		$o               = '<div class="ttisi-content-block">';
		$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
		$assessment_atts = shortcode_atts(
			array(
				'assess_id'   => '',
				'type'        => '',
				'intro'       => '',
				'datalisting' => '',
				'feedback'    => '',
			),
			$atts,
			$tag
		);

		$current_usr  = wp_get_current_user();
		$current_user = $current_usr->ID;
		$assess_id    = $assessment_atts['assess_id'];

		// Get the current assessment status.
		$asses_status = get_post_meta( $assess_id, 'status_assessment', true );

		if ( 'Suspended' !== $asses_status ) {

			$type        = $assessment_atts['type'];
			$page_indent = 1;

			/* check for EQTables type */
			if ( false !== strpos( $type, 'EQTABLES2' ) ) {
				$eqtype           = $type;
				$eqtables_section = explode( '-', $type );
				$page_indent      = $eqtables_section[1];
				$type             = 'EQTABLES2';
			}

			$gen_char_intro    = $assessment_atts['intro'];
			$gen_char_par      = $assessment_atts['datalisting'];
			$gen_char_feedback = $assessment_atts['feedback'];
			$link_id           = get_post_meta( $assess_id, 'link_id', true );

			/* Get assessment version */
			$asses_version = get_current_user_assess_version( $current_user, $link_id );

			// Get latest completed assessment results.
			$columns = 'assessment_result, selected_all_that_apply';
			$results = get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version, $columns );

			if ( $results ) {

				$report_sections         = unserialize( $results->assessment_result ); // phpcs:ignore
				$selected_all_that_apply = unserialize( $results->selected_all_that_apply ); // phpcs:ignore
				$sections                = $report_sections->report->sections;
				$assessment_arr          = array();

				/**
				 * Filter to update feedback sections of assessments data
				*
				* @since  1.2
				*/
				$sections = apply_filters( 'ttisi_platform_assessments_feedback_sections', $sections );

				foreach ( $sections as $section_data ) {

					if ( $section_data->type === $type ) {

						if ( 'DOS' === $type || 'DONTS' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'prefix'     => $section_data->prefix,
								'statements' => $section_data->statements,
							);

						} elseif ( 'EQGENCHAR' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'titles'     => $section_data->header->titles,
								'statements' => $section_data->statement_blocks,
							);

						} elseif ( 'COMMTIPS' === $type ) {

							$assessment_arr = array(
								'intro'   => $section_data->header->text,
								'factors' => $section_data->factors,
							);

						} elseif ( 'PERCEPT' === $type ) {

							$assessment_arr = array(
								'intro'     => $section_data->header->text,
								'title'     => $section_data->title,
								'wordlists' => $section_data->wordlists,
							);

						} elseif ( 'NASTYLE' === $type ) {

							$assessment_arr = array(
								'intro'  => $section_data->header->text,
								'styles' => $section_data->styles,
							);

						} elseif ( 'PIAVBARS12HIGH' === $type ) {

							$assessment_arr = array(
								'intro'          => $section_data->header->text,
								'driving_forces' => $section_data->driving_forces,
							);

						} elseif ( 'TWASTERS' === $type ) {

							$assessment_arr = array(
								'intro'   => $section_data->header->text,
								'wasters' => $section_data->wasters,
							);

						} elseif ( 'EQTABLES2' === $type ) {

							$pages[] = $section_data->$page_indent;

							$assessment_arr = array(
								'intro'     => $pages[0]->description,
								'lead_text' => $pages[0]->leadin,
								'pages'     => $pages,
							);

						} elseif ( 'EQ_INTRO' === $type ) {

							$titles  = $section_data->header->titles[0] . ' - ' . $section_data->header->titles[1];
							$pages[] = $section_data->page1;
							$pages[] = $section_data->page2;

							$assessment_arr = array(
								'titles' => $titles,
								'pages'  => $pages,
							);

						} elseif ( 'DFSTRWEAK' === $type || 'DFENGSTRESS' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'left_side'  => $section_data->left_side,
								'right_side' => $section_data->right_side,
							);

						} elseif (
							'INTEGRATIONINTRO_DF' === $type ||
							'POTENTIALSTR_DR' === $type ||
							'POTENTIALCONFLIT_DR' === $type ||
							'IDEALENVDR' === $type ||
							'BLENDING_DF_INTRO' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'statements' => $section_data->statements,
							);

						} elseif ( 'MOTIVATINGDR' === $type || 'MANAGINGDR' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'prefix'     => $section_data->prefix,
								'statements' => $section_data->statements,
							);

						} elseif ( 'BLENDINGSADFEQ' === $type ) {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'title'      => $section_data->header->titles[0],
								'paragraphs' => $section_data->paragraphs,
							);

						} else {

							$assessment_arr = array(
								'intro'      => $section_data->header->text,
								'prefix'     => $section_data->prefix,
								'statements' => $section_data->statements,
							);

						}
					}
				}

				if ( 'yes' === $gen_char_intro ) {
					$o .= '<p>' . esc_html( $assessment_arr['intro'] ) . '</p>';
				}

				if ( isset( $gen_char_par ) && '' !== $gen_char_par ) {
					$gen_char_par_arr = explode( ',', $gen_char_par );
				} else {
					$gen_char_par_arr = array();
				}

				$in_select = false;

				if ( 'feedback' === $gen_char_feedback ) {

					if ( 'INTRO' === $type || 'TRICOACHINTRO2' === $type || 'ACTION2' === $type || 'INTRO12' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $assessment_arr['statements'] as $key => $value ) {

								$format = $assessment_arr['statements'][ $key ]->format;
								$style  = $assessment_arr['statements'][ $key ]->style;
								$text   = $assessment_arr['statements'][ $key ]->text;

								if ( 'para' === $format && 'left' === $style ) {
									$o .= '<p style="margin-top: 10px;">' . esc_html( $text ) . '</p>';
								} elseif ( 'list' === $format && 'bullets' === $style ) {
									$o .= '<ul>';
									$o .= '<li>' . esc_html( $text ) . '</li>';
									$o .= '</ul>';
								}
							}
						}
					} elseif ( 'COMMTIPS' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							$count = 0;
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['factors'] ) ) {

									$o .= '<div>';

									$factor_statements = $assessment_arr['factors'][ $value ]->statements;
									foreach ( $factor_statements as $key => $value ) {

										$format  = $value->format;
										$style   = $value->style;
										$subhead = $value->subhead;
										$stmts   = $value->stmts;

										if ( 'list' === $format && 'bullets' === $style ) {

											$o .= '<h4>' . esc_html( $subhead ) . '</h4>';
											$o .= '<ul>';
											foreach ( $stmts as $key => $value ) {
												$o .= '<li>' . esc_html( $value ) . '</li>';
											}
											$o .= '</ul>';

										}
									}
									$o .= '</div>';

								}
								$count++;
								if ( 0 === $count % 2 ) {
									$o .= '';
								}
							}
						}
					} elseif ( 'EQGENCHAR' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {

									$o .= '<div>';
									$o .= '<p>';
									foreach ( $assessment_arr['statements'][ $value ]->statements as $index => $checklist ) {
										$o .= esc_html( $checklist );
									}
									$o .= '</p>';

									$o .= '</div>';
								}
							}
						}
					} elseif ( 'BLENDINGSADFEQ' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['paragraphs'] ) ) {

									$o .= '<div>';
									foreach ( $assessment_arr['paragraphs'] as $index => $para ) {
										$o .= '<p>';
										$o .= esc_html( $para );
										$o .= '</p>';
									}
									$o .= '</div>';
								}
							}
						}
					} elseif ( 'PERCEPT' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							foreach ( $gen_char_par_arr as $key => $value ) {
								if ( array_key_exists( $value, $assessment_arr['wordlists'] ) ) {

									$title  = $assessment_arr['wordlists'][ $value ]->title;
									$prefix = $assessment_arr['wordlists'][ $value ]->prefix;
									$words  = $assessment_arr['wordlists'][ $value ]->words;
									$o     .= '<h3 style="margin-bottom:0;">' . esc_html( $title ) . '</h3><strong><em>' . esc_html( $prefix ) . '</em></strong><br><br>';
									$o     .= '<ul>';
									foreach ( $words as $key => $value ) {
										$o .= '<li>' . esc_html( $value ) . '</li>';
									}
									$o .= '</ul>';

								}
							}
						}
					} elseif ( 'NASTYLE' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['styles'] ) ) {

									$styles_title            = $assessment_arr['styles'][ $value ]->title;
									$natural_statements      = $assessment_arr['styles'][ $value ]->natural->statements;
									$natural_statement_title = $assessment_arr['styles'][ $value ]->natural->ident;
									$adapted_statements      = $assessment_arr['styles'][ $value ]->adapted->statements;
									$adapted_statement_title = $assessment_arr['styles'][ $value ]->adapted->ident;

									$o .= '<div style="width: 100%; float: left; margin: 0; padding: 0; box-sizing: border-box;">'
										. '<h3 style="margin: 0">' . esc_html( $styles_title ) . '</h3>'
										. '<div style="width: 48%; float: left; border-right: 1px solid #ccc; padding: 0 15px; box-sizing: border-box;">'
										. '<h4 style="margin:0;">' . esc_html( ucfirst( $natural_statement_title ) ) . '</h4>'
										. '<p>';

									foreach ( $natural_statements as $key => $value ) {
										$o .= $value;
									}

									$o .= '</p>'
										. '</div>'
										. '<div style="width: 48%; float: left; padding: 0 15px; box-sizing: border-box;">'
										. '<h4 style="margin:0;">' . esc_html( ucfirst( $adapted_statement_title ) ) . '</h4>'
										. '<p>';

									foreach ( $adapted_statements as $key => $value ) {
										$o .= $value;
									}

									$o .= '</p>'
										. '</div>'
										. '</div>';
								}
							}
						}
					} elseif ( 'EQ_INTRO' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							$pages = $assessment_arr['pages'];
							$o    .= '<h3>' . esc_html( $assessment_arr['titles'] ) . '</h3>';

							foreach ( $gen_char_par_arr as $key => $value0 ) {

								foreach ( $pages as $key => $value ) {
									if ( $value0 === $key ) {
										foreach ( $value as $key => $value1 ) {

											foreach ( $value1 as $key => $value2 ) {
												$format = $value2->format;
												$style  = $value2->style;
												$text   = $value2->text;
												if ( 'para' === $format && 'left' === $style && '$space' !== $text ) {
													$o .= '<p><strong><em>' . esc_html( $text ) . '</em></strong></p>';
												}
											}
										}
									}
								}
							}
						}
					} elseif ( 'BEHAVIOR_AVOIDANCE' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {
									foreach ( $assessment_arr['statements'] as $key => $value ) {

										$format = $value->format;
										$style  = $value->style;
										$text   = $value->text;

										if ( 'para' === $format && 'left' === $style ) {
											$o .= '<p><strong><em>' . esc_html( $text ) . '</em></strong></p>';
										} elseif ( 'list' === $format && 'bullets' === $style ) {
											$o .= '<ul>';
											$o .= '<li>' . esc_html( $text ) . '</li>';
											$o .= '</ul>';
										}
									}
								}
							}
						}
					} elseif ( 'TWASTERS' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {

							foreach ( $gen_char_par_arr as $key => $value ) {
								if ( array_key_exists( $value, $assessment_arr['wasters'] ) ) {

									foreach ( $assessment_arr['wasters'] as $key => $value ) {

										$o .= '<h3>' . esc_html( $value->title ) . '</h3>';
										$o .= '<p>' . esc_html( $value->description ) . '</p>';

										foreach ( $value->statements as $key => $value ) {

											$o .= '<h5>' . esc_html( $value->subhead ) . '</h5>';
											$o .= '<ul>';
											foreach ( $value->stmts as $key => $value ) {
												$o .= '<li>' . esc_html( $value ) . '</li>';
											}
											$o .= '</ul>';

										}
									}
								}
							}
						}
					} elseif ( 'DFSTRWEAK' === $type || 'DFENGSTRESS' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( 0 === $value ) {

									$o .= '<h5>' . esc_html( $assessment_arr['left_side']->title ) . '</h5>';
									$o .= '<ul>';

									foreach ( $assessment_arr['left_side']->statements as $key => $value ) {
										$o .= '<li>' . esc_html( $value ) . '</li>';
									}

									$o .= '</ul>';

								}

								if ( 1 === $value ) {

									$o .= '<h5>' . esc_html( $assessment_arr['right_side']->title ) . '</h5>';
									$o .= '<ul>';

									foreach ( $assessment_arr['right_side']->statements as $key => $value ) {
										$o .= '<li>' . esc_html( $value ) . '</li>';
									}

									$o .= '</ul>';
								}
							}
						}
					} elseif ( 'INTEGRATIONINTRO_DF' === $type || 'BLENDING_DF_INTRO' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {
									foreach ( $assessment_arr['statements'] as $key => $value ) {

										if ( 'para' === $value->format && 'left' === $value->style ) {
											$o .= '<p><strong><em>' . esc_html( $value->text ) . '</em></strong></p>';
										} elseif ( 'list' === $value->format && 'bullets' === $value->style ) {
											$o .= '<ul>';
											$o .= '<li>' . esc_html( $value->text ) . '</li>';
											$o .= '</ul>';
										}
									}
								}
							}
						}
					} elseif ( 'POTENTIALSTR_DR' === $type || 'POTENTIALCONFLIT_DR' === $type || 'IDEALENVDR' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {
								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {
									foreach ( $assessment_arr['statements'] as $key => $value ) {

										$o .= '<ul>';
										foreach ( $value->stmts as $key => $value ) {
											$o .= '<li>' . esc_html( $value ) . '</li>';
										}
										$o .= '</ul>';

									}
								}
							}
						}
					} elseif ( 'MOTIVATINGDR' === $type || 'MANAGINGDR' === $type ) {

						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {

									if ( isset( $assessment_arr['prefix'] ) && ! empty( $assessment_arr['prefix'] ) ) {
										$o .= '<h3>' . esc_html( $assessment_arr['prefix'] ) . '</h3>';
									}

									foreach ( $assessment_arr['statements'] as $key => $value ) {

										$o .= '<ul>';
										foreach ( $value->stmts as $key => $value ) {
											$o .= '<li>' . esc_html( $value ) . '</li>';
										}
										$o .= '</ul>';

									}
								}
							}
						}
					} elseif ( 'EQTABLES2' === $type ) {

						if ( is_array( $gen_char_par_arr ) && count( $gen_char_par_arr ) > 0 ) {

							foreach ( $assessment_arr['pages'] as $key => $value ) {

								$title = $value->title;
								$o    .= '<h4>' . $value->leadin . '</h4>';

								foreach ( $value->bullets as $key => $value ) {
									$o .= '<li>' . esc_html( $value ) . '</li>';
								}

								$o .= '</ul>';
							}
						}
					} else {

						// $type == 'VAL'.
						if ( is_array( $gen_char_par_arr ) && ! empty( $gen_char_par_arr ) ) {
							foreach ( $gen_char_par_arr as $key => $value ) {

								if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {
									$format = $assessment_arr['statements'][ $value ]->format;
									$style  = $assessment_arr['statements'][ $value ]->style;

									if ( 'para' === $format && 'indent' === $style ) {
										$o .= '<p>' . implode( ' ', $assessment_arr['statements'][ $value ]->stmts ) . '</p>';
									} elseif ( 'list' === $format && 'bullets' === $style ) {

										if ( isset( $assessment_arr['prefix'] ) && ! empty( $assessment_arr['prefix'] ) ) {
											$o .= '<h3>' . esc_html( $assessment_arr['prefix'] ) . '</h3>';
										}

										$o .= '<ul>';
										foreach ( $assessment_arr['statements'][ $value ]->stmts as $index => $checklist ) {
											$o .= '<li>' . esc_html( $checklist ) . '</li>';
										}
										$o .= '</ul>';

									} elseif ( 'list' === $format ) {

										$o .= '<ul>';
										foreach ( $assessment_arr['statements'][ $value ]->stmts as $index => $checklist ) {
											$o .= '<li>' . esc_html( $checklist ) . '</li>';
										}
										$o .= '</ul>';

									}
								}
							}
						}
					}
				} elseif ( 'select' === $gen_char_feedback ) {

					if ( 'GENCHAR' === $type || 'MOTGENCHAR' === $type ) {
						if ( ! empty( $gen_char_par_arr ) ) {

							$get_bool = false;
							$o       .= '<div class="selectFeedbackData" id="' . $type . '">';

							if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
								foreach ( $selected_all_that_apply as $key => $value ) {

									if ( $value['type'] === $type ) {
										foreach ( $value['statements'] as $key => $value ) {

											$ident = $value['ident'];
											if ( ! in_array( $key, $gen_char_par_arr ) ) { // phpcs:ignore
												$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '"></ul>';
												continue;
											}

											$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';
											foreach ( $value['stmts'] as $key => $value ) {

												$checked = ( 1 == $value['value'] ) ? 'checked= checked' : ''; // phpcs:ignore
												$randstr = generate_random_string( 20 );
												$o      .= '<input type="checkbox"
												name="' . esc_attr( $ident ) . '[]"
												id="isSelected-' . esc_attr( $randstr ) . '"
												value=""
												text="' . str_replace( '"', '&quot;', stripslashes( $value['text'] ) ) . '"
												ident="' . esc_attr( $ident ) . '" ' . esc_attr( $checked ) . ' />
												<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $value['text'] ) . '</span></label>';
											}
											$o .= '</ul>';
										}
										$get_bool  = true;
										$in_select = true;
									}
								}
							}

							if ( ! $get_bool ) {
								if ( isset( $assessment_arr['statements'] ) ) {
									foreach ( $assessment_arr['statements'] as $key => $value ) {

										$format = $value->format;
										$style  = $value->style;
										$ident  = $value->ident;

										if ( ! in_array( $key, $gen_char_par_arr ) ) { // phpcs:ignore
											$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '"></ul>';
											continue;
										}

										$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';
										if ( 'para' === $format && 'indent' === $style ) {
											foreach ( $value->stmts as $index => $checklist ) {
												$randstr = generate_random_string( 20 );
												$o      .= '<input type="checkbox" name="' . esc_attr( $ident ) . '[]" id="isSelected-' . esc_attr( $randstr ) . '" value="" text="' . str_replace( '"', '&quot;', stripslashes( $checklist ) ) . '" ident="' . esc_attr( $ident ) . '" /> <label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $checklist ) . '</span></label>';
											}
											$in_select = true;
										}
										$o .= '</ul>';
									}
								}
							}

							$o .= '</div>';

							if ( $in_select ) {
								$o .= '<br>
								<button
									id="isSelected"
									link_id="' . esc_attr( $link_id ) . '"
									data-type="' . esc_attr( $type ) . '"
									class="isSelected ' . esc_attr( $type ) . '-subbtn" >Submit</button>
								<br><div id="responseIsSelected"></div>';
							}
						}
					} elseif ( 'POTENTIALSTR_DR' === $type || 'POTENTIALCONFLIT_DR' === $type || 'IDEALENVDR' === $type || 'MOTIVATINGDR' === $type || 'MANAGINGDR' === $type ) {

						if ( ! empty( $gen_char_par_arr ) ) {

							$get_bool = false;
							$o       .= '<div class="selectFeedbackData" id="' . $type . '">';

							if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
								foreach ( $selected_all_that_apply as $key => $value ) {

									if ( $value['type'] === $type ) {

										foreach ( $value['statements'] as $key => $value ) {

											$ident = $value['ident'];
											$o    .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';

											foreach ( $value['stmts'] as $key => $value ) {

												$checked = ( $value['value'] == 1 ) ? 'checked= checked' : ''; // phpcs:ignore
												$randstr = generate_random_string( 20 );

												$o .= '<input
													type="checkbox"
													name="' . esc_attr( $ident ) . '[]"
													id="isSelected-' . esc_attr( $randstr ) . '"
													value=""
													text="' . str_replace( '"', '&quot;', stripslashes( $value['text'] ) ) . '"
													ident="' . esc_attr( $ident ) . '" ' . esc_attr( $checked ) . ' />
													<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $value['text'] ) . '</span></label>';
											}
											$o .= '</ul>';
										}
										$get_bool  = true;
										$in_select = true;
									}
								}
							}

							if ( ! $get_bool ) {
								if ( isset( $assessment_arr['statements'] ) ) {

									foreach ( $assessment_arr['statements'] as $key => $value ) {

										$format = $value->format;
										$style  = $value->style;
										$ident  = $value->ident;

										$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';
										foreach ( $value->stmts as $index => $checklist ) {

											$randstr = generate_random_string( 20 );

											$o .= '<input
												type="checkbox"
												name="' . esc_attr( $ident ) . '[]"
												id="isSelected-' . esc_attr( $randstr ) . '"
												value=""
												text="' . str_replace( '"', '&quot;', stripslashes( $checklist ) ) . '"
												ident="' . esc_attr( $ident ) . '" />
												<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $checklist ) . '</span></label>';
										}
										$in_select = true;
										$o        .= '</ul>';
									}
								}
							}

							$o .= '</div>';

							if ( $in_select ) {
								$o .= '<br><button
									id="isSelected"
									link_id="' . esc_attr( $link_id ) . '"
									data-type="' . esc_attr( $type ) . '"
									class="isSelected ' . esc_attr( $type ) . '-subbtn">Submit</button>
									<br><div id="responseIsSelected"></div>';
							}
						}
					} elseif ( 'EQTABLES2' === $type ) {

						// EQTABLES2 Select.
						if ( ! empty( $gen_char_par_arr ) ) {

							$get_bool  = false;
							$in_select = false;

							$o .= '<div class="selectFeedbackData" id="' . $eqtype . '">';

							if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {

								foreach ( $selected_all_that_apply as $key => $value ) {

									if ( $value['type'] === $eqtype ) {

										foreach ( $value['statements'] as $key => $value ) {
											$ident = $value['ident'];

											$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';

											if ( isset( $value['stmts'] ) ) {
												foreach ( $value['stmts'] as $key => $value ) {

													$checked = ( $value['value'] == 1 ) ? 'checked= checked' : ''; // phpcs:ignore
													$randstr = generate_random_string( 20 );

													$o .= '<input
														type="checkbox"
														name="' . esc_attr( $ident ) . '[]"
														id="isSelected-' . esc_attr( $randstr ) . '"
														value=""
														text="' . str_replace( '"', '&quot;', stripslashes( $value['text'] ) ) . '"
														ident="' . esc_attr( $ident ) . '" ' . esc_attr( $checked ) . ' />
														<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $value['text'] ) . '</span></label>';

												}
											}
											$o .= '</ul>';

										}
										$get_bool  = true;
										$in_select = true;
									}
								}
							}

							if ( ! $get_bool ) {
								$count = 0;
								foreach ( $gen_char_par_arr as $key => $value ) {

									$ident = str_replace( ' ', '-', strtolower( $assessment_arr['pages'][0]->title ) );

									$o .= '<ul id="' . esc_attr( $eqtype ) . '" count="' . esc_attr( $value ) . '">';

									foreach ( $assessment_arr['pages'][0]->bullets as $index => $checklist ) {
										$ident   = str_replace( ' ', '-', strtolower( $assessment_arr['pages'][0]->title ) );
										$randstr = generate_random_string( 20 );

										$o .= '<input
											type="checkbox"
											name="' . esc_attr( $ident ) . '[]"
											id="isSelected-' . esc_attr( $randstr ) . '"
											value=""
											text="' . str_replace( '"', '&quot;', esc_attr( $checklist ) ) . '"
											ident="' . esc_attr( $ident ) . '" />
											<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . esc_html( $checklist ) . '</span></label>';

										$in_select = true;
									}

									$o .= '</ul>';
									$count++;
								}
							}

							$o .= '</div>';
							if ( $in_select ) {
								$o .= '<br><button
									id="isSelected"
									link_id="' . esc_attr( $link_id ) . '"
									data-type="' . esc_attr( $type ) . '"
									class="isSelected ' . esc_attr( $type ) . '-subbtn" >Submit</button>
									<br><div id="responseIsSelected"></div>';
							}
						}
					} elseif ( 'EQGENCHAR' === $type ) {

						// EQGENCHAR Select.
						if ( ! empty( $gen_char_par_arr ) ) {

							$get_bool  = false;
							$in_select = false;

							$o .= '<div class="selectFeedbackData" id="' . $type . '">';

							if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
								foreach ( $selected_all_that_apply as $key => $value ) {

									if ( $value['type'] === $type ) {
										foreach ( $value['statements'] as $key => $value ) {

											$ident = $value['ident'];
											$o    .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';

											if ( isset( $value['stmts'] ) ) {
												foreach ( $value['stmts'] as $key => $value ) {

													$checked = ( $value['value'] == 1 ) ? 'checked= checked' : ''; // phpcs:ignore
													$randstr = generate_random_string( 20 );

													$o .= '<input
														type="checkbox"
														name="' . esc_attr( $ident ) . '[]"
														id="isSelected-' . esc_attr( $randstr ) . '"
														value=""
														text="' . str_replace( '"', '&quot;', stripslashes( $value['text'] ) ) . '"
														ident="' . esc_attr( $ident ) . '" ' . esc_attr( $checked ) . ' />
														<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $value['text'] ) . '</span></label>';
												}
											}
											$o .= '</ul>';
										}
										$get_bool  = true;
										$in_select = true;
									}
								}
							}

							if ( ! $get_bool ) {

								foreach ( $gen_char_par_arr as $key => $value ) {
									if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {

										$format = $value->format;
										$style  = $value->style;
										$ident  = $value->id;

										$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $value ) . '">';

										$ident = $assessment_arr['statements'][ $value ]->title;

										foreach ( $assessment_arr['statements'][ $value ]->statements as $index => $checklist ) {

											$randstr = generate_random_string( 20 );

											$o .= '<input
												type="checkbox"
												name="' . esc_attr( $ident ) . '[]"
												id="isSelected-' . esc_attr( $randstr ) . '"
												value=""
												text="' . str_replace( '"', '&quot;', esc_attr( $checklist ) ) . '"
												ident="' . esc_attr( $ident ) . '" />
												<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . esc_html( $checklist ) . '</span></label>';

											$in_select = true;

										}
										$o .= '</ul>';

									}
								}
							}

							$o .= '</div>';
							if ( $in_select ) {
								$o .= '<br><button
									id="isSelected"
									link_id="' . esc_attr( $link_id ) . '"
									data-type="' . esc_attr( $type ) . '"
									class="isSelected ' . esc_attr( $type ) . '-subbtn">Submit</button>
									<br><div id="responseIsSelected"></div>';
							}
						}
					} else {
						if ( ! empty( $gen_char_par_arr ) ) {

							$get_bool = false;

							$o .= '<div class="selectFeedbackData" id="' . $type . '">';

							if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
								foreach ( $selected_all_that_apply as $key => $value ) {

									if ( $value['type'] === $type ) {
										foreach ( $value['statements'] as $key => $value ) {

											$ident = $value['ident'];
											if ( ! in_array( $key, $gen_char_par_arr ) ) { // phpcs:ignore
												$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '"></ul>';
												continue;
											}

											$o .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $key ) . '">';
											foreach ( $value['stmts'] as $key => $value ) {

												$checked = ( $value['value'] == 1 ) ? 'checked= checked' : ''; // phpcs:ignore
												$randstr = generate_random_string( 20 );

												$o .= '<input
													type="checkbox"
													name="' . esc_attr( $ident ) . '[]"
													id="isSelected-' . esc_attr( $randstr ) . '"
													value=""
													text="' . str_replace( '"', '&quot;', stripslashes( $value['text'] ) ) . '"
													ident="' . esc_attr( $ident ) . '" ' . esc_attr( $checked ) . ' />
													<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . stripslashes( $value['text'] ) . '</span></label>';
											}
											$o .= '</ul>';
										}
										$get_bool  = true;
										$in_select = true;
									}
								}
							}

							if ( ! $get_bool ) {
								foreach ( $gen_char_par_arr as $key => $value ) {
									if ( array_key_exists( $value, $assessment_arr['statements'] ) ) {

										$format = $assessment_arr['statements'][ $value ]->format;
										$style  = $assessment_arr['statements'][ $value ]->style;
										$ident  = $assessment_arr['statements'][ $value ]->ident;
										$o     .= '<ul id="' . esc_attr( $ident ) . '" count="' . esc_attr( $value ) . '">';

										if (
											( 'para' === $format && 'indent' === $style ) ||
											( 'list' === $format && 'bullets' === $style ) ||
											( 'list' === $format )
											) {

											foreach ( $assessment_arr['statements'][ $value ]->stmts as $index => $checklist ) {

												$randstr = generate_random_string( 20 );

												$o .= '<input
													type="checkbox"
													name="' . esc_attr( $ident ) . '[]"
													id="isSelected-' . esc_attr( $randstr ) . '"
													value=""
													text="' . str_replace( '"', '&quot;', esc_attr( $checklist ) ) . '"
													ident="' . esc_attr( $ident ) . '" />
													<label for="isSelected-' . esc_attr( $randstr ) . '"><span>' . esc_html( $checklist ) . '</span></label>';
											}
											$in_select = true;

										}
										$o .= '</ul>';
									}
								}
							}

							$o .= '</div>';
							if ( $in_select ) {
								$o .= '<br><button
									id="isSelected"
									link_id="' . esc_attr( $link_id ) . '"
									data-type="' . esc_attr( $type ) . '"
									class="isSelected ' . esc_attr( $type ) . '-subbtn" >Submit</button>
									<br><div id="responseIsSelected"></div>';
							}
						}
					}

					$o .= '<div class="getSelectArr">';
					foreach ( $gen_char_par_arr as $key => $value ) {
						$o .= '<input type="hidden" value="' . esc_attr( $value ) . '" />';
					}
					$o .= '</div>';

				} elseif ( 'display' === $gen_char_feedback ) {

					if ( 'POTENTIALSTR_DR' === $type || 'POTENTIALCONFLIT_DR' === $type || 'IDEALENVDR' === $type || 'MOTIVATINGDR' === $type || 'MANAGINGDR' === $type ) {

						$o .= '<div class="selectFeedbackData">';
						if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {

							foreach ( $selected_all_that_apply as $key => $value ) {
								if ( $value['type'] === $type ) {

									foreach ( $value['statements'] as $key => $value ) {
										$o .= '<ul>';

										if ( isset( $value['stmts'] ) ) {

											foreach ( $value['stmts'] as $key => $value ) {

												if ( $value['value'] == 1 ) { // phpcs:ignore
													$o .= '<li>' . stripslashes( $value['text'] ) . '</li>';
												}
											}
										}
										$o .= '</ul>';
									}
								}
							}
						}
						$o .= '</div>';
					} elseif ( 'EQGENCHAR' === $type ) {

						$o .= '<div class="selectFeedbackData">';
						if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
							foreach ( $selected_all_that_apply as $key => $value ) {

								if ( $value['type'] === $type ) {
									foreach ( $value['statements'] as $key => $value ) {

										if ( ! in_array( $key, $gen_char_par_arr ) ) { // phpcs:ignore
											$o .= '<ul></ul>';
											continue;
										}

										$o .= '<ul>';

										if ( isset( $value['stmts'] ) ) {
											foreach ( $value['stmts'] as $key => $value ) {

												if ( $value['value'] == 1 ) { // phpcs:ignore
													$o .= '<li>' . stripslashes( $value['text'] ) . '</li>';
												}
											}
										}
										$o .= '</ul>';

									}
								}
							}
						}
						$o .= '</div>';
					} else {

						$o .= '<div class="selectFeedbackData">';
						if ( is_array( $selected_all_that_apply ) || is_object( $selected_all_that_apply ) ) {
							foreach ( $selected_all_that_apply as $key => $value ) {

								if ( $value['type'] === $type || $value['type'] === $eqtype ) {
									foreach ( $value['statements'] as $key => $value ) {

										if ( ! in_array( $key, $gen_char_par_arr ) && $type != 'EQTABLES2' ) { // phpcs:ignore
											$o .= '<ul></ul>';
											continue;
										}

										$o .= '<ul>';
										if ( isset( $value['stmts'] ) ) {

											foreach ( $value['stmts'] as $key => $value ) {

												if ( $value['value'] == 1 ) { // phpcs:ignore
													$o .= '<li>' . stripslashes( $value['text'] ) . '</li>';
												}
											}
										}
										$o .= '</ul>';
									}
								}
							}
						}
						$o .= '</div>';
					}
				}
			} else {
				$msg = __( 'No feedback found. Please contact to the administrator of this site.', 'tti-platform' );
				$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			}
		} else {
			$msg = __( 'This assessment has been suspended.', 'tti-platform' );
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
		}

		/**
		 * Filter to update feedback assessment array
		*
		* @since  1.2
		*/
		$assessment_arr = apply_filters( 'ttisi_platform_assessments_feedback_final_array', $assessment_arr );

		/**
		 * Filter to update feedback final string
		*
		* @since  1.2
		*/
		$o = apply_filters( 'ttisi_platform_assessments_feedback_final_string', $o );

		/**
		 * Fires after assessment feedback shortcode called
		*
		* @since   1.2
		*/
		do_action( 'ttisi_platform_after_assessments_feedback_shortcode', $assessment_arr, $o );
		$o .= '</div>';
		return $o;
	}

	/**
	 * Function to handle assessment graphic feedback shortcode.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param array  $atts contains shortcode attributes.
	 * @param string $content contains shortcode content.
	 * @param string $tag contains shortcode tags.
	 *
	 * @return string
	 */
	public function mi_assessment_graphic_feedback_sccb( $atts = array(), $content = null, $tag = '' ) {

		global $wpdb, $current_usr;

		// Early bail if user is not logged-in.
		if ( ! is_user_logged_in() ) {

			$msg = __( 'Please logged-in to complete the feedback.', 'tti-platform' );

			$o  = '<div>';
			$o .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o .= '</div>';
			return $o;
		}

		// Enqueue style and script.
		$this->mi_assessments_enqueue_styles_scripts();

		/**
		 * Fires before graphic feedback
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_before_graphic_feedback' );

		$o               = '';
		$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
		$assessment_atts = shortcode_atts(
			array(
				'assess_id'        => '',
				'type'             => '',
				'intro'            => '',
				'count'            => '',
				'is_graph_adapted' => '',
				'is_graph_natural' => '',
				'both'             => '',
				'width'            => '',
				'para1'            => '',
				'para2'            => '',
			),
			$atts,
			$tag
		);

		$current_usr = wp_get_current_user();
		$user_id     = $current_usr->ID;
		$assess_id   = $assessment_atts['assess_id'];

		// Get the current assessment status.
		$asses_status = get_post_meta( $assess_id, 'status_assessment', true );

		if ( 'Suspended' !== $asses_status ) {

			$type        = $assessment_atts['type'];
			$page_indent = 1;

			/* check for EQTables type */
			if ( strpos( $type, 'EQTABLES2' ) !== false ) {
				$eqtype           = $type;
				$eqtables_section = explode( '-', $type );
				$page_indent      = $eqtables_section[1];
				$type             = 'EQTABLES2';
			}

			$intro            = $assessment_atts['intro'];
			$count            = $assessment_atts['count'];
			$is_graph_adapted = $assessment_atts['is_graph_adapted'];
			$is_graph_natural = $assessment_atts['is_graph_natural'];
			$both             = $assessment_atts['both'];
			$width            = $assessment_atts['width'];
			$para1            = $assessment_atts['para1'];
			$para2            = $assessment_atts['para2'];
			$link_id          = get_post_meta( $assess_id, 'link_id', true );

			// Get assessment version.
			$asses_version = get_current_user_assess_version( $user_id, $link_id );

			// Get latest completed assessment results.
			$columns = 'assessment_result';
			$results = get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version, $columns );

			if ( $results ) {

				if ( 'PIAVBARS12HIGH' === $type || 'PIAVBARS12MED' === $type || 'PIAVBARS12LOW' === $type ) {

					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							$title          = $section_data->header->titles;
							$intro_header   = $section_data->header->text;
							$behaviors      = $section_data->driving_forces;
							$assessment_arr = array(
								'title'     => $title,
								'intro'     => $intro_header,
								'behaviors' => $behaviors,
							);
						}
					}
					if ( 'yes' === $intro ) {
						$o .= '<p><em>' . esc_html( $assessment_arr['intro'] ) . '</em></p>';
					}
					if ( 'all' === $count ) {

						if ( isset( $assessment_arr['behaviors'] ) && ! empty( $assessment_arr['behaviors'] ) && array_filter( $assessment_arr['behaviors'] ) ) {
							foreach ( $assessment_arr['behaviors'] as $key => $value ) {
								++$key;
								$name  = $value->name;
								$text  = $value->text;
								$order = $value->order;
								$o    .= '<p><strong>' . esc_html( $order ) . '. ' . esc_html( $name ) . ' - </strong> ' . esc_html( $text ) . '</p>';
								$o    .= '<img src="' . esc_url( $value->url ) . '" alt="' . esc_attr( $value->text ) . '" />';
							}
						}
					} else {
						$gen_char_par_arr = explode( ',', $count );
						$behaviors_arr    = array();
						foreach ( $assessment_arr['behaviors'] as $key => $value ) {
							$behaviors_arr[] = array(
								'order' => $value->order,
								'url'   => $value->url,
								'text'  => $value->text,
								'name'  => $value->name,
							);
						}
						foreach ( $behaviors_arr as $key => $value ) {
							++$key;
							if ( in_array( $value['order'], $gen_char_par_arr ) ) { // phpcs:ignore
								$o .= '<p><strong>' . esc_html( $value['order'] ) . '. ' . esc_html( $value['name'] ) . ' - </strong> ' . esc_html( $value['text'] ) . '</p>';
								$o .= '<img src="' . esc_url( $value['url'] ) . '" alt="' . esc_attr( $value['text'] ) . '" />';
							}
						}
					}
				}

				if ( 'EQTABLES2' === $type ) {

					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							$pages[]        = $section_data->$page_indent;
							$ident_of_eq    = strtolower( $pages[0]->title );
							$intro          = $pages[0]->description;
							$leading_text   = $pages[0]->leadin;
							$assessment_arr = array(
								'intro'     => $intro,
								'lead_text' => $leading_text,
								'pages'     => $pages,
							);
						}
					}

					if ( 'all' === $count || 1 === $count ) {
						if ( isset( $assessment_arr['pages'] ) && ! empty( $assessment_arr['pages'] ) && array_filter( $assessment_arr['pages'] ) ) {
							foreach ( $assessment_arr['pages'] as $key => $value ) {
								++$key;
								$o .= '<img src="' . esc_url( $value->url ) . '" alt="' . esc_attr( $value->text ) . '" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}

				if ( 'EQWHEEL' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							$title          = $section_data->header->titles;
							$intro_header   = $section_data->header->text;
							$graph_url      = $section_data->graph_url;
							$assessment_arr = array(
								'title'     => $title,
								'intro'     => $intro_header,
								'graph_url' => $graph_url,
							);
						}
					}

					if ( 'yes' === $intro ) {
						$o .= '<p><em>' . esc_html( $assessment_arr['intro'] ) . '</em></p>';
					}

					if ( 1 === $count ) {
						$o .= '<img src="' . esc_url( $assessment_arr['graph_url'] ) . '" alt="' . esc_attr( $assessment_arr['title'] ) . '" width="' . esc_attr( $width ) . '" />';
					}
				}

				if ( 'EQRESULTS2' === $type || 'EQSCOREINFO2' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type == $type ) { // phpcs:ignore
							$title          = $section_data->header->titles;
							$intro_header   = $section_data->lead_text;
							$scores         = $section_data->scores;
							$assessment_arr = array(
								'title'  => $title,
								'intro'  => $intro_header,
								'scores' => $scores,
							);
						}
					}

					if ( 'yes' === $intro ) {
						$o .= '<p>' . esc_html( $assessment_arr['intro'] ) . '</p>';
					}

					if ( 'all' === $count ) {
						if ( isset( $assessment_arr['scores'] ) && ! empty( $assessment_arr['scores'] ) && array_filter( $assessment_arr['scores'] ) ) {
							foreach ( $assessment_arr['scores'] as $key => $value ) {
								++$key;

								$o .= '<p><strong>' . esc_html( $value->name ) . '  </strong> : ' . esc_html( $value->description ) . ' </p>';
								$o .= '<img src="' . esc_url( $value->url ) . '" alt="' . esc_attr( $value->name ) . '" width="' . esc_attr( $width ) . '" />';
							}
						}
					} else {
						$gen_char_par_arr = explode( ',', $count );
						$behaviors_arr    = array();

						foreach ( $assessment_arr['scores'] as $key => $value ) {
							$behaviors_arr[] = array(
								'order' => $value->id,
								'url'   => $value->url,
								'text'  => $value->description,
								'name'  => $value->name,
							);
						}

						foreach ( $behaviors_arr as $key => $value ) {
							++$key;
							if ( in_array( $value['order'], $gen_char_par_arr ) ) { // phpcs:ignore
								$o .= '<p><strong>' . esc_html( $value['name'] ) . '  </strong> :' . esc_html( $value['text'] ) . '</p>';
								$o .= '<img src="' . esc_url( $value['url'] ) . '" alt="' . esc_attr( $value['text'] ) . '" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}

				if ( 'PGRAPH12' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							$title          = $section_data->header->titles;
							$intro_header   = $section_data->header->text;
							$graphs         = $section_data->graphs;
							$assessment_arr = array(
								'graph' => $graphs,
							);
						}
					}

					if ( 'all' === $count ) {
						$full_url = $assessment_arr['graph']->full_url;
						$o       .= '<img src="' . esc_attr( $full_url ) . '" alt="" />';
					} else {
						$gen_char_par_arr = explode( ',', $count );
						foreach ( $gen_char_par_arr as $graph ) {
							$knowledge     = $assessment_arr['graph']->row1_url;
							$utility       = $assessment_arr['graph']->row2_url;
							$surroundings  = $assessment_arr['graph']->row3_url;
							$others        = $assessment_arr['graph']->row4_url;
							$power         = $assessment_arr['graph']->row5_url;
							$methodologies = $assessment_arr['graph']->row6_url;

							if ( 'Knowledge' === $graph ) {
								$o .= '<img src="' . esc_attr( $knowledge ) . '" alt="" />';
							}

							if ( 'Utility' === $graph ) {
								$o .= '<img src="' . esc_attr( $utility ) . '" alt="" />';
							}

							if ( 'Surroundings' === $graph ) {
								$o .= '<img src="' . esc_attr( $surroundings ) . '" alt="" />';
							}

							if ( 'Others' === $graph ) {
								$o .= '<img src="' . esc_attr( $others ) . '" alt="" />';
							}

							if ( 'Power' === $graph ) {
								$o .= '<img src="' . esc_attr( $power ) . '" alt="" />';
							}

							if ( 'Methodologies' === $graph ) {
								$o .= '<img src="' . esc_attr( $methodologies ) . '" alt="" />';
							}
						}
					}
				}

				if ( 'SABARS' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							$title          = $section_data->header->titles;
							$intro_header   = $section_data->header->text;
							$behaviors      = $section_data->behaviors;
							$assessment_arr = array(
								'title'     => $title,
								'intro'     => $intro_header,
								'behaviors' => $behaviors,
							);
						}
					}

					if ( 'yes' === $intro ) {
						$o .= '<p><em>' . esc_html( $assessment_arr['intro'] ) . '</em></p>';
					}

					if ( 'all' === $count ) {

						if ( isset( $assessment_arr['behaviors'] ) && ! empty( $assessment_arr['behaviors'] ) && array_filter( $assessment_arr['behaviors'] ) ) {
							foreach ( $assessment_arr['behaviors'] as $key => $value ) {
								++$key;
								$arr_bold_after = $value->text;
								$arr_bold       = explode( ':', $value->text, 2 );
								$titlearr_bold  = $arr_bold[0];
								$text           = substr( $arr_bold_after, ( strpos( $arr_bold_after, ':' ) ? 0 : -1 ) + 1 ); // TASSAWER SHORT TERNARY.

								$o .= '<p><strong>' . esc_html( $key ) . '. ' . esc_html( $titlearr_bold ) . ' - </strong> ' . esc_html( $text ) . '</p>';
								$o .= '<img src="' . esc_attr( $value->url ) . '" alt="' . esc_attr( $value->text ) . '" />';
							}
						}
					} else {

						$gen_char_par_arr = explode( ',', $count );
						$behaviors_arr    = array();
						foreach ( $assessment_arr['behaviors'] as $key => $value ) {
							$behaviors_arr[] = array(
								'order' => $value->order,
								'url'   => $value->url,
								'text'  => $value->text,
							);
						}
						foreach ( $behaviors_arr as $key => $value ) {
							++$key;
							if ( in_array( $value['order'], $gen_char_par_arr ) ) { // phpcs:ignore
								$order = $value['order'];
								$url   = $value['url'];
								$text  = $value['text'];

								$arr_bold_after = $text;
								$arr_bold       = explode( ':', $text, 2 );
								$titlearr_bold  = $arr_bold[0];
								$text           = substr( $arr_bold_after, ( strpos( $arr_bold_after, ':' ) ? 0 : -1 ) + 1 ); // TASSAWER SHORT TERNARY.

								$o .= '<p><strong>' . esc_html( $key ) . '. ' . esc_html( $titlearr_bold ) . ' - </strong> ' . esc_html( $text ) . '</p>';
								$o .= '<img src="' . esc_attr( $url ) . '" alt="' . esc_attr( $text ) . '" />';
							}
						}
					}
				}

				if ( 'NORMS12' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {

							$assessment_arr = array(
								'title'          => $section_data->header->titles,
								'intro'          => $section_data->header->text,
								'par1'           => $section_data->par1,
								'par2'           => $section_data->par2,
								'driving_forces' => $section_data->driving_forces,
							);
						}
					}

					if ( 'yes' === $intro ) {
						$o .= '<p><em>' . esc_html( $assessment_arr['intro'] ) . '</em></p>';
					}
					if ( 'yes' === $para1 ) {
						$o .= '<p>' . esc_html( $assessment_arr['par1'] ) . '</p>';
					}
					if ( 'yes' === $para2 ) {
						$o .= '<p>' . esc_html( $assessment_arr['par2'] ) . '</p>';
					}

					if ( 'all' === $count ) {
						if ( isset( $assessment_arr['driving_forces'] ) && ! empty( $assessment_arr['driving_forces'] ) && array_filter( $assessment_arr['driving_forces'] ) ) {
							foreach ( $assessment_arr['driving_forces'] as $key => $value ) {
								++$key;

								$o .= '<p><strong>' . esc_html( $key ) . '. ' . esc_html( $value->name ) . ' - </strong> ' . esc_html( $value->text ) . '</p>';
								$o .= '<img src="' . esc_attr( $value->url ) . '" alt="' . esc_html( $value->name ) . '" />';
							}
						}
					} else {

						$gen_char_par_arr = explode( ',', $count );
						$behaviors_arr    = array();

						foreach ( $assessment_arr['driving_forces'] as $key => $value ) {
							$behaviors_arr[] = array(
								'url'  => $value->url,
								'text' => $value->text,
								'name' => $value->name,
							);
						}

						foreach ( $behaviors_arr as $key => $value ) {
							++$key;
							if ( in_array( $key, $gen_char_par_arr ) ) { // phpcs:ignore

								$o .= '<p><strong>' . esc_html( $key ) . '. ' . esc_html( $value['name'] ) . ' - </strong> ' . esc_html( $value['text'] ) . '</p>';
								$o .= '<img src="' . esc_attr( $value['url'] ) . '" alt="' . esc_attr( $value['name'] ) . '" />';
							}
						}
					}
				}

				if ( 'MICHART1' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();
					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							if ( 'yes' === $is_graph_natural ) {
								$o .= '<img src="' . esc_url( $section_data->graph_url ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}

				if ( 'MICHART2' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();
					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {
							if ( 'yes' === $is_graph_adapted ) {
								$o .= '<img src="' . esc_url( $section_data->graph_url ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}

				if ( 'SAGRAPH' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();

					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {

							if ( 'yes' === $is_graph_adapted || 'yes' === $both ) {
								$o .= '<img src="' . esc_url( $section_data->adapted ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}

							if ( 'yes' === $is_graph_natural || 'yes' === $both ) {
								$o .= '<img src="' . esc_url( $section_data->natural ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}

				if ( 'WHEEL' === $type ) {
					$report_sections = unserialize( $results->assessment_result ); // phpcs:ignore
					$sections        = $report_sections->report->sections;
					$assessment_arr  = array();
					foreach ( $sections as $section_data ) {
						if ( $section_data->type === $type ) {

							if ( 'yes' === $both ) {
								$o .= '<img src="' . esc_url( $section_data->wheel->url ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}

							if ( 'yes' === $is_graph_adapted ) {
								$o .= '<img src="' . esc_url( $section_data->wheel->adapted->url ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}

							if ( 'yes' === $is_graph_natural ) {
								$o .= '<img src="' . esc_url( $section_data->wheel->natural->url ) . '" alt="" width="' . esc_attr( $width ) . '" />';
							}
						}
					}
				}
			} else {
				$o .= '<h2>No feedback found. Please contact to the administrator of this site.</h2>';
			}
		} else {
			$msg = __( 'This assessment has been suspended.', 'tti-platform' );
			$o   = '';
			$o  .= '<div class="assessment_disabled">';
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o  .= '</div>';
		}

		/**
		 * Filter to update feedback graphic assessment array
		 *
		 * @since  1.2
		 */
		$assessment_arr = apply_filters( 'ttisi_platform_assessments_feedback_graphic_final_array', $assessment_arr );

		/**
		 * Filter to update feedback final string
		 *
		 * @since  1.2
		 */
		$o = apply_filters( 'ttisi_platform_assessments_feedback_graphic_final_string', $o );

		/**
		 * Fires after graphic feedback
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_after_graphic_feedback', $assessment_arr, $o );

		return $o;
	}

	/**
	 * Function to handle assessment graphic feedback shortcode process.
	 *
	 * @since   1.0.0
	 * @param array  $atts contains shortcode attributes.
	 * @param string $content contains shortcode content.
	 * @param string $tag contains shortcode tags.
	 */
	public function mi_assessments_pdf_download_sccb( $atts = array(), $content = null, $tag = '' ) {

		/**
		 * Fires before graphic feedback
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_before_assessment_pdf_download_shortcode' );

		global $wpdb, $current_usr;

		// Enqueue style and script.
		$this->mi_assessments_enqueue_styles_scripts();

		$o               = '';
		$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
		$assessment_atts = shortcode_atts(
			array(
				'assess_id' => '',
			),
			$atts,
			$tag
		);

		$assess_id = $assessment_atts['assess_id'];

		// Get the current assessment status.
		$asses_status = get_post_meta( $assess_id, 'status_assessment', true );

		if ( 'Suspended' !== $asses_status ) {

			$post         = get_post( $assess_id );
			$slug         = $post->post_name;
			$post_meta    = get_post_custom( $assess_id );
			$link_id      = $post_meta['link_id']['0'];
			$print_report = $post_meta['print_report']['0'];

			if ( 'Yes' === $print_report ) {

				if ( is_user_logged_in() ) {

					$current_usr      = wp_get_current_user();
					$current_user     = $current_usr->ID;
					$assessment_table = $wpdb->prefix . 'assessments';

					// Get assessment version.
					$asses_version = get_current_user_assess_version( $current_user, $link_id );

					// Get latest completed assessment results.
					$results = get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version );

					if ( empty( $results ) ) {

						if ( $asses_version > 1 ) {
							--$asses_version;
						}

						$results = get_user_latest_completed_assessment_result( $current_user, $link_id, $asses_version );
					}

					if ( $results ) {

						// $pdf_download_url = esc_url( get_site_url() . '?version=' . $asses_version . '&assessment_id=' . $assess_id . '&user_id=' . $current_user );
						$pdf_download_url = esc_url( get_site_url() . '?version=' . $asses_version . '&assessment_id=' . $assess_id );
						$pdf_img_url      = esc_url( MI_PUBLIC_URL . 'images/download.png', 'https' );

						$o .= '<a
						assessment-id="' . esc_attr( $assess_id ) . '"
						class="download_pdf"
						href="' . esc_attr( $pdf_download_url ) . '"
						target="_blank">
							<img width="40" src="' . esc_attr( $pdf_img_url ) . '" alt="" />
							<span>Download Assessment Results</span>
						</a>';
					} else {
						$o .= '<h4>' . esc_html( 'No PDF Available' ) . '</h4>';
					}
				}
			}
		} else {
			$msg = __( 'This assessment has been suspended.', 'tti-platform' );
			$o   = '';
			$o  .= '<div class="assessment_disabled">';
			$o  .= '<h2>' . esc_html( $msg ) . '</h2>';
			$o  .= '</div>';
		}

		/**
		 * Filter to update pdf download final string
		 *
		 * @since  1.2
		 */
		$o = apply_filters( 'ttisi_platform_assessment_pdf_download_shortcode_final_string', $o );

		/**
		 * Fires after graphic feedback
		 *
		 * @since   1.2
		 */
		do_action( 'ttisi_platform_after_assessment_pdf_download_shortcode', $o );

		return $o;
	}

	/**
	 * Function to print PDF download button shortcode function.
	 *
	 * @since    1.6.3
	 *
	 * @param array $atts contains shortcode options.
	 * @param array $content contains shortcode content.
	 * @param array $tag contains shortcode tag.
	 * @return string contains download PDF link
	 */
	public function mi_assessment_print_pdf_button_sccb( $atts = array(), $content = null, $tag = '' ) {

		$atts            = array_change_key_case( (array) $atts, CASE_LOWER );
		$assessment_atts = shortcode_atts(
			array(
				'assess_id' => '',
				'type'      => 'type_one',
			),
			$atts,
			$tag
		);

		$assess_id   = $assessment_atts['assess_id'];
		$report_type = $assessment_atts['type'];

		if ( ! empty( $assess_id ) ) {

			// $pdf_download_url = esc_url( get_site_url() . '?version=' . $asses_version . '&assessment_id=' . $assess_id . '&user_id=' . $current_user );
			$pdf_download_url = esc_url( get_site_url() . '?report_type=' . $report_type . '&assess_id=' . $assess_id . '&tti_print_consolidation_report=1', 'https' );
			$pdf_img_url      = esc_url( MI_PUBLIC_URL . 'images/download.png', 'https' );

			$output = '<a
				style=""
				target="__blank"
				href="' . esc_attr( $pdf_download_url ) . '"
				class="tti_print_report_pdf"
				data-type="' . esc_attr( $assess_id ) . '"
				data-assessid="' . esc_attr( $report_type ) . '">
					<img width="40" src="' . esc_attr( $pdf_img_url ) . '" alt="" />
				</a>';

			return $output;
		}

	}

	/**
	 * Function for the listener shortcode showing on public end.
	 *
	 * @since   1.0.0
	 *
	 * @param array $atts contains shortcode options.
	 *
	 * @return string contains download PDF link
	 */
	public function mi_assessments_listener_sccb( $atts = array() ) {

		global $wpdb, $current_user;

		$update_limit_flag = true;

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		$get_link     = isset( $_get_data['link'] ) ? sanitize_text_field( $_get_data['link'] ) : '';
		$get_password = isset( $_get_data['password'] ) ? sanitize_text_field( $_get_data['password'] ) : '';

		if ( is_user_logged_in() && $get_link && $get_password ) {

			$mi_error_log = array(
				'ACTION' => 'Saving Assessment results into database. Redirecting to listener page...',
			);

			wp_get_current_user();
			$current_user_id = $current_user->ID;

			$listener_retake_asse_status = get_transient( 'assessmentListenerRetakeAsseStatus' . $current_user_id );

			if ( 'true' === $listener_retake_asse_status ) {
				$get_link = get_transient( 'assessmentListenerRetakeAsseLink' . $current_user_id );
			}

			// Get assessment version.
			$asses_version = get_current_user_assess_version( $current_user_id, $get_link );

			// Check if password exist.
			$assessment_table = $wpdb->prefix . 'assessments';

			// Execute the query to get results.
			$results = $wpdb->get_row( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					'SELECT * FROM %i WHERE user_id = %s AND password = %s AND version = %d', // phpcs:ignore
					$assessment_table,
					$current_user_id,
					$get_password,
					$asses_version
				)
			);

			$api_token     = $results->api_token;
			$respondent_id = $results->password;
			$account_id    = $results->account_id;
			$api_service   = $results->service_location;

			$status_of_the_user_ass = $results->status;

			// Check if that assessment is already completed or not.
			if ( 1 !== $status_of_the_user_ass ) {

				/* API v3.0 url */
				$url = $api_service . '/api/v3/reports?account_login=' . $account_id . '&respondent_passwd=' . $get_password;

				$api_response = $this->mi_api->mi_send_api_request( $url, $api_token, 'GET' );
				$api_response = json_decode( $api_response );

				if ( isset( $api_response ) && count( $api_response ) <= 0 ) {

					// If user exists assessment or not completed because of some reason.
					$first_name        = $results->first_name;
					$last_name         = $results->last_name;
					$email             = $results->email;
					$update_limit_flag = false;

					$mi_error_log['Assessment Status'] = 'User exit the assessment without completing it.';
					$mi_error_log['log_type']          = 'error';

				} else {

					$first_name = $api_response[0]->respondent->first_name;
					$last_name  = $api_response[0]->respondent->last_name;
					$email      = $api_response[0]->respondent->email;

					$mi_error_log['Assessment Status'] = 'User completed the assessment.';

				}

				$company      = $api_response[0]->respondent->company;
				$position_job = $api_response[0]->respondent->position_job;
				$gender       = $api_response[0]->respondent->gender;
				$report_id    = $api_response[0]->id;

				// User data for email template.
				$user_email_data = array(
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'email'        => $email,
					'company'      => $company,
					'position_job' => $position_job,
					'link_id'      => $get_link,
				);

				// phpcs:ignore
				$update_query = $wpdb->update(
					$assessment_table,
					array(
						'first_name'   => $first_name,
						'last_name'    => $last_name,
						'email'        => $email,
						'report_id'    => $report_id,
						'gender'       => $gender,
						'company'      => $company,
						'position_job' => $position_job,
						'updated_at'   => gmdate( 'Y-m-d H:i:s' ),
					),
					array(
						'user_id'  => $current_user_id,
						'password' => $get_password,
					)
				);

				if ( false === $update_query ) {
					$mi_error_log['Update Status'] = 'There is somthing wrong in updating the assessment record in database and save the report id.';
					$mi_error_log['Report ID']     = $report_id;
					$mi_error_log['log_type']      = 'error';

					$message = __( 'There is somthing wrong.', 'tti-platform' );
				} else {
					$mi_error_log['Update Status'] = 'Update the assessment record in database and save the report id.';
					$mi_error_log['Report ID']     = $report_id;

					/* API v3.0 url */
					$url = $api_service . '/api/v3/reports/' . $report_id;

					$api_response = $this->mi_api->mi_send_api_request( $url, $api_token, 'GET' );
					$api_response = json_decode( $api_response );

					$report_id = $api_response->report->info->reportid;

					$assessment_table = $wpdb->prefix . 'assessments';

					// Execute the query to get results.
					$results = $wpdb->get_row( // phpcs:ignore
						$wpdb->prepare( // phpcs:ignore
							'SELECT * FROM %i WHERE user_id = %s AND password = %s AND report_id = %s AND link_id = %s AND version = %d', // phpcs:ignore
							$assessment_table,
							$current_user_id,
							$get_password,
							$report_id,
							$get_link,
							$asses_version
						)
					);

					if ( $results ) {
						$update_query = $wpdb->update( // phpcs:ignore
							$assessment_table,
							array(
								'status'            => 1,
								'assessment_result' => serialize( $api_response ), // phpcs:ignore
							),
							array(
								'user_id'  => $current_user_id,
								'password' => $get_password,
							)
						);

						if ( false !== $update_query ) {
							$mi_error_log['Assessment Result'] = 'Updated the assessment result in database.';
						}
					}

					$args = array(
						'ID'           => $current_user_id,
						'first_name'   => $first_name,
						'last_name'    => $last_name,
						'display_name' => $first_name . ' ' . $last_name,
						'user_email'   => $email,
					);

					wp_update_user( $args );

					if ( $update_limit_flag ) {

						// Updating user limit for current assessment completed.
						$this->update_user_limit_after_comp_assess( $current_user_id, $get_link );

					}

					// Get assessment id.
					$assessment_id = get_assessment_post_id_by_link_id( $get_link );

					if ( $assessment_id && ! empty( $report_id ) ) {

						$send_rep_group_lead = get_post_meta( $assessment_id, 'send_rep_group_lead', true );
						$send_rep_group_lead = ( ! empty( $send_rep_group_lead ) ) ? $send_rep_group_lead : '';

						if ( 'Yes' === $send_rep_group_lead ) {

							$mi_error_log['User Details']    = $user_email_data;
							$mi_error_log['Email To Leader'] = 'Email Sent Option Checked. Sending mail to group leaders';

							// Intiate process of sending reports to group leaders.
							$error_log = initiate_group_leader_email_process( $report_id, $api_token, $api_service, $current_user_id, $user_email_data, $assessment_id );

							$mi_error_log = array_merge( $mi_error_log, $error_log );

						} else {
							$mi_error_log['User Details']    = $user_email_data;
							$mi_error_log['Email To Leader'] = 'Email Sent Option Not Checked.';
						}
					}

					// Loading bar after completing assessment.
					$this->loading_bar_completing_assessment( $current_user_id );
					$message = __( 'Your assessment has been successfully completed.', 'tti-platform' );

					/**
					 * Filter to take successful assessment message
					 *
					 * @since  1.2
					 */
					$message = apply_filters( 'ttisi_platform_success_take_assessments_msg', $message );
				}
			} else {
				$message = __( 'Assessment Completed.', 'tti-platform' );
			}

			/**
			 * Fires before take assessment successful message block.
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_before_success_take_assessments_msg_block' );

			$o  = '';
			$o .= '<div class="assessment_button">';
			$o .= '<h2>' . esc_html( $message ) . '<h2>';
			$o .= '</div>';

			/**
			 * Fires after take assessment successful message block.
			 *
			 * @since   1.2
			 */
			do_action( 'ttisi_platform_after_success_take_assessments_msg_block' );

			$log_type = ( isset( $mi_error_log['log_type'] ) && ! empty( $mi_error_log['log_type'] ) ) ? $mi_error_log['log_type'] : 'success';
			Mi_Error_Log::put_error_log( $mi_error_log, 'array', $log_type );

			return $o;
		}

	}

	/**
	 * Function to reduce user limit for taking assessment.
	 *
	 * @since   1.6
	 *
	 * @param integer $current_user contains current user id.
	 * @param string  $link_id contains link id.
	 */
	public function update_user_limit_after_comp_assess( $current_user, $link_id ) {

		global $wpdb;
		$users_limit = $wpdb->prefix . 'tti_users_limit';

		// Execute the query to get results.
		$results = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				'SELECT * FROM %i WHERE user_id = %s AND data_link = %s', // phpcs:ignore
				$users_limit,
				$current_user,
				$link_id
			)
		);

		if ( $results ) {

			$limit = 0;

			if ( isset( $results->limits ) && $results->limits > 0 ) {
				$limit = $results->limits - 1;
			}

			$update_query = $wpdb->update( // phpcs:ignore
				$users_limit,
				array(
					'limits' => $limit,
				),
				array(
					'user_id'   => $current_user,
					'data_link' => $link_id,
				)
			);
		}
	}

	/**
	 * Function to show loading bar after assessment completed.
	 *
	 * @since   1.2
	 *
	 * @param integer $user_id contains user id.
	 */
	public function loading_bar_completing_assessment( $user_id ) {
		$assessment_listener = get_transient( 'assessmentListener' . $user_id );
		?>
			<script type="text/javascript">
				setTimeout(function(){
					window.location = "<?php echo esc_url( $assessment_listener, 'https' ); ?>";
				}, 1000);
			</script>
		<?php
	}

}
