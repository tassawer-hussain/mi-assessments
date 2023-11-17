(function ($) {
	'use strict';

	jQuery(document).ready(function ($) {

		// update the "Add New" button URL to redirect to the custom page to add new assessments.
		if ('tti_assessments' == mi_assessments_admin_ajax_obj.post_type) {
			jQuery('#wpbody-content .wrap h1+a').attr("href", mi_assessments_admin_ajax_obj.add_new_href);
		}

		// Trigger on clicking the "Validate Data" button on add new assessment screen
		jQuery('#validate_assessment').on('click', function (e) {

			// orevent the form submission to stop the page load.
			e.preventDefault();

			// do the pre-work to hide and reset the things.
			jQuery('#status-ok,#status-error').css({ 'display': 'none' });
			jQuery('#afterResponse').css('display', 'none');
			jQuery('.tti_hidden_ele').remove();
			$('#print_report_yes, #print_report_no, #send_rep_group_lead_no, #send_rep_group_lead_yes').attr('checked', false);
			$('#add_assessment, #validate_assessment').prop('disabled', true);

			// if all required field fill, move on.
			if (validate_assessments_values()) {

				var organization = jQuery('#organization').val();
				var api_key = jQuery('#api_key').val();
				var account_login = jQuery('#account_login').val();
				var api_service_location = jQuery('#api_service_location').val();
				var survay_location = jQuery('#survay_location').val();
				var tti_link_id = jQuery('#tti_link_id').val();

				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',

					data: {
						action: 'validate_assessment_details_before_adding',
						api_key: api_key,
						api_service_location: api_service_location,
						tti_link_id: tti_link_id,
						nonce: mi_assessments_admin_ajax_obj.mi_admin_nonce,
					},

					beforeSend: function () {
						jQuery('#loader').css('display', 'inline-block');
					},

					success: function (response) {
						console.log(response);
						jQuery('#loader').css('display', 'none');
						jQuery('#status-error').css('display', 'none');

						if (response.status == 'success') {

							if (response.print_status == 'true') {
								jQuery('#print_report_settings').css('display', 'block');
							} else if (response.print_status == 'false') {
								jQuery('#print_report_settings').css('display', 'none');
							}

							if (response.print_status == 'error' || response.assessment_status_hidden == 'false') {
								jQuery('#print_report_settings').css('display', 'none');
								jQuery('#status-ok').css({ 'background': 'red', 'display': 'inline-block' });
								jQuery('#status-ok').text(response.message);
							} else {
								jQuery('#afterResponse, #add_assessment, #assessment_name_block, #assessment_locked_status').css('display', 'block');
								jQuery('#assessment_name_block #assessment_name_span').text(response.assessment_name_hidden);
								jQuery('#assessment_locked_status #assessment_locked_status_span').text(response.assessment_locked_status);
								jQuery('#status-ok').text('Success!');
								jQuery('#status-ok').css({ 'background': 'green', 'display': 'inline-block' });
							}

							if (response.print_status != 'error' || response.assessment_name_hidden != 'true') {

								var assessment_name_hidden = response.assessment_name_hidden;
								var assessment_status_hidden = response.assessment_status_hidden;
								var assessment_locked_status = response.assessment_locked_status;

								jQuery('<input>').attr({ type: 'hidden', id: 'assessment_locked_status_hidden', class: 'tti_hidden_ele', name: 'assessment_locked_status', value: assessment_locked_status }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'assessment_name_hidden', class: 'tti_hidden_ele', name: 'assessment_name_hidden', value: assessment_name_hidden }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'assessment_status_hidden', class: 'tti_hidden_ele', name: 'assessment_status_hidden', value: assessment_status_hidden }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'organization_hidden', class: 'tti_hidden_ele', name: 'organization_hidden', value: organization }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'api_key_hidden', class: 'tti_hidden_ele', name: 'api_key_hidden', value: api_key }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'account_login_hidden', class: 'tti_hidden_ele', name: 'account_login_hidden', value: account_login }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'api_service_location_hidden', class: 'tti_hidden_ele', name: 'api_service_location_hidden', value: api_service_location }).appendTo('#afterResponse');
								jQuery('<input>').attr({ type: 'hidden', id: 'survay_location_hidden', class: 'tti_hidden_ele', name: 'survay_location_hidden', value: survay_location }).appendTo('#afterResponse');
							}
						} else {
							jQuery('#status-ok').text(response.message);
							jQuery('#status-ok').css({ 'background': 'red', 'display': 'inline-block' });
						}

						$('#add_assessment, #validate_assessment').prop('disabled', false);

					},

					complete: function () { },

					error: function () {
						jQuery('#loader').css('display', 'none');
						jQuery('#status-error').css('display', 'inline-block');
						$('#add_assessment, #validate_assessment').prop('disabled', false);
						console.log('Error!');
					}
				});

			} else {
				$('#add_assessment, #validate_assessment').prop('disabled', false);
			}

		});

		/*
		 * Input validation when send call to fetch the assessment from TTI ADMIN on Add/Update new assessment
		 */
		function validate_assessments_values() {

			var valid = true;

			// reset the things.
			$(".assessment-wrap input").css('border', '');
			$("span.ttiinfo").html('');

			if (!$("#api_key").val()) {
				$("#api-info").html("(required)");
				$("#api_key").css('border', '1px solid red');
				valid = false;
			}

			if (!$("#tti_link_id").val()) {
				$("#link-info").html("(required)");
				$("#tti_link_id").css('border', '1px solid red');
				valid = false;
			}

			if (!$("#account_login").val()) {
				$("#account-info").html("(required)");
				$("#account_login").css('border', '1px solid red');
				valid = false;
			}

			if (!$("#api_service_location").val()) {
				$("#service-info").html("(required)");
				$("#api_service_location").css('border', '1px solid red');
				valid = false;
			}

			if (!$("#survay_location").val()) {
				$("#survay-info").html("(required)");
				$("#survay_location").css('border', '1px solid red');
				valid = false;
			}

			return valid;
		}

		/*
		 * When add assessment in assessment POST TYPE
		 */
		jQuery('#add_assessment').on('click', function (e) {

			// orevent the form submission to stop the page load and do pre-work.
			e.preventDefault();
			$('#add_assessment, #validate_assessment').prop('disabled', true);

			var link_id = jQuery("#tti_link_id").val();

			if (link_id && link_id != 'none') {
				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'insert_assessments',
						link_id: link_id,
						nonce: mi_assessments_admin_ajax_obj.mi_admin_nonce,
						name: jQuery("#assessment_name_hidden").val(),
						status_assessment: jQuery("#assessment_status_hidden").val(),
						status_locked: jQuery("#assessment_locked_status_hidden").val(),
						print_report: jQuery("input[name='print_report']:checked").val(),
						organization_hidden: jQuery('#organization_hidden').val(),
						api_key_hidden: jQuery('#api_key_hidden').val(),
						account_login_hidden: jQuery('#account_login_hidden').val(),
						api_service_location_hidden: jQuery('#api_service_location_hidden').val(),
						send_rep_group_lead: jQuery("input[name='send_rep_group_lead']:checked").val(),
						report_api_check: jQuery("input[name='report_api_check']:checked").val(),
						survay_location_hidden: jQuery('#survay_location_hidden').val(),
					},
					beforeSend: function () {
						jQuery('#loader_insert_assessment').css('display', 'inline-block');
					},
					success: function (response) {
						console.log(response);
						/* If post is in trash */
						if (response.status == '6') {  // 'false' === $status_locked -  This condition true return status code 6. 
							validate_opened_assessment(response);
						} else if (response.status == '3') { // Assessment already created and publish status is 'trash'.
							assessment_trashed_process(response);
						} else if (response.status == '2') { // Assessment already created and publish status is 'publish'
							assessment_published_popup(response);
						} else {

							if (response.status == 'return_url') {
								jQuery('.error_popup_tti').css('display', 'block');
							} else {
								jQuery('#record_inserted').html(response.message);
								if (response.status === 0) { // 0 status means post exist with different publish_status
									assessment_failed_popup(response);

								} else if (response.status === 1) {
									assessment_new_popup(response);

								}
							}
						}
						jQuery('#loader_insert_assessment').css('display', 'none');
						$('#add_assessment, #validate_assessment').prop('disabled', false);
					},
					error: function () {
						console.log('Error!');
						$('#add_assessment, #validate_assessment').prop('disabled', false);
					}
				});
			} else {
				$('#add_assessment, #validate_assessment').prop('disabled', false);
				validate_wrong_assessment();
			}
		});

		/**
		* Validate wrong assessment
		*/
		function validate_opened_assessment() {
			Swal.fire({
				title: '',
				text: "Opened Assessment Only Work With TTI Assessment Application Screening addon",
				type: 'error',
				showCancelButton: false,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'OK'
			}).then(function (result) {

			});
		}

		/**
		* Validate wrong assessment
		*/
		function validate_wrong_assessment() {
			// Swal.fire({
			//   title: 'No Assessment selected',
			//   text: "",
			//   type: 'info',
			//   showCancelButton: false,
			//   confirmButtonColor: '#3085d6',
			//   cancelButtonColor: '#d33',
			//   confirmButtonText: 'OK'
			// }).then(function (result) {

			// });
		}

		/**
		* Assessment already exists published one
		*/
		function assessment_published_popup(response) {
			Swal.fire({
				title: response.popup_message,
				text: "",
				type: 'info',
				showCancelButton: false,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'OK'
			}).then(function (result) {
				if (result.value) {
					window.location.href = mi_assessments_admin_ajax_obj.siteurl + "/wp-admin/edit.php?post_type=tti_assessments";
				}
			});
		}

		/**
		* Assesment published
		*/
		function assessment_new_popup(response) {
			console.log(response);
			Swal.fire({
				title: response.message,
				text: "",
				type: 'success',
				showCancelButton: false,
				confirmButtonColor: '#3085d6',
				confirmButtonText: 'OK'
			}).then(function (result) {
				if (result.value) {
					window.location.href = mi_assessments_admin_ajax_obj.siteurl + "/wp-admin/edit.php?post_type=tti_assessments";
				}
			});
		}

		/**
	   * Assesment published
	   */
		function assessment_failed_popup(response) {
			Swal.fire({
				title: 'Error in adding assessment',
				text: "",
				type: 'error',
				showCancelButton: false,
				confirmButtonColor: '#3085d6',
				confirmButtonText: 'OK'
			}).then(function (result) {
				if (result.value) {
				}
			});
		}

		/**
		* Already assesment module trashed one
		*/
		function assessment_trashed_process(response) {
			var post_id = response.message;
			Swal.fire({
				title: 'NOTE',
				text: response.popup_message,
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'No',
				confirmButtonText: 'Yes',
				reverseButtons: true,
			}).then(function (result) {

				if (result.value) {
					if (post_id) {

						// Redirect to untrash page
						send_ajax_to_restore_trash(post_id);
					}
				} else if (result.dismiss === Swal.DismissReason.cancel) {

				}
			})
		}


		/**
		* Restore the trash
		*/
		function send_ajax_to_restore_trash(post_id) {

			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'restore_trashed_post',
					post_id: post_id,
					nonce: mi_assessments_admin_ajax_obj.mi_admin_nonce,
				},
				beforeSend: function () {
					jQuery('#loader_insert_assessment').css('display', 'inline-block');
				},
				success: function (response) {
					console.log(response);
					if (response.status == 1) {
						window.location.href = mi_assessments_admin_ajax_obj.siteurl + "/wp-admin/edit.php?post_type=tti_assessments";
					}
				},
				error: function () {
					console.log('Error!');
				}
			});
		}


		/*
		 * Generate Secret Key
		 */
		jQuery('#generate_secret_key').on('click', function (e) {
			e.preventDefault();
			jQuery('.secret_key_response').empty();
			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'generate_secret_key',
					nonce: mi_assessments_admin_ajax_obj.mi_admin_nonce,
				},
				beforeSend: function () {
					jQuery('#loader_insert_assessment').css('display', 'inline-block');
				},
				success: function (response) {
					jQuery('#loader_insert_assessment').css('display', 'none');
					jQuery('#secret_key').val(response);
				},
				error: function () {
					console.log('Error!');
				}
			});
		});


		/*
		 * Save Secret Key
		 */
		jQuery('#save_secret_key').on('click', function (e) {

			e.preventDefault();
			jQuery('.secret_key_response').empty();
			var secret_key = jQuery('#secret_key').val();

			if( ! secret_key ) {
				jQuery('.secret_key_response').empty().append('<span class="error">Error! Please generate secret key first.</span>');
			} else {
				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'save_secret_key',
						secret_key: secret_key,
						nonce: mi_assessments_admin_ajax_obj.mi_admin_nonce,
					},
					beforeSend: function () {
						jQuery('#loader_insert_assessment').css('display', 'inline-block');
					},
					success: function (response) {
						jQuery('#loader_insert_assessment').css('display', 'none');
						jQuery('.secret_key_response').empty();
						jQuery('.secret_key_response').append(response.message);
						// location.reload(true);
					},
					error: function () {
						console.log('Error!');
					}
				});
			}

		});

	}, jQuery);


	/*
	 * Shortcode button and POPUP 
	 */
	(function () {


		function asc_sort(myArguments) {
			return myArguments.sort(function (a, b) {
				return a - b;
			});
		}

		var win;
		tinymce.PluginManager.add('mybutton', function (editor, url) {

			editor.addButton('mybutton', {
				text: 'TTI Shortcodes',
				icon: 'indent',
				text: ' TTI Shortcodes',
				onclick: function () {
					jQuery('.tti-platform-loader-admin').show();
					win = editor.windowManager.open({
						title: 'Assessment Shortcodes',
						width: 800,
						height: 550,
						url: mi_assessments_admin_ajax_obj.mi_admin_url + 'partials/mi-shortcode-popup.php',
						buttons: [
							{
								text: 'Insert Code',
								id: 'plugin-slug-button-insert',
								class: 'btn',
								onclick: function (e) {
									var assessment_feedback_value_eq = 0;
									var currentTab = jQuery('#current_tab_assessment').val();
									var assessment_id = jQuery('#assessment_id').val();
									var type_cons_report = jQuery('#assessment_list_cons_report_type').val();

									/*
									 * Get the feedback metadata list name
									 * @type jQuery
									 */
									var assessment_feedback_value = jQuery('#assessment_feedback_value').val();

									if (assessment_feedback_value) {
										if (assessment_feedback_value.indexOf('EQTABLES2') !== -1) {
											assessment_feedback_value_eq = 1;
										}
									}


									/*
									 * Check if intro header exist
									 * @type jQuery
									 */
									var intro_header_status = '';
									intro_header_status = jQuery('#intro_header_status').val();
									if (intro_header_status == null || intro_header_status == '') {
										intro_header_status = 'no';
									}
									/*
									 * GEN CHAR default
									 * @type jQuery|String
									 */
									var gen_char_feedback = jQuery('#gen_char_feedback').val();
									if (gen_char_feedback == null || gen_char_feedback == '') {
										gen_char_feedback = 'feedback';
									}
									/*
									 * The Success Insights��� Wheel
									 * @type jQuery|String
									 */
									var both_graph_param;
									var both_graph = jQuery('#both_graph').val();
									if (both_graph == 'both') {
										both_graph_param = 'both="yes"';
									} else {
										both_graph_param = 'both="no"';
									}

									/*
									* The Success Insights Wheel
									* @type jQuery|String
									*/

									var both_graph = jQuery('#both_siw').val();
									var both_graph_param_siw = '';

									if (both_graph == 'both') {
										both_graph_param_siw = 'both="yes"';
									} else {
										both_graph_param_siw = 'both="no"';
									}


									/*
									 * Getting all hidden inputs from specific div
									 * @type Array
									 */
									var gen_char_parArr = [];
									jQuery('#feedback_params input').each(function (key, value) {
										gen_char_parArr.push(jQuery(this).val());
									});
									var gen_char_parArr = asc_sort(gen_char_parArr);
									var gen_char_par_values = gen_char_parArr.join(',');



									/*
									* Section List "SABARS"
									* @type Array
									*/
									var feedback_params_graphicArr = [];
									jQuery('#feedback_params_graphic input').each(function (key, value) {
										var idx = jQuery.inArray(jQuery(this).val(), feedback_params_graphicArr);
										var val = jQuery(this).val();
										if (jQuery(this).val() == 'on') {
											val = 1;
										}
										if (idx == -1) {
											feedback_params_graphicArr.push(val);
										} else {

										}

									});
									var feedback_params_graphicArr = feedback_params_graphicArr.join(',');


									/*
									 * Style Insights��� Graphs
									 * @type string
									 */
									var graphic_width_input = jQuery('#graphic_width_input_updated').val();
									if (graphic_width_input == null || graphic_width_input == '') {
										if (assessment_feedback_value_eq == 1) {
											graphic_width_input = '500';
										} else {
											graphic_width_input = '250';
										}
									}

									/*
									 * Natural/Adapted checks
									 */
									var natural_graph_status;
									var adapted_graph_status;
									var adapted_graph = jQuery('#adapted_graph').val();
									var natural_graph = jQuery('#natural_graph').val();
									if (adapted_graph == null || adapted_graph == '') {
										adapted_graph_status = 'no';
									} else {
										if (adapted_graph == 'adapted') {
											adapted_graph_status = 'yes';
										}
									}
									if (natural_graph == null || natural_graph == '') {
										natural_graph_status = 'no';
									} else {
										if (natural_graph == 'natural') {
											natural_graph_status = 'yes';
										}
									}

									/*NORMS12*/
									var bh_checkbox_par1 = jQuery('#bh_checkbox_par1').val();
									var bh_checkbox_par2 = jQuery('#bh_checkbox_par2').val();
									console.log(bh_checkbox_par1);
									console.log(bh_checkbox_par2);
									if (bh_checkbox_par1 == null || bh_checkbox_par1 == '') {
										bh_checkbox_par1 = 'no';
									} else {
										bh_checkbox_par1 = 'yes';
									}
									if (bh_checkbox_par2 == null || bh_checkbox_par2 == '') {
										bh_checkbox_par2 = 'no';
									} else {
										bh_checkbox_par2 = 'yes';
									}


									/*
									 * Embedding shortcodes based on current selected action
									 */
									if (currentTab == null) {
										editor.insertContent('[take_assessment assess_id="' + assessment_id + '" button_text="Take the Assessment Now"]');
										editor.windowManager.close();
									} else if (currentTab == 'Assessment') {
										editor.insertContent('[take_assessment assess_id="' + assessment_id + '" button_text="Take the Assessment Now"]');
										editor.windowManager.close();
									} else if (currentTab == 'Text') {
										editor.insertContent('[assessment_text_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" intro="' + intro_header_status + '" datalisting="' + gen_char_par_values + '" feedback="' + gen_char_feedback + '"]');
										editor.windowManager.close();
									} else if (currentTab == 'Graphic') {
										if (assessment_feedback_value == 'SABARS' || assessment_feedback_value == 'PIAVBARS12HIGH' || assessment_feedback_value == 'PIAVBARS12MED' || assessment_feedback_value == 'PIAVBARS12LOW' || assessment_feedback_value == 'PGRAPH12') {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" intro="' + intro_header_status + '" count="' + feedback_params_graphicArr + '"]');
										} else if (assessment_feedback_value == 'NORMS12') {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" para1="' + bh_checkbox_par1 + '" para2="' + bh_checkbox_par2 + '" count="' + feedback_params_graphicArr + '"]');
										} else if (assessment_feedback_value == 'EQRESULTS2' || assessment_feedback_value == 'EQSCOREINFO2') {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '"  intro="' + intro_header_status + '" count="' + feedback_params_graphicArr + '" ]');
										} else if (assessment_feedback_value == 'EQWHEEL') {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '"  intro="' + intro_header_status + '" count="' + feedback_params_graphicArr + '" width="' + graphic_width_input + '"]');
										} else if (assessment_feedback_value == 'WHEEL' || assessment_feedback_value == 'SAGRAPH') {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" is_graph_adapted="' + adapted_graph_status + '" is_graph_natural="' + natural_graph_status + '" ' + both_graph_param_siw + ' width="' + graphic_width_input + '"]');
										} else if (assessment_feedback_value_eq == 1) {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" width="' + graphic_width_input + '" count="' + feedback_params_graphicArr + '"]');
										} else {
											editor.insertContent('[assessment_graphic_feedback assess_id="' + assessment_id + '" type="' + assessment_feedback_value + '" is_graph_adapted="' + adapted_graph_status + '" is_graph_natural="' + natural_graph_status + '" width="' + graphic_width_input + '"]');
										}
										editor.windowManager.close();
									} else if (currentTab == 'PDF') {
										editor.insertContent('[assessment_pdf_download assess_id="' + assessment_id + '"]');
										editor.windowManager.close();
									} else if (currentTab == 'ttisi_LIST') {
										editor.insertContent('[list_assessment_users assess_id="' + assessment_id + '"]');
										editor.windowManager.close();
									} else if (currentTab == 'Tti_Cons_Report') {
										editor.insertContent('[assessment_print_pdf_button_download_report assess_id="' + assessment_id + '" type="' + type_cons_report + '"]');
										editor.windowManager.close();
									}
								}
							},
							/*
							 * Close the shortcode POPUP
							 */
							{
								text: 'Cancel',
								id: 'plugin-slug-button-cancel',
								onclick: 'close'
							}
						],
						onsubmit: function (e) {
							var data = win.toJSON();
							//console.log(JSON.stringify(data));
						}
					});
				}
			});
		});
	})();





})(jQuery);
