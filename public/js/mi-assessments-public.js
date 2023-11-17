(function ($) {
	'use strict';

	// hide next steps button
	jQuery(document).ready(function ($) {
		if ($('#isSelected').length) {
			$('.ld-content-actions a.ld-button').css('display', 'none');
		}

		$('#isSelected').on('click', function () {
			$('.ld-content-actions a.ld-button').css('display', 'flex');
		});

	});

	jQuery(window).on("load", function () {
		// Display the group name field
		jQuery('.ldgr_group_name').attr('style', 'display: block !important;');

		// Display the group name field on change of group creation dropdown
		jQuery('.ldgr_dynamic_options_select').on('change', function () {
			if ("create_new" == $(this).val()) {
				jQuery('.ldgr_group_name').attr('style', 'display: block !important;');
			} else {
				jQuery('.ldgr_group_name').attr('style', 'display: none !important;');
			}
		});

		jQuery(".fl-module.fl-module-heading.fl-node-5edf2f5cd056f").detach().appendTo('form.cart');
		jQuery(".single-product div.quantity").detach().appendTo('form.cart');
		jQuery("button.single_add_to_cart_button").detach().appendTo('form.cart');

		// 		if( $('.ttisi-front-noti').length ) {
		// 		   jQuery(".fl-module.fl-module-heading.fl-node-5edf2f5cd056f").detach().insertAfter('.ttisi-front-noti');
		// 		} else if( $('.wcpa_form_outer').length ) {
		// 		   jQuery(".fl-module.fl-module-heading.fl-node-5edf2f5cd056f").detach().insertAfter('.wcpa_form_outer');
		// 		}

	});

	/** Hide/Show text on enroll user page. */
	if (jQuery('.ldgr-group-listing').length) {
		jQuery('span.manage-users').css('display', 'inline');
		jQuery('span.manage-groups').css('display', 'none');
	}
	if (jQuery('.ldgr-group-single').length) {
		jQuery('span.manage-users').css('display', 'none');
		jQuery('span.manage-groups').css('display', 'inline');
		jQuery('h2.fl-heading .fl-heading-text').text('Manage Groups');
	}

	/** Update the pronoun label in assessment */
	waitForElm('#gender_0-lbl').then((elm) => {
		// console.log('Pronoun is ready');
		var text = jQuery('#gender_0-lbl').html();
		text = text.replace("Pronoun Choice", "Gender");
		jQuery('#gender_0-lbl').html(text);

		// Change She.
		var she_label = jQuery('#gender_0 label:last-child').html();
		she_label = she_label.replace("She", "Female");
		jQuery('#gender_0 label:last-child').html(she_label);

		// Change He.
		var he_label = jQuery('#gender_0 label:first-child').html();
		he_label = he_label.replace("He", "Male");
		jQuery('#gender_0 label:first-child').html(he_label);

		// Change He.
		jQuery('.card.field-set .card-body:first-child p').html('Your information is used for personalization purposes only.');
	});

	function waitForElm(selector) {
		return new Promise(resolve => {
			if (document.querySelector(selector)) {
				return resolve(document.querySelector(selector));
			}

			const observer = new MutationObserver(mutations => {
				if (document.querySelector(selector)) {
					resolve(document.querySelector(selector));
					observer.disconnect();
				}
			});

			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		});
	}


	/* Hide Enroll me button js */
	//$('input[name="wdm_enroll_me"]').attr('checked', true); 
	//$('.wdm-enroll-me-div').css('opacity', '0'); 
	/* Hide Enroll me button js ends */

	jQuery('body').prepend(jQuery('.tti-platform-user-level-loading'));

	/*
	 * Take assessment button action
	 */
	jQuery('#assessment_button').on('click', function (e) {
		e.preventDefault();
		$(this).text('Starting Assesment...');
		$(".user_error").remove();
		var assessment_id = jQuery(this).attr('assessment-id');
		var assessment_permalink = jQuery(this).attr('assessment-permalink');
		var assessment_locked = jQuery(this).attr('assessment-locked');
		var retake_status = jQuery(this).attr('data-retake');
		jQuery("#assessment_button").attr("disabled", true);
		jQuery.ajax({
			url: mi_assessment_public_ajax_obj.ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'take_assessment',
				assessment_id: assessment_id,
				assessment_permalink: assessment_permalink,
				assessment_locked: assessment_locked,
				retake_status: retake_status,
				nonce: mi_assessment_public_ajax_obj.mi_public_nonce
			},
			beforeSend: function () {
				jQuery('#take_loader_front').show();
			},
			success: function (response) {
				var status = response.status;
				console.log(response);
				jQuery('#take_loader_front').show();
				if (status === '0') {
					/* status 0 */
					var link_id = response.link_id;
					var survey_location = response.survey_location;
					var onsite_survey = response.onsite_survey;
					console.log(survey_location + '/' + link_id);
					setTimeout(function () {
						window.location = onsite_survey + '/?link_id=' + link_id;
					}, 2000);
				} else if (status === '1') {
					/* status 1 */

				} else if (status === '2') {
					var survey_location = response.survey_location;
					var onsite_survey = response.onsite_survey;
					var link_id = response.link_id;
					var password = response.password;
					var email = response.email;
					var user_id = response.user_id;
					setTimeout(function () {
						window.location = onsite_survey + '/?link_id=' + link_id + '&password=' + password + '&user_id=' + user_id;
					}, 2000);
				} else if (status === '3') {
					var survey_location = response.survey_location;
					var onsite_survey = response.onsite_survey;
					var link_id = response.link_id;
					var password = response.password;
					var email = response.email;
					var user_id = response.user_id;
					setTimeout(function () {
						window.location = onsite_survey + '/?link_id=' + link_id + '&password=' + password + '&user_id=' + user_id;
					}, 2000);
				} else if (status === '4') {
				} else if (status === '5') {
					jQuery('.assessment_button p.user_error').empty();
					jQuery('.assessment_button').append('<p class="user_error">User first name & last name should not be empty to take this assessment.</p>')
				} else if (status === '6') {
					jQuery('.assessment_button p.user_error').empty();
					$("#assessment_button").removeAttr("disabled");
					$("#assessment_button").text('Take the Assessment Now');
					jQuery('#take_loader_front').hide();
					jQuery('.assessment_button').append('<p class="user_error">Our assessment servers are currently undergoing maintenance. We are sorry for the inconvenience but please try again later.</p>')
				} else if (status === '7') {
					/* User level take assessment response */
					var width = 100;
					$(".loadbar").animate({
						width: width + "%"
					}, 1000);
					jQuery('.tti-platform-user-level-loading ').css('display', 'block');
					var PercentageID = $("#precent"),
						start = 0,
						end = 100;

					animateValue(PercentageID, start, end, ' 700');
					setTimeout(function () {
						location.reload();
					}, 1000);
				}
				$(this).text('Take Assessment');
			},
			error: function () {
				$(this).text('Take Assessment');
				console.log('Ajax Error!');
			}
		});
	});

	/**
	* When feedback is in "Select ALll That Apply" mode with check boxes
	*/
	jQuery('.isSelected').on('click', function (e) {
		e.preventDefault();

		var total_selected_th = 0;

		var link_id = jQuery(this).attr('link_id');
		var typee = jQuery(this).data('type');
		jQuery("." + typee + "-subbtn").text('SUBMITTING...');
		console.log(typee);
		var getSelectArr = [];
		jQuery('.getSelectArr input').each(function (key, value) {
			getSelectArr[key] = jQuery(this).val();
		});

		let elem;
		var elemObj = {};
		var selectFeedbackData = $('.selectFeedbackData').attr('id');
		selectFeedbackData = typee;

		var count = 1;
		var matchCount = 0;
		elemObj = { "type": selectFeedbackData, "statements": [] };
		var all = $('#' + typee + ' ul').map(function () {
			if (selectFeedbackData == typee) {

				var id = $(this).attr('id');
				var countArr = $(this).attr('count');
				console.log('countArr: ' + countArr);
				console.log('matchCount: ' + matchCount);
				if (countArr) {
					console.log('countArr == matchCount');
					elem = { "ident": id, "stmts": [] };
					$.each($("#" + typee + " ul:nth-child(" + count + ") input"), function (key, value) {
						var isSelected;
						if ($(this).prop("checked") == true) {
							isSelected = '1';
							total_selected_th++;
						} else if ($(this).prop("checked") == false) {
							isSelected = '0';
						}
						var text = jQuery(this).attr('text');
						var item = {};
						item["text"] = text;
						item["value"] = isSelected;
						elem.stmts.push(item);
					});
				} else {
					console.log('countArr != matchCount');
					elem = { "ident": '', "stmts": [] };
					var item = {};
					item["text"] = '';
					item["value"] = '';
					elem.stmts.push(item);
				}
				matchCount++;
				count++;
				elemObj['statements'].push(elem);
			}
		});
		console.log(total_selected_th);
		// console.log(elemObj);

		if ('GENCHAR' == typee && (total_selected_th == 0 || total_selected_th > 9)) {
			var result = {};
			result["message"] = 'Please choose atleat 1 or max 9 statements. Thanks'
			assessment_feedback_submitted_err(result);
			jQuery("." + typee + "-subbtn").text('SUBMIT');
		} else if (('DOS' == typee || 'DONTS' == typee || 'IDEALENV' == typee || 'MOT' == typee) && (total_selected_th == 0 || total_selected_th > 4)) {
			var result = {};
			result["message"] = 'Please choose atleat 1 or max 4 statements. Thanks'
			assessment_feedback_submitted_err(result);
			jQuery("." + typee + "-subbtn").text('SUBMIT');
		} else {
			/*
			* Insert checked data in the Assessment table
			*/
			jQuery.ajax({
				url: mi_assessment_public_ajax_obj.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'insertIsSelectedData',
					isSelected: elemObj,
					link_id: link_id,
					nonce: mi_assessment_public_ajax_obj.mi_public_nonce
				},
				beforeSend: function () { },
				success: function (response) {
					jQuery("." + typee + "-subbtn").text('SUBMIT');
					var message = response.message;
					jQuery('#responseIsSelected').empty();
					if (response.status == '1') {
						assessment_feedback_submitted_success(response);
					} else {
						assessment_feedback_submitted_err(response);
					}

					//jQuery('#responseIsSelected').append(message);

				},
				error: function () {
					jQuery("." + typee + "-subbtn").text('SUBMIT');
					console.log('Error!');
				}
			});
		}

	});

	/**
	* Assesment published
	*/
	function assessment_feedback_submitted_success(response) {
		Swal.fire({
			title: response.message,
			text: "",
			type: 'success',
			showCancelButton: false,
			confirmButtonColor: '#3085d6',
			confirmButtonText: 'OK'
		}).then(function (result) {
			if (result.value) {
			}
		});
	}

	/**
	* Assesment published
	*/
	function assessment_feedback_submitted_err(response) {
		Swal.fire({
			title: response.message,
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
	/*
	 * Listener Loader and Redirect within 7 sec
	 */
	var width = 100,
		time = '700';
	// Loadbar Animation
	$(".loadbar").animate({
		width: width + "%"
	}, 1500);
	// Percentage Increment Animation
	var PercentageID = $("#precent"),
		start = 0,
		end = 100,
		durataion = time;
	animateValue(PercentageID, start, end, durataion);
	function animateValue(id, start, end, duration) {
		var range = end - start,
			current = start,
			increment = end > start ? 1 : -1,
			stepTime = Math.abs(Math.floor(duration / range)),
			obj = $(id);
		var timer = setInterval(function () {
			current += increment;
			$(obj).text(current + "%");
			//obj.innerHTML = current;
			if (current == end) {
				clearInterval(timer);
			}
		}, stepTime);
	}



})(jQuery);