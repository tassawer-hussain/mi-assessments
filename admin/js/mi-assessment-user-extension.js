jQuery(document).ready(function ($) {

    /**
    *  Tab change click event
    */
    jQuery(document).on("click", ".tti-user-tab button", function (e) {
        var id = jQuery(this).attr('id');
        jQuery('.tabcontent').hide();
        jQuery('.tti-user-tab button').removeClass('tti-user-active-tab');
        jQuery('.' + id).show();
        jQuery('#' + id).addClass('tti-user-active-tab');
    });


    /**
    *  Valdation user assessment click event 
    */
    jQuery('#validate_assessment_user').on('click', function (e) {
        e.preventDefault();
        jQuery('#status-ok,#status-error').css({ 'display': 'none' });
        jQuery('#validate_assessment_user').prop('disabled', true);
        jQuery('#afterResponse').css('display', 'none');
        var valid;
        valid = validateAssForm();
        var organization = jQuery('#organization_user').val();
        var api_key = jQuery('#api_key_user').val();
        var account_login = jQuery('#account_login_user').val();
        var api_service_location = jQuery('#api_service_location_user').val();
        var survay_location = jQuery('#survay_location_user').val();
        var tti_link_id = jQuery('#tti_link_id_user').val();
        var tti_user_id = jQuery('#tti_user_id').val();

        var objAssess = [];
        if (valid) {
            jQuery.ajax({
                url: tti_platform_admin_user_obj.ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'validate_user_assessment',
                    api_key_user: api_key,
                    account_login_user: account_login,
                    api_service_location_user: api_service_location,
                    tti_link_id_user: tti_link_id,
                    tti_user_id: tti_user_id,
                    nonce: tti_platform_admin_user_obj.mi_user_nonce,
                },
                beforeSend: function () {
                    jQuery('#loader').css('display', 'inline-block');
                },
                success: function (response) {
                    jQuery('#validate_assessment_user').prop('disabled', false);
                    console.log(response);
                    if (response.status == 'success') {
                        if (response.print_status == 'true') {
                            jQuery('#print_report_settings').css('display', 'block');
                        } else if (response.print_status == 'false') {
                            jQuery('#print_report_settings').css('display', 'none');
                        }
                    }

                    if (response.print_status == 'error' || response.assessment_status_hidden == 'false') {
                        jQuery('#print_report_settings').css('display', 'none');
                        jQuery('#status-ok').css({ 'background': 'red', 'display': 'inline-block' });
                        jQuery('#status-ok').text(response.message);
                    } else {
                        jQuery('#afterResponse').css('display', 'block');
                        jQuery('#add_user_assessment').css('display', 'block');
                        jQuery('#assessment_name_block').css('display', 'block');
                        jQuery('#assessment_locked_status').css('display', 'block');
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

                    jQuery('#loader').css('display', 'none');
                    $('#add_user_assessment, #validate_assessment_user').prop('disabled', false);
                },
                complete: function () { },
                error: function () {
                    jQuery('#loader').css('display', 'none');
                    jQuery('#status-error').css('display', 'inline-block');
                    jQuery('#validate_assessment_user').prop('disabled', false);
                    console.log('Error!');
                }
            });
        } else {
            $('#validate_assessment_user').prop('disabled', false);
        }
    });



    /**
     * Add assessment click event
     */
    jQuery('#add_user_assessment').on('click', function (e) {
        e.preventDefault();

        $('#add_user_assessment, #validate_assessment_user').prop('disabled', true);
        var link_id = jQuery("#tti_link_id_user").val();
        var name = jQuery("#assessment_name_hidden").val();
        var tti_user_id = jQuery('#tti_user_id').val();
        var status_assessment = jQuery("#assessment_status_hidden").val();
        var status_locked = jQuery("#assessment_locked_status_hidden").val();
        var print_report = jQuery("input[name='print_report']:checked").val();
        var organization_hidden = jQuery('#organization_hidden').val();
        var api_key_hidden = jQuery('#api_key_hidden').val();
        var account_login_hidden = jQuery('#account_login_hidden').val();
        var api_service_location_hidden = jQuery('#api_service_location_hidden').val();
        var send_rep_group_lead = jQuery("input[name='send_rep_group_lead']:checked").val();
        var report_api_check = jQuery("input[name='report_api_check']:checked").val();
        var survay_location_hidden = jQuery('#survay_location_hidden').val();

        if (link_id && link_id != 'none') {
            jQuery.ajax({
                url: tti_platform_admin_user_obj.ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'insert_user_assessments',
                    name: name,
                    link_id: link_id,
                    status_assessment: status_assessment,
                    status_locked: status_locked,
                    print_report: print_report,
                    organization_hidden: organization_hidden,
                    api_key_hidden: api_key_hidden,
                    account_login_hidden: account_login_hidden,
                    api_service_location_hidden: api_service_location_hidden,
                    send_rep_group_lead: send_rep_group_lead,
                    survay_location_hidden: survay_location_hidden,
                    report_api_check: report_api_check,
                    tti_user_id: tti_user_id,
                    nonce: tti_platform_admin_user_obj.mi_user_nonce,
                },
                beforeSend: function () {
                    jQuery('#loader_insert_assessment').css('display', 'inline-block');
                },
                success: function (response) {
                    console.log(response);
                    if (response.status == 'success') {
                        assess_success_add();
                    } else if (response.status == 'exits') {
                        assess_exists();
                    } else {
                        assess_error_add();
                    }
                    jQuery('#loader_insert_assessment, #loader_insert_assessment').css('display', 'none');
                    $('#add_user_assessment, #validate_assessment_user').prop('disabled', false);
                },
                error: function () {
                    console.log('Error!');
                    jQuery('#loader_insert_assessment, #loader_insert_assessment').css('display', 'none');
                    $('#add_user_assessment, #validate_assessment_user').prop('disabled', false);
                }
            });
        } else {
            $('#add_user_assessment, #validate_assessment_user').prop('disabled', false);
            validate_wrong_assessment();
        }
    });

    /**
     * Add assessment click event
     */
    jQuery('#add_user_settings').on('click', function (e) {
        e.preventDefault();

        $('#add_user_settings').prop('disabled', true);

        var user_capa = jQuery("input[name='user_capa']:checked").val();
        var tti_user_id = jQuery('#tti_user_id').val();

        if (link_id && link_id != 'none') {
            jQuery.ajax({
                url: tti_platform_admin_user_obj.ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'insert_user_asses_settings',
                    user_capa: user_capa,
                    tti_user_id: tti_user_id,
                    nonce: tti_platform_admin_user_obj.mi_user_nonce,
                },
                beforeSend: function () {
                    jQuery('#loader_settings_assessment').css('display', 'inline-block');
                },
                success: function (response) {
                    console.log(response);
                    if (response.status == 'success') {
                        validate_success_settings();
                    } else {
                        validate_wrong_settings();
                    }
                    jQuery('#loader_settings_assessment').css('display', 'none');
                    $('#add_user_settings').prop('disabled', false);
                },
                error: function () {
                    console.log('Error!');
                    jQuery('#loader_settings_assessment').css('display', 'none');
                    $('#add_user_settings').prop('disabled', false);
                }
            });
        } else {
            $('#add_user_settings').prop('disabled', false);
            validate_wrong_settings();
        }
    });

    /**
    * Add wrong assessment settings alert
    */
    function validate_wrong_settings() {
        Swal.fire({
            title: 'Error',
            text: "Please try again after providing valid details",
            type: 'error',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'OK'
        }).then(function (result) {

        });
    }

    /**
    * Add wrong assessment settings alert
    */
    function validate_success_settings() {
        Swal.fire({
            title: 'Assessment Successfully Added',
            text: "",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }


    /**
     * Add wrong assessment alert
     */
    function assess_error_add() {
        Swal.fire({
            title: 'Error',
            text: "Please try again after providing valid details",
            type: 'error',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'OK'
        }).then(function (result) {

        });
    }

    /**
    * Add wrong assessment alert
    */
    function assess_success_add() {
        Swal.fire({
            title: 'Assessment Successfully Added',
            text: "",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then(function (result) {
            location.reload();
        });
    }

    /**
    * Add wrong assessment alert
    */
    function assess_exists() {
        Swal.fire({
            title: 'Assessment Already Exists',
            text: "",
            type: 'info',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then(function (result) {

        });
    }


    /**
    * Assessment successFully updated alert 
    */
    function assess_success_updated() {
        Swal.fire({
            title: 'Assessment Successfully Updated',
            text: "",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then(function (result) {

        });
    }


    /**
    *  Update click event 
    */
    jQuery('#update_assessment_user').on('click', function (e) {
        e.preventDefault();
        jQuery('#status-ok,#status-error').css({ 'display': 'none' });
        jQuery('#update_assessment_user').prop('disabled', true);
        jQuery('#status-success,#status-hide').hide();
        var valid;
        valid = validateAssForm();
        var organization = jQuery('#organization_user').val();
        var api_key = jQuery('#api_key_user').val();
        var account_login = jQuery('#account_login_user').val();
        var api_service_location = jQuery('#api_service_location_user').val();
        var survey_location = jQuery('#survay_location_user').val();
        var tti_link_id = jQuery('#tti_link_id_user').val();
        var tti_user_id = jQuery('#tti_user_id').val();
        var send_rep_group_lead = jQuery("input[name='send_rep_group_lead']:checked").val();
        var status_assessment = jQuery("#assessment_status_hidden").val();
        var status_locked = jQuery("#assessment_locked_status_hidden").val();
        var print_report = jQuery("input[name='print_report']:checked").val();
        var report_api_check = jQuery("input[name='report_api_check']:checked").val();
        var assessment_name = jQuery("#assessment_name").val();
        var report_view_id = jQuery("#report_view_id").val();

        var objAssess = [];
        if (valid) {
            jQuery.ajax({
                url: tti_platform_admin_user_obj.ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'update_user_assessment',
                    title: organization,
                    name: assessment_name,
                    api_key_user: api_key,
                    account_login_user: account_login,
                    api_service_location_user: api_service_location,
                    survey_location: survey_location,
                    tti_link_id_user: tti_link_id,
                    tti_user_id: tti_user_id,
                    send_rep_group_lead: send_rep_group_lead,
                    status_assessment: status_assessment,
                    status_locked: status_locked,
                    print_report: print_report,
                    report_api_check: report_api_check,
                    report_view_id: report_view_id,
                    nonce: tti_platform_admin_user_obj.mi_user_nonce,
                },
                beforeSend: function () {
                    jQuery('#loader').css('display', 'inline-block');
                },
                success: function (response) {
                    jQuery('#update_assessment_user').prop('disabled', false);
                    if (response.status == 'success') {
                        assess_success_updated();
                    } else {
                        jQuery('#status-error').show();
                    }
                    jQuery('#loader').css('display', 'none');
                },
                complete: function () { },
                error: function () {
                    jQuery('#loader').css('display', 'none');
                    jQuery('#status-error').css('display', 'inline-block');
                    jQuery('#update_assessment_user').prop('disabled', false);
                    console.log('Error!');
                }
            });
        } else {
            $('#update_assessment_user').prop('disabled', false);
        }
    });



    /*
     * Input validation when send call to fetch the assessment from TTI ADMIN
     */
    function validateAssForm() {
        var valid = true;
        $(".user-add-assess-form input[type=\"text\"]").css('border', '1px solid #7e8993');
        $(".assessment-wrap input").css('border', '');
        $("span.info").html('');
        if (!$("#api_key_user").val()) {
            $("#api-info").html("(required)");
            $("#api_key_user").css('border', '1px solid red');
            valid = false;
        }
        if (!$("#tti_link_id_user").val()) {
            $("#link-info").html("(required)");
            $("#tti_link_id_user").css('border', '1px solid red');
            valid = false;
        }
        if (!$("#account_login_user").val()) {
            $("#account-info").html("(required)");
            $("#account_login_user").css('border', '1px solid red');
            valid = false;
        }
        if (!$("#api_service_location_user").val()) {
            $("#service-info").html("(required)");
            $("#api_service_location_user").css('border', '1px solid red');
            valid = false;
        }
        if (!$("#survay_location_user").val()) {
            $("#survay-info").html("(required)");
            $("#survay_location_user").css('border', '1px solid red');
            valid = false;
        }
        return valid;
    }



});