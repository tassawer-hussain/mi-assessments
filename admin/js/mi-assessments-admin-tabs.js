/*
 * Shortcode Tabs on the Left Side
 */
function openTab(evt, assessmentName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tti-platform-tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(assessmentName).style.display = "block";
    evt.currentTarget.className += " active";

    parent.jQuery('#current_tab_assessment').remove();
    jQuery('<input>').attr({
        type: 'hidden',
        id: 'current_tab_assessment',
        name: 'current_tab_assessment',
        value: assessmentName
    }).appendTo(parent.jQuery('body'));
}

/*
 * Get all assessments and render in the select on popup load.
 * This function populate the select assessment dropdown on "Assessment" Tab.
 */
function get_assessments_list() {
    jQuery.ajax({
        url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'list_assessments',
            nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
        },
        beforeSend: function () {
            jQuery('.tti-platform-loader-admin').show();
        },
        success: function (response) {
            // parent.jQuery('#feedback_params').remove(); - TASSAWER COMMENTED
            if (response != 'none') {
                jQuery.each(response, function (key, value) {
                    jQuery('#assessment_list select').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                });
            } else {
                jQuery('#assessment_list select option:nth-child(1)').text("No Assessments Available");
            }
            jQuery('.tti-platform-loader-admin').hide();
        },
        error: function () {
            console.log('Error! From get_assessments_list() function');
        }
    });
}

/*
 * Get all assessments for text feedback and render in the select
 */
function get_assessments_list_for_feedback() {
    var dataArr = [];
    jQuery.ajax({
        url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'list_assessments_for_feedback',
            nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
        },
        beforeSend: function () {
            jQuery('.tti-platform-loader-admin').show();
        },
        success: function (response) {
            if (response != 'none') {
                jQuery.each(response, function (key, value) {
                    jQuery('#assessment_list_text #assessment_list_for_text').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                    jQuery('#assessment_list_graphic #assessment_list_for_graphic').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                    jQuery('#assessment_list_for_cons_report_block #assessment_list_for_cons_report').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                });
            } else {
                jQuery('#assessment_list_graphic_feedback option:nth-child(1)').text("No Feedback Available");
                jQuery('#assessment_list_text_feedback option:nth-child(1)').text("No Feedback Available");
                jQuery('#assessment_list_for_text option:nth-child(1)').text("No Assessments Available");
                jQuery('#assessment_list_for_graphic option:nth-child(1)').text("No Assessments Available");
                jQuery('#assessment_list_for_cons_report option:nth-child(1)').text("No Assessments Available");
            }
            jQuery('.tti-platform-loader-admin').hide();
        },
        error: function () {
            console.log('Error!');
        }
    });
    return dataArr;
}

/*
 * Get all assessments for pdf report and render in the select
 */
function get_assessments_list_for_pdf() {
    var dataArr = [];
    jQuery.ajax({
        url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'list_assessments_for_pdf',
            nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
        },
        beforeSend: function () {
            jQuery('.tti-platform-loader-admin').show();
        },
        success: function (response) {
            if (response != 'none') {
                jQuery.each(response, function (key, value) {
                    jQuery('#assessment_list_pdf #assessment_list_for_pdf').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                });
            } else {
                jQuery('#assessment_list_for_pdf option:nth-child(1)').text("No Assessments Available");
            }
            jQuery('.tti-platform-loader-admin').hide();
        },
        error: function () {
            console.log('Error!');
        }
    });
    return dataArr;
}


/*
 * Get all assessments with locked status true
 */
function get_opened_assessments_list() {
    var dataArr = [];
    jQuery.ajax({
        url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'list_opened_assessments_list',
            nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
        },
        beforeSend: function () {
            jQuery('.tti-platform-loader-admin').show();
        },
        success: function (response) {
            if (response != 'none') {
                jQuery.each(response, function (key, value) {
                    jQuery('#assessment_list_block #assessment_lists_resp').append(jQuery("<option></option>").attr("value", value.id).text(value.title));
                });
            } else {
                jQuery('#assessment_list_for_pdf option:nth-child(1)').text("No Assessments Available");
            }
            jQuery('.tti-platform-loader-admin').hide();
        },
        error: function () {
            console.log('Error!');
        }
    });
    return dataArr;
}

/*
 * Load when document ready
 */
jQuery(document).ready(function () {
    parent.jQuery('#intro_header_status').remove();
    parent.jQuery('#bh_checkbox_par1').remove();
    parent.jQuery('#bh_checkbox_par2').remove();

    /*
     * Run function get_assessments_list()
     */
    get_assessments_list();
    /*
     * Run function get_assessments_list_for_feedback()
     */
    get_assessments_list_for_feedback();
    /*
     * Run function get_assessments_list_for_pdf()
     */
    get_assessments_list_for_pdf();
    /*
    * Run function get_opened_assessments_list()
    */
    get_opened_assessments_list();

    /*
     * Create input with hidden input types and assign assessment ID
     */
    jQuery('#assessment_list select, #assessment_list_for_cons_report').on('change', function () {
        parent.jQuery('#assessment_id').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_id',
            name: 'assessment_id',
            value: this.value
        }).appendTo(parent.jQuery('body'));
    });

    /*
     * Create input with hidden type and assign assessment ID for text feedback
     */
    jQuery('#assessment_list_pdf #assessment_list_for_pdf').on('change', function () {
        var assess_id = this.value;
        parent.jQuery('#assessment_id').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_id',
            name: 'assessment_id',
            value: assess_id
        }).appendTo(parent.jQuery('body'));
        jQuery('.tti-platform-loader-admin').hide();
    });

    /*
     * Create input with hidden type and assign assessment ID for text feedback
     */
    jQuery('#assessment_list_for_cons_report_type').on('change', function () {
        var assessment_list_cons_report_type_val = this.value;
        parent.jQuery('#assessment_list_cons_report_type').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_list_cons_report_type',
            name: 'assessment_list_cons_report_type',
            value: assessment_list_cons_report_type_val
        }).appendTo(parent.jQuery('body'));
        jQuery('.tti-platform-loader-admin').hide();
    });



    /*
     * Create input with hidden type and assign assessment ID for text feedback
     */
    jQuery('#assessment_list_block #assessment_lists_resp').on('change', function () {
        var assess_id = this.value;
        parent.jQuery('#assessment_id').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_id',
            name: 'assessment_id',
            value: assess_id
        }).appendTo(parent.jQuery('body'));
        jQuery('.tti-platform-loader-admin').hide();
    });

    /*
     * Create input with hidden type and assign assessment ID for text feedback
     */
    jQuery('#assessment_list_text #assessment_list_for_text').on('change', function () {
        var assess_id = this.value;
        var dataArr = [];
        jQuery.ajax({
            url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_assessments_metadeta',
                type: 'text',
                assessment_text_feedback_id: assess_id,
                nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
            },
            beforeSend: function () {
                jQuery('.tti-platform-loader-admin').show();
            },
            success: function (response) {
                jQuery('#assessment_checklist .rowTitles').empty();
                jQuery('#assessment_checklist .checklist_feedback').empty();
                parent.jQuery('#feedback_params').remove();
                jQuery('#assessment_list_text #assessment_list_text_feedback option').remove();
                jQuery.each(response, function (key, value) {
                    console.log(value);
                    if (key == '0') {
                        jQuery('#assessment_list_text #assessment_list_text_feedback').append(jQuery('<option></option>'));
                    }

                    jQuery('#assessment_list_text #assessment_list_text_feedback').append(jQuery('<option value=' + value.ident + ' assess_id=' + assess_id + '>' + value.title + '</option>'));

                });
                /*
                 * Hidden input field for assessment ID for later use
                 */
                parent.jQuery('#assessment_id').remove();
                jQuery('<input>').attr({
                    type: 'hidden',
                    id: 'assessment_id',
                    name: 'assessment_id',
                    value: assess_id
                }).appendTo(parent.jQuery('body'));
                jQuery('.tti-platform-loader-admin').hide();
            },
            error: function () {
                console.log('Error!');
            }
        });
        return dataArr;
    });


    /********************************* Common Functions ******************************/
    /**
    * All checkbox in shortcode functionality
    *
    */
    function all_bh_click_func() {
        jQuery('#assessment_checklist_for_graphic #all_bh').on('click', function () {
            if (jQuery(this).is(':checked')) {
                parent.jQuery('#feedback_params_graphic').empty();
                jQuery("input.count_scorebars").attr("disabled", true);
                jQuery('<input>').attr({
                    type: 'hidden',
                    id: 'all_bh_val',
                    name: 'all_bh_val',
                    value: 'all'
                }).appendTo(parent.jQuery('#feedback_params_graphic'));
            } else {
                jQuery("input.count_scorebars").removeAttr("disabled");
                jQuery("input.count_scorebars").prop("disabled", false).attr("checked", false);
                parent.jQuery('#feedback_params_graphic').empty();
            }
        });
    }

    /**
    * Option checkboxs in shortcode functionality
    *
    */
    function checkbox_options_click_func() {
        setTimeout(function () {
            jQuery('#assessment_checklist_for_graphic #count_for_bh input[type=checkbox]').on('click', function () {
                if (jQuery(this).is(':checked')) {
                    parent.jQuery('bh_checkbox' + jQuery(this).val()).remove();
                    jQuery('<input>').attr({
                        type: 'hidden',
                        id: 'bh_checkbox' + jQuery(this).val(),
                        name: 'bh_checkbox' + jQuery(this).val(),
                        value: jQuery(this).val()
                    }).appendTo(parent.jQuery('#feedback_params_graphic'));
                } else {
                    parent.jQuery('#bh_checkbox' + jQuery(this).val()).remove();
                }
            });
        }, 100);
    }
    /**********************************************************************************/


    /******* Hardcoded conditions because of incomplete date in report_metadata *******/

    /**
     * Situational Driving Forces Cluster checkboxes
     *
     */
    function situational_driving_forces_cluster_checkboxes(value, html, innerTitle) {
        jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="all_bh" name="all_bh" value="all"> <label for="all_bh">All ' + innerTitle + '</label></div></div>');
        var count = value.count;
        html += '<div id="count_for_bh">';
        html += '<div class="col">\n\
                                <input type="checkbox" value="5" id="bh_checklist5" name="bh_checklist5" class="count_scorebars"> <label for="bh_checklist5">5</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="6" id="bh_checklist6" name="bh_checklist6" class="count_scorebars"> <label for="bh_checklist6">6</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="7" id="bh_checklist7" name="bh_checklist7" class="count_scorebars"> <label for="bh_checklist7">7</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="8" id="bh_checklist8" name="bh_checklist8" class="count_scorebars"> <label for="bh_checklist8">8</label>\n\
                            </div>';
        html += '</div>';
        return html;
    }

    /**
    * Indifferent Driving Forces Cluster checkboxes
    *
    */
    function indifferent_driving_forces_cluster_checkboxes(value, html, innerTitle) {
        jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="all_bh" name="all_bh" value="all"> <label for="all_bh">All ' + innerTitle + '</label></div></div>');
        var count = value.count;
        html += '<div id="count_for_bh">';
        html += '<div class="col">\n\
                                <input type="checkbox" value="9" id="bh_checklist9" name="bh_checklist9" class="count_scorebars"> <label for="bh_checklist9">9</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="10" id="bh_checklist10" name="bh_checklist10" class="count_scorebars"> <label for="bh_checklist10">10</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="11" id="bh_checklist11" name="bh_checklist11" class="count_scorebars"> <label for="bh_checklist11">11</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="12" id="bh_checklist12" name="bh_checklist12" class="count_scorebars"> <label for="bh_checklist12">12</label>\n\
                            </div>';
        html += '</div>';
        return html;
    }

    /**
    * Driving Forces Graph checkboxes
    *
    */
    function driving_forces_graph(value, html) {
        jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="all_bh" name="all_bh" value="all"> <label for="all_bh">All</label></div></div>');
        var count = value.count;
        html += '<div id="count_for_bh">';
        html += '<div class="col">\n\
                                <input type="checkbox" value="Knowledge" id="bh_checklistKnowledge" name="bh_checklistKnowledge" class="count_scorebars"> <label for="bh_checklistKnowledge">Knowledge</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="Utility" id="bh_checklistUtility" name="bh_checklistUtility" class="count_scorebars"> <label for="bh_checklistUtility">Utility</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="Surroundings" id="bh_checklistSurroundings" name="bh_checklistSurroundings" class="count_scorebars"> <label for="bh_checklistSurroundings">Surroundings</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                <input type="checkbox" value="Others" id="bh_checklistOthers" name="bh_checklistOthers" class="count_scorebars"> <label for="bh_checklistOthers">Others</label>\n\
                            </div>';
        html += '<div class="col">\n\
                                    <input type="checkbox" value="Power" id="bh_checklistPower" name="bh_checklistPower" class="count_scorebars"> <label for="bh_checklistPower">Power</label>\n\
                                </div>';
        html += '<div class="col">\n\
                                    <input type="checkbox" value="Methodologies" id="bh_checklistMethodologies" name="bh_checklistMethodologies" class="count_scorebars"> <label for="bh_checklistMethodologies">Methodologies</label>\n\
                                </div>';
        html += '</div>';
        return html;
    }

    /**********************************************************************************/

    /*
     * Create options when specific text feedback selected
     */
    jQuery('#assessment_list_text #assessment_list_text_feedback').on('change', function () {
        parent.jQuery('#feedback_params').remove();
        var assessment_feedback_value = this.value;
        var assess_id = jQuery('#assessment_list_text #assessment_list_text_feedback option:selected').attr('assess_id');
        /*
         * Creating a hidden field with selected feedback
         */
        parent.jQuery('#assessment_feedback_value').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_feedback_value',
            name: 'assessment_feedback_value',
            value: assessment_feedback_value
        }).appendTo(parent.jQuery('body'));
        /*
         * Ajax call for getting the metadata values
         * @type Array
         */
        var dataArr = [];
        jQuery.ajax({
            url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'get_assessments_metadeta_checklist',
                type: 'text',
                assessment_feedback_value: assessment_feedback_value,
                assess_id: assess_id,
                nonce: mi_assessments_admin_tabs_ajax_obj.mi_tabs_nonce,
            },
            beforeSend: function () {
                jQuery('.tti-platform-loader-admin').show();
            },
            success: function (response) {
                console.log(response);
                jQuery('#assessment_checklist').show();
                jQuery('#assessment_checklist #feedback_title').text('');
                jQuery('#assessment_checklist .rowTitles').empty();
                jQuery('#assessment_checklist .checklist_feedback').empty();
                var hasLooped = false;
                console.log(response['0'].content);
                var countRow = 1;
                /* content loop */
                jQuery.each(response['0'].content, function (key, value) {
                    var getIntro = response['0'].intro;
                    var can_validate = value.can_validate;

                    if (countRow == 1) {
                        jQuery('#assessment_checklist .rowTitles').append('<div class="row-1"><div class="col-1" id="feedback_title"></div><div class="col-1">Feedback</div><div class="col-1">Select All That Apply</div><div class="col-1">Display Selected</div></div>');
                        jQuery('#assessment_checklist #feedback_title').text(response['0'].title);
                    }

                    /*
                     * Checking if API has intro true/false
                     * @type boolean 
                     */
                    if (!hasLooped) {
                        if (getIntro == true) {
                            jQuery('#assessment_checklist .checklist_feedback').append('<div class="row"><div class="col"><input type="checkbox" id="intro_header" name="intro_header"> <label for="intro_header">Intro Header</label></div></div>');
                        }
                        hasLooped = true;
                    }
                    /*
                     * if title null
                     * @type string
                     */
                    var innerTitleSlug = value.ident;
                    var innerTitle = value.title;
                    if (innerTitle == null) {
                        innerTitle = response['0'].title;
                    }


                    /*
                     * Create HTML for title
                     * @type html
                     */
                    var html = '';
                    html += '<div class="row">';
                    html += '<div class="col">\n\
                                    <input type="checkbox" id="'+ innerTitleSlug + '" name="' + innerTitleSlug + '"> <label for="' + innerTitleSlug + '">' + innerTitle + '</label>\n\
                                </div>';
                    html += '<div class="col">\n\
                                    <input type="radio" id="" name="'+ innerTitleSlug + '-' + key + '" mood="feedback" value="' + key + '" section="' + innerTitleSlug + '" disabled>\n\
                                </div>';
                    if (can_validate == true || value.ident == 'bullets') {
                        html += '<div class="col">\n\
                                        <input type="radio" id="" name="'+ innerTitleSlug + '-' + key + '" mood="select" value="' + key + '" section="' + innerTitleSlug + '" disabled>\n\
                                    </div>';
                        html += '<div class="col">\n\
                                        <input type="radio" id="" name="'+ innerTitleSlug + '-' + key + '" mood="display" value="' + key + '" section="' + innerTitleSlug + '" disabled>\n\
                                    </div>';
                    }
                    html += '</div>';
                    jQuery('#assessment_checklist .checklist_feedback').append(html);
                    countRow++;
                });
                /*
                 * Enable/Disable radio button on click
                 */
                jQuery('#assessment_checklist input[type=checkbox]').on('click', function () {
                    if (jQuery(this).is(':checked')) {
                        jQuery(this).parent().parent().find('input[type=radio]').attr('disabled', false);
                    } else {
                        jQuery(this).parent().parent().find('input[type=radio]').attr('disabled', true).prop("checked", false);
                    }
                });
                /*
                 * Hidden field for intro header if checked
                 */
                jQuery('#assessment_checklist #intro_header').on('click', function () {
                    if (jQuery(this).is(':checked')) {
                        parent.jQuery('#intro_header_status').remove();
                        jQuery('<input>').attr({
                            type: 'hidden',
                            id: 'intro_header_status',
                            name: 'intro_header_status',
                            value: 'yes'
                        }).appendTo(parent.jQuery('body'));
                    } else {
                        parent.jQuery('#intro_header_status').remove();
                    }
                });
                /*
                 * Create hidden fields when radio button click
                 */
                jQuery('#assessment_checklist input[type=radio]').on('click', function () {
                    var gen_char_par = jQuery(this).val();
                    var ident = jQuery(this).attr('section');
                    var gen_char_feedback = jQuery(this).attr('mood');
                    parent.jQuery('#' + ident).remove();
                    parent.jQuery('#gen_char_feedback').remove();
                    if (jQuery(this).is(':checked')) {
                        jQuery('<input>').attr({
                            type: 'hidden',
                            id: ident,
                            name: ident,
                            value: gen_char_par,
                            feedback: gen_char_feedback,
                            from: 'radio'
                        }).appendTo(parent.jQuery('#feedback_params'));
                        jQuery('<input>').attr({
                            type: 'hidden',
                            id: 'gen_char_feedback',
                            name: 'gen_char_feedback',
                            value: gen_char_feedback
                        }).appendTo(parent.jQuery('body'));
                    } else { }
                });
                /*
                 * Appending all the hidden fields in this specific div in Text Feedback just
                 */
                jQuery('<div id="feedback_params"></div>').appendTo(parent.jQuery('body'));
                jQuery('.tti-platform-loader-admin').hide();
            },
            error: function () {
                console.log('Error!');
            }
        });
        return dataArr;
    });
    /*
     * Create input with hidden type and assign assessment ID for text feedback
     */
    jQuery('#assessment_list_graphic #assessment_list_for_graphic').on('change', function () {
        var assess_id = this.value;
        var dataArr = [];
        jQuery.ajax({
            url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_assessments_metadeta',
                type: 'graphic',
                assessment_text_feedback_id: assess_id
            },
            beforeSend: function () {
                jQuery('.tti-platform-loader-admin').show();
            },
            success: function (response) {
                jQuery('#assessment_checklist_for_graphic .rowTitles').empty();
                jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').empty();
                jQuery('#assessment_list_graphic #assessment_list_graphic_feedback option').remove();
                jQuery.each(response, function (key, value) {
                    if (key == '0') {
                        jQuery('#assessment_list_graphic #assessment_list_graphic_feedback').append(jQuery('<option></option>'));
                    }
                    jQuery('#assessment_list_graphic #assessment_list_graphic_feedback').append(jQuery('<option value=' + value.ident + ' assess_id=' + assess_id + '>' + value.title + '</option>'));

                });
                parent.jQuery('#assessment_id').remove();
                jQuery('<input>').attr({
                    type: 'hidden',
                    id: 'assessment_id',
                    name: 'assessment_id',
                    value: assess_id
                }).appendTo(parent.jQuery('body'));
                jQuery('.tti-platform-loader-admin').hide();
            },
            error: function () {
                console.log('Error!');
            }
        });
        return dataArr;
    });
    /*
     * When select the list from graphic dropdown
     */
    jQuery('#assessment_list_graphic #assessment_list_graphic_feedback').on('change', function () {
        parent.jQuery('#feedback_params').remove();
        var assessment_feedback_value = this.value;
        var assess_id = jQuery('#assessment_list_graphic #assessment_list_graphic_feedback option:selected').attr('assess_id');
        parent.jQuery('#assessment_feedback_value').remove();
        jQuery('<input>').attr({
            type: 'hidden',
            id: 'assessment_feedback_value',
            name: 'assessment_feedback_value',
            value: assessment_feedback_value
        }).appendTo(parent.jQuery('body'));
        /*
         * Ajax that fetch record from assessment table for Graphic Feedback
         */
        var dataArr = [];
        jQuery.ajax({
            url: mi_assessments_admin_tabs_ajax_obj.tabs_ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'get_assessments_metadeta_checklist',
                type: 'graphic',
                assessment_feedback_value: assessment_feedback_value,
                assess_id: assess_id
            },
            beforeSend: function () {
                jQuery('.tti-platform-loader-admin').show();
            },
            success: function (response) {
                console.log(response);
                jQuery('#assessment_checklist_for_graphic .rowTitles').empty();
                jQuery('#assessment_checklist_for_graphic').show();
                jQuery('#assessment_checklist_for_graphic #feedback_title_graphic').text('');
                jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').empty();
                parent.jQuery('#feedback_params_graphic').remove();
                hasLooped = false;
                var counterVal = 0;

                /* Loop the content */
                jQuery.each(response['0'].content, function (key, value) {
                    var getIntro = response['0'].intro;
                    var can_validate = value.can_validate;

                    /* Allow intro for following sections */
                    if (
                        assessment_feedback_value == 'EQRESULTS2' ||
                        assessment_feedback_value == 'EQSCOREINFO2' ||
                        assessment_feedback_value == 'EQWHEEL'
                    ) {
                        var getIntro = true;
                    }

                    if (!hasLooped) {
                        if (getIntro == true) {
                            jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="intro_header" name="intro_header"> <label for="intro_header">Intro Header</label></div></div>');
                        }
                        hasLooped = true;
                    }
                    var html = '';

                    var innerTitleSlug = value.ident;

                    /* style variable */
                    var styleFromAPI = value.style;

                    var innerTitle = value.title;

                    if (innerTitle == null) {
                        innerTitle = response['0'].title;
                    }

                    if (counterVal == 0) {
                        jQuery('#assessment_checklist_for_graphic .rowTitles').append('<div class="row-1"><div class="col-1" id="feedback_title_graphic"></div></div>');
                        jQuery('#assessment_checklist_for_graphic #feedback_title_graphic').text(response['0'].title);
                    }

                    /*
                     * Intro Header input field
                     */
                    jQuery('#assessment_checklist_for_graphic #intro_header').on('click', function () {
                        if (jQuery(this).is(':checked')) {
                            parent.jQuery('#intro_header_status').remove();
                            jQuery('<input>').attr({
                                type: 'hidden',
                                id: 'intro_header_status',
                                name: 'intro_header_status',
                                value: 'yes'
                            }).appendTo(parent.jQuery('body'));
                        } else {
                            parent.jQuery('#intro_header_status').remove();
                        }
                    });
                    parent.jQuery('#feedback_params_graphic').empty();


                    if (innerTitle == 'Situational Driving Forces Cluster') {

                        var count = value.count;
                        html += situational_driving_forces_cluster_checkboxes(value, html, innerTitle);

                        /* All checkbox click function */
                        all_bh_click_func();

                        /* Checkboxes options click function */
                        checkbox_options_click_func();

                    } else if (innerTitle == 'Indifferent Driving Forces Cluster') {
                        //jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="all_bh" name="all_bh" value="all"> <label for="all_bh">All '+innerTitle+'</label></div></div>');

                        var count = value.count;
                        html += indifferent_driving_forces_cluster_checkboxes(value, html, innerTitle)

                        /* All button click function */
                        all_bh_click_func();

                        /* Checkboxes options click function */
                        checkbox_options_click_func();


                    } else if (innerTitle == 'Driving Forces Graph') {
                        var count = value.count;
                        html += driving_forces_graph(value, html);

                        /* All button click function */
                        all_bh_click_func();

                        /* Checkboxes options click function */
                        checkbox_options_click_func();

                    }

                    else if (styleFromAPI == 'scorebars') {

                        if (
                            innerTitle == 'Motivation' ||
                            innerTitle == 'Self-Regulation' ||
                            innerTitle == 'Self-Awareness' ||
                            innerTitle == 'Social Awareness' ||
                            innerTitle == 'Social Regulation') {
                            html += '<div class="row">';
                            html += '<div class="col" style="width: 100% !important; margin: 10px 0 20px;">\n\
                                                <label>Image Width: </label><input type="number" id="graphic_width" name="graphic_width" style="width: 200px;" value=""> px\n\
                                                <input type="hidden" id="graphic_width_input" name="graphic_width_input" value="">\n\
                                            </div>';
                            html += '</div>';
                        } else {
                            jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append('<div class="row"><div class="col"><input type="checkbox" id="all_bh" name="all_bh" value="all"> <label for="all_bh">All ' + innerTitle + '</label></div></div>');
                        }


                        var count = value.count;
                        html += '<div id="count_for_bh">';
                        for (var i = 1; i <= count; i++) {
                            /* Setting Titles */
                            if (innerTitle == 'Emotional Quotient Assessment Results') {
                                /* Setting custom titles for Emotional Quotient Assessment Results */
                                if (i == 1) {
                                    title = 'Self-Awareness';
                                } else if (i == 2) {
                                    title = 'Self-Regulation';
                                } else if (i == 3) {
                                    title = 'Motivation';
                                } else if (i == 4) {
                                    title = 'Social Awarenesss';
                                } else if (i == 5) {
                                    title = 'Social Regulation';
                                }
                            } else if (innerTitle == 'Emotional Quotient Scoring Information') {
                                /* Setting custom titles for Emotional Quotient Scoring Information */
                                if (i == 1) {
                                    title = 'Total Emotional Quotient';
                                } else if (i == 2) {
                                    title = 'Self';
                                } else if (i == 3) {
                                    title = 'Others';
                                }
                            } else if (
                                innerTitle == 'Motivation' ||
                                innerTitle == 'Self-Regulation' ||
                                innerTitle == 'Self-Awareness' ||
                                innerTitle == 'Social Awareness' ||
                                innerTitle == 'Social Regulation') {
                                title = innerTitle;
                            } else {
                                title = i;
                            }




                            html += '<div class="col">\n\
                                <input type="checkbox" value="'+ i + '" id="bh_checklist' + i + '" name="bh_checklist' + i + '" class="count_scorebars"> <label for="bh_checklist' + i + '">' + title + '</label>\n\
                            </div>';
                        }
                        html += '</div>';

                        /* All button click function */
                        all_bh_click_func();

                        setTimeout(function () {
                            jQuery('#assessment_checklist_for_graphic #count_for_bh input[type=checkbox]').on('click', function () {
                                if (jQuery(this).is(':checked')) {
                                    parent.jQuery('#bh_checkbox' + jQuery(this).val()).remove();
                                    jQuery('<input>').attr({
                                        type: 'hidden',
                                        id: 'bh_checkbox' + jQuery(this).val(),
                                        name: 'bh_checkbox' + jQuery(this).val(),
                                        value: jQuery(this).val()
                                    }).appendTo(parent.jQuery('#feedback_params_graphic'));
                                } else {
                                    parent.jQuery('#bh_checkbox' + jQuery(this).val()).remove();
                                }
                            });

                        }, 100);

                        jQuery('#assessment_checklist_for_graphic #par1').on('click', function () {
                            if (jQuery(this).is(':checked')) {
                                parent.jQuery('#par1').remove();
                                jQuery('<input>').attr({
                                    type: 'hidden',
                                    id: 'bh_checkbox_par1',
                                    name: 'bh_checkbox_par1',
                                    value: 'par1'
                                }).appendTo(parent.jQuery('body'));
                            } else {
                                parent.jQuery('#bh_checkbox_par1').remove();
                            }
                        });
                        jQuery('#assessment_checklist_for_graphic #par2').on('click', function () {
                            if (jQuery(this).is(':checked')) {
                                parent.jQuery('#par2').remove();
                                jQuery('<input>').attr({
                                    type: 'hidden',
                                    id: 'bh_checkbox_par2',
                                    name: 'bh_checkbox_par2',
                                    value: 'par2'
                                }).appendTo(parent.jQuery('body'));
                            } else {
                                parent.jQuery('#bh_checkbox_par2').remove();
                            }
                        });

                    } else {
                        if (counterVal == '0') {

                            if (assessment_feedback_value != 'NORMS12') {
                                html += '<div class="row">';
                                html += '<div class="col" style="width: 100% !important; margin: 10px 0 20px;">\n\
                                                <label>Image Width: </label><input type="number" id="graphic_width" name="graphic_width" style="width: 200px;" value=""> px\n\
                                                <input type="hidden" id="graphic_width_input" name="graphic_width_input" value="">\n\
                                            </div>';
                                html += '</div>';
                            }
                            if (innerTitle == 'The Success Insights��� Wheel' || innerTitle == 'Style Insights��� Graphs') {
                                html += '<div class="row">';
                                html += '<div class="col">\n\
                                                <input type="checkbox" id="both_siw" name="both"> <label for="both" style="text-transform: capitalize;">Both</label>\n\
                                            </div>';
                                html += '</div>';
                            }

                        }
                        html += '<div class="row">';
                        html += '<div class="col">\n\
                                        <input type="checkbox" id="'+ innerTitleSlug + '" name="' + innerTitleSlug + '"> <label for="' + innerTitleSlug + '" style="text-transform: capitalize;">' + innerTitleSlug + '</label>\n\
                                    </div>';
                        html += '</div>';

                        setTimeout(function () {
                            jQuery('#assessment_checklist_for_graphic #wheel').on('click', function () {
                                if (jQuery(this).is(':checked')) {
                                    parent.jQuery('bh_checkbox' + jQuery(this).val()).remove();
                                    jQuery('<input>').attr({
                                        type: 'hidden',
                                        id: 'bh_checkbox' + jQuery(this).val(),
                                        name: 'bh_checkbox' + jQuery(this).val(),
                                        value: jQuery(this).val()
                                    }).appendTo(parent.jQuery('#feedback_params_graphic'));
                                } else {
                                    parent.jQuery('#bh_checkbox' + jQuery(this).val()).remove();
                                }
                            });
                        }, 100);


                    }
                    jQuery('#assessment_checklist_for_graphic .checklist_feedback_graphic').append(html);
                    counterVal++;
                });

                setTimeout(function () {
                    parent.jQuery('#both').remove();
                    parent.jQuery('#adapted_graph').remove();
                    parent.jQuery('#natural_graph').remove();
                    jQuery('#both').on('click', function () {
                        if (jQuery(this).is(':checked')) {
                            jQuery('<input>').attr({
                                type: 'hidden',
                                id: 'both_graph',
                                name: 'both_graph',
                                value: 'both'
                            }).appendTo(parent.jQuery('#feedback_params_graphic'));
                        } else {
                            parent.jQuery('#both_graph').remove();
                        }
                    });
                    jQuery('#adapted').on('click', function () {
                        if (jQuery(this).is(':checked')) {
                            jQuery('<input>').attr({
                                type: 'hidden',
                                id: 'adapted_graph',
                                name: 'adapted_graph',
                                value: 'adapted'
                            }).appendTo(parent.jQuery('#feedback_params_graphic'));
                        } else {
                            parent.jQuery('#adapted_graph').remove();
                        }
                    });
                    jQuery('#natural').on('click', function () {
                        if (jQuery(this).is(':checked')) {
                            jQuery('<input>').attr({
                                type: 'hidden',
                                id: 'natural_graph',
                                name: 'natural_graph',
                                value: 'natural'
                            }).appendTo(parent.jQuery('#feedback_params_graphic'));
                        } else {
                            parent.jQuery('#natural_graph').remove();
                        }
                    });
                }, 100);
                /*
                 * Appending all graphic related hidden fields in this div
                 */
                jQuery('<div id="feedback_params_graphic"></div>').appendTo(parent.jQuery('body'));

                /* The Success Insights Wheel */
                jQuery('#both_siw').on('click', function () {
                    if (this.checked == true) {

                        jQuery('#adapted').prop('checked', true);
                        jQuery('#natural').prop('checked', true);
                    } else {

                        jQuery('#adapted').prop('checked', false);
                        jQuery('#natural').prop('checked', false);
                    }
                    if (jQuery(this).is(':checked')) {
                        jQuery('<input>').attr({
                            type: 'hidden',
                            id: 'both_siw',
                            name: 'both_siw',
                            value: 'both'
                        }).appendTo(parent.jQuery('#feedback_params_graphic'));
                    } else {
                        parent.jQuery('#both_siw').remove();
                    }
                });



                jQuery('#both_siw').on('click', function () {
                    if (jQuery(this).is(':checked')) {
                        jQuery('<input>').attr({
                            type: 'hidden',
                            id: 'both_graph',
                            name: 'both_graph',
                            value: 'both'
                        }).appendTo(parent.jQuery('#feedback_params_graphic'));
                    } else {
                        parent.jQuery('#both_graph').remove();
                    }
                });

                jQuery('#adapted').on('click', function () {
                    jQuery('#assessment_checklist_for_graphic #both_siw').prop('checked', false);
                });

                /*
                 * Update hidden value based on input value while typing
                 */
                jQuery('#graphic_width').keyup(function () {
                    jQuery('#graphic_width_input').val(jQuery(this).val());
                    parent.jQuery('#graphic_width_input_updated').remove();
                    jQuery('<input>').attr({
                        type: 'hidden',
                        id: 'graphic_width_input_updated',
                        name: 'graphic_width_input_updated',
                        value: jQuery('#graphic_width_input').val()
                    }).appendTo(parent.jQuery('body'));
                });
                jQuery('.tti-platform-loader-admin').hide();
            },
            error: function () {
                console.log('Error!');
            }
        });
        return dataArr;
    });
});