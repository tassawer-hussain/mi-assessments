jQuery(document).ready(function ($) {
    var wdm_datatable = jQuery('#wdm_group').DataTable({
        "language": {
            "decimal": "",
            "emptyTable": wdm_data.no_user_is_enrolled,
            "info": wdm_data.info,
            "infoFiltered": wdm_data.info_filtered,
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": wdm_data.length_menu_msg,
            "loadingRecords": wdm_data.loading,
            "processing": wdm_data.processing,
            "search": wdm_data.search,
            "zeroRecords": wdm_data.no_matching_record_found,
            "paginate": wdm_data.paginate,
            "searchPlaceholder": wdm_data.search_placeholder
        },
        "lengthMenu": wdm_data.length_menu,
    });
    jQuery('#wdm_group_wrapper').prepend(jQuery('#bulk_remove'));

    var user_ids = [];
    var group_ids = [];

    var group_limit = wdm_data.group_limit - 1;


    jQuery(document).on('click', 'thead input[name="select_all"]', function (e) {
        if (this.checked) {
            jQuery('#wdm_group tbody input[type="checkbox"]:not(:checked)').trigger('click');
        } else {
            jQuery('#wdm_group tbody input[type="checkbox"]:checked').trigger('click');
        }
    });



    // Support to buddy boss theme, it uses iCheck plugin to decorate the radio and checkboxes
    if (jQuery.fn.iCheck) {

        function selectAllUsers() {
            jQuery('#wdm_group tbody input[type="checkbox"]:not(:checked)').each(function (ind, obj) {
                jQuery(obj).closest('span').addClass('checked');
                jQuery(obj).prop('checked', true);
            });
        }

        function deselectAllUsers() {
            jQuery('#wdm_group tbody input[type="checkbox"]:checked').each(function (ind, obj) {
                jQuery(obj).closest('span').removeClass('checked');
                jQuery(obj).prop('checked', false);
            });
        }

        jQuery('thead input[name="select_all"]').on('ifUnchecked', function (e) {
            deselectAllUsers();
        });

        jQuery('thead input[name="select_all"]').on('ifChecked', function (e) {
            selectAllUsers();
        });

        jQuery('thead input[name="select_all"]').on('ifChanged', function (e) {
            var checked = jQuery('thead input[name="select_all"]').is(':checked');
            if (checked) {
                selectAllUsers();
            } else {
                deselectAllUsers();
            };
        });
    }



    jQuery(document).on('change', 'thead input[name="select_all"]', function (e) {
        if (this.checked) {
            jQuery('#wdm_group tbody input[type="checkbox"]:not(:checked)').prop('checked', true);
        } else {
            jQuery('#wdm_group tbody input[type="checkbox"]:checked').prop('checked', false);
        }
    });

    jQuery('body').on('click', '#bulk_remove', function (e) {
        e.preventDefault();

        if (jQuery('#wdm_group tbody input[type="checkbox"]:checked').length == 0) {
            alert(wdm_data.no_user_selected);
            return false;
        }

        var removal_message = wdm_data.are_you_sure_plural;
        if (!confirm(removal_message)) {
            return false;
        }

        jQuery('#wdm_group tbody input[type="checkbox"]:checked').each(function () {
            if (jQuery(this).closest('td').siblings('td.ldgr-actions').find('a').hasClass('request_sent')) {
                return;
            }

            // jQuery(this).closest('td.select_action').siblings('td.ldgr-actions').append('<img id="wdm_ajax_loader" src="' + wdm_data.ajax_loader + '">');
            jQuery(this).closest('td.select_action').siblings('td.ldgr-actions .dashicons-update').removeClass('hide');

            user_ids.push(jQuery(this).data('user_id'));
            group_ids.push(jQuery(this).data('group_id'));
        });


        if (user_ids.length === 0) {
            return false;
        }

        if (group_ids.length === 0) {
            return false;
        }

        jQuery.ajax({
            type: "post",
            dataType: 'json',
            url: wdm_data.ajaxurl,
            data: {
                action: 'bulk_unenrollment',
                user_ids: user_ids,
                group_ids: group_ids
            },
            timeout: 30000,
            success: function (response) {
                jQuery.each(response, function (id, value) {
                    jQuery.each(value, function (status, message) {
                        switch (status) {
                            case 'success':
                                snackbar(message);
                                if (wdm_data.admin_approve == 'on') {
                                    jQuery('#wdm_search_submit').submit();
                                }
                                else {
                                    jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "' + id + '"].wdm_remove').addClass('request_sent');
                                    jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "' + id + '"].wdm_remove').text(wdm_data.request_sent);
                                    // jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "'+id+'"].wdm_remove').siblings('#wdm_ajax_loader').remove();
                                    jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "' + id + '"].wdm_remove').siblings('.dashicons-update').addClass('hide');
                                    jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "' + id + '"].wdm_remove').removeClass('wdm_remove');
                                    jQuery('#wdm_group tr td.select_action input[data-user_id = "' + id + '"]').trigger('click');
                                }
                                break;
                            case 'error':
                                snackbar(message);
                                // jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "'+id+'"].wdm_remove').siblings('#wdm_ajax_loader').remove();
                                jQuery('#wdm_group tr td.ldgr-actions a[data-user_id = "' + id + '"].wdm_remove').siblings('.dashicons-update').addClass('hide');
                                break;
                        }
                    });
                });
                wdm_datatable.draw(false);
                jQuery('#wdm_group thead input[name="select_all"]').attr('checked', false);
            }
        });

    });

    jQuery('body').on('click', '.wdm_remove', function (e) {
        e.preventDefault();
        var temp = jQuery(this);

        sendRemovalRequest(temp);
    });

    function sendRemovalRequest(temp) {
        if (temp.hasClass("request_sent")) {
            return false;
        }
        var student_name = temp.parent().siblings('td[data-title="Name"]').text().trim();
        if (!student_name.length) {
            temp.parent().siblings('td').each(function (ind, obj) {
                if ('Name' === jQuery(obj).data('title')) {
                    student_name = jQuery(obj).text().trim();
                }
            })
        }
        var removal_message = wdm_data.are_you_sure;
        if (0 === student_name.length) {
            removal_message = removal_message.replace('{user}', '');
            removal_message = removal_message.replace('the following user', wdm_data.student_singular);
        }

        var removal_message = removal_message.replace('{user}', student_name);
        if (confirm(removal_message)) {
            // jQuery(temp).parent().append('<img id="wdm_ajax_loader" src="' + wdm_data.ajax_loader + '">');
            var user_id = jQuery(temp).data('user_id');
            var group_id = jQuery(temp).data('group_id');
            var nonce = jQuery(temp).data('nonce');

            jQuery.ajax({
                type: "post",
                dataType: 'json',
                url: wdm_data.ajaxurl,
                data: {
                    action: 'wdm_group_unenrollment',
                    user_id: user_id,
                    group_id: group_id,
                    nonce: nonce
                },
                timeout: 30000,
                beforeSend: function () {
                    temp.siblings('.dashicons-update').removeClass('hide');
                },
                complete: function () {
                    temp.siblings('.dashicons-update').addClass('hide');
                },
                success: function (response) {
                    jQuery.each(response, function (status, message) {
                        switch (status) {
                            case 'success':
                                snackbar(message);
                                if (wdm_data.admin_approve == 'on') {
                                    jQuery('#wdm_search_submit').trigger('submit');
                                }
                                else {
                                    temp.removeClass('wdm_remove');
                                    temp.addClass('request_sent');
                                    temp.text(wdm_data.request_sent);
                                }

                                break;
                            case 'error':
                                snackbar(message);
                                temp.siblings('.dashicons-update').addClass('hide');
                                break;
                        }
                    });
                    wdm_datatable.draw(false);

                    // CUSTOMISATION Made by Tassawer.
                    // Reload the page on success.
                    location.reload();
                },
                error: function () {
                    alert(wdm_data.error_msg);
                }
            });
        }
    }


    /*
     * function to check upload file extension
     */
    (function ($) {
        $.fn.checkFileType = function (options) {
            var defaults = {
                allowedExtensions: [],
                success: function () { },
                error: function () { }
            };
            options = $.extend(defaults, options);

            return this.each(function () {

                $(this).on('change', function () {
                    var value = $(this).val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                    if ($.inArray(extension, options.allowedExtensions) == -1) {
                        options.error();
                        $(this).focus();
                    } else {
                        options.success();

                    }

                });

            });
        };

    })(jQuery);
    jQuery(function () {
        jQuery('#uploadcsv').checkFileType({
            allowedExtensions: ['csv'],
            success: function () {
                //alert('Success');
                // jQuery('#import-upload-form').submit();
                //jQuery(this).parent().append('<img id="wdm_ajax_loader" src="' + wdm_data.ajax_loader + '">');
            },
            error: function () {
                alert(wdm_data.only_csv_file_allowed);
                jQuery('#uploadcsv').val("");
                return false;
            }
        });
    });

    // Fetch file info
    jQuery('#uploadcsv').on('change', function () {
        var file = document.getElementById('uploadcsv');
        var fileName = file.files[0].name;
        var fileSize = file.files[0].size + ' Bytes';
        jQuery('#ldgr-upload-file-name span').html(fileName);
        jQuery('#ldgr-upload-file-size span').html(fileSize);
        jQuery('#ldgr-upload-file-info').slideDown();
        jQuery('.ldgr-uploader .ldgr-info').hide();
    });
    // function to check upload file extension end's here
    //
    jQuery('body').on('click', '.request_sent', function (e) {
        e.preventDefault();
    });
    jQuery('.wdm_add_users').click(function () {
        if (group_limit > 0 || wdm_data.is_unlimited) {
            jQuery('#wdm_members_name').clone().appendTo('#add_details');
            //jQuery('#wdm_members_email').clone().appendTo('#add_details');
            jQuery('#add_details').find('tr:last-child').removeAttr('id');
            var email_count = jQuery("[name='wdm_members_email[]']").length;
            var i = 1;
            jQuery("[name='wdm_members_email[]']").each(function () {
                if (email_count == i) {
                    jQuery(this).attr('value', '');
                    jQuery(this).parent().find('.error').remove();
                    jQuery(this).parent().parent().find('td:last-child').append(' | ' + wdm_data.remove_html);
                }
                i++;
            });
            var name_count = jQuery("[name='wdm_members_fname[]']").length;
            var i = 1;

            jQuery("[name='wdm_members_fname[]']").each(function () {
                if (name_count == i) {
                    jQuery(this).attr('value', '');
                }
                i++;
            });
            var i = 1;

            jQuery("[name='wdm_members_lname[]']").each(function () {
                if (name_count == i) {
                    jQuery(this).attr('value', '');
                }
                i++;
            });
            group_limit--;
            //console.log(group_limit);
        } else {
            alert(wdm_data.user_limit);
        }
    });
    jQuery('body').on('click', '.wdm_remove_add_user', function (e) {
        e.preventDefault();
        jQuery(this).parent().parent().remove();

        group_limit++;
    });


    jQuery(document).on('click', '.wdm-add-user-btn', function (event) {
        event.preventDefault();
        if (group_limit > 0 || wdm_data.is_unlimited) {
            jQuery('#wdm_members_name').clone().appendTo('#add_details');
            //jQuery('#wdm_members_email').clone().appendTo('#add_details');
            jQuery('#add_details').find('tr:last-child').removeAttr('id');
            var email_count = jQuery("[name='wdm_members_email[]']").length;
            var i = 1;
            jQuery("[name='wdm_members_email[]']").each(function () {
                if (email_count == i) {
                    jQuery(this).attr('value', '');
                    jQuery(this).parent().find('.error').remove();
                    jQuery(this).parent().parent().find('td:last-child').find('.wdm-add-user-btn').after(wdm_data.remove_html);
                }
                i++;
            });
            var name_count = jQuery("[name='wdm_members_fname[]']").length;
            var i = 1;

            jQuery("[name='wdm_members_fname[]']").each(function () {
                if (name_count == i) {
                    jQuery(this).attr('value', '');
                }
                i++;
            });
            var i = 1;

            jQuery("[name='wdm_members_lname[]']").each(function () {
                if (name_count == i) {
                    jQuery(this).attr('value', '');
                }
                i++;
            });
            group_limit--;
            //console.log(group_limit);
        } else {
            alert(wdm_data.user_limit);
        }
    });


    //    jQuery("[name='wdm_members_email[]']").live('input focus',function(){
    //        if(jQuery(this).val() == ""){
    //         jQuery('#wdm_submit').attr('disabled',true);
    //        }
    //
    //    })
    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    jQuery('[name="wdm_group_id"]').change(function () {
        jQuery("[name='wdm_members_email[]']").each(function () {
            jQuery(this).attr('value', '');
        });


        jQuery("[name='wdm_members_fname[]']").each(function () {
            jQuery(this).attr('value', '');
        });

        jQuery('#wdm_search_submit').submit();
    });

    jQuery('body').on('click', '.wdm_clear_data', function (e) {
        e.preventDefault();
        jQuery(this).parent().parent().find('[name="wdm_members_fname[]"]').attr('value', '');
        jQuery(this).parent().parent().find('[name="wdm_members_email[]"]').attr('value', '');
        jQuery(this).parent().parent().find('[name="wdm_members_lname[]"]').attr('value', '');
    });


    //Custom tabs on Group registration page start
    jQuery('body').on('click', 'ul.tabs li', function (e) {
        e.preventDefault();
        var tab_id = jQuery(this).attr('data-tab');
        jQuery('ul.tabs li').removeClass('current');
        jQuery('.tab-content').removeClass('current');
        jQuery(this).addClass('current');
        jQuery("#" + tab_id).addClass('current');
    });

    //Custom tabs on Group registration page End

    //Custom tabs for Add New User start
    jQuery('body').on('click', '.wdm-adduser-tabs li', function (e) {
        e.preventDefault();
        var wdm_tab_id = jQuery(this).attr('data-tab');
        jQuery('.wdm-adduser-tabs li').removeClass('current');
        jQuery('.wdm-tab-content').removeClass('current');
        jQuery(this).addClass('current');
        jQuery("#" + wdm_tab_id).addClass('current');
    });

    //Custom tabs for Add New User end


    //For making smooth transition border below tabs start
    var activeWidth = jQuery(".tabs li.current").width();
    jQuery("#wdm-border-bottom").width(activeWidth);
    var posLeft;
    jQuery(".tabs li").click(function () {
        posLeft = jQuery(this).position().left;
        jQuery("#wdm-border-bottom").css('left', posLeft);
        var activenewWidth = jQuery(this).width();
        jQuery("#wdm-border-bottom").width(activenewWidth);
    })

    //For making smooth transition border below tabs End

    //  Ajax CSV Upload
    function batchProcessUploads(step, data) {
        data.set('step', step);
        jQuery.ajax({
            type: 'post',
            url: wdm_data.ajaxurl,
            dataType: 'json',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                if ('done' == response.step) {

                    if (response.error) {
                        jQuery('.wdm-notification-messages').addClass('wdm-error-message').append(response.error);
                    }
                    if (response.update) {
                        jQuery('.wdm-notification-messages').addClass('wdm-update-message').append(wdm_data.users_uploaded_msg);
                    }
                    if (response.users) {
                        add_enrolled_users(response.users);
                        if (!wdm_data.is_unlimited) {
                            update_enrolled_count(response.users.length);
                        }
                    }

                    // jQuery('.wdm-progress-container').remove();
                    jQuery('div.ldgr-upload-csv .blocked').addClass('hide');
                    jQuery('.upload-csv-cancel').trigger('click');
                    jQuery('html, body').animate({
                        scrollTop: jQuery('.wdm-notification-messages').offset().top - 100
                    }, 1000);
                } else {
                    if (response.error) {
                        jQuery('.wdm-notification-messages').addClass('wdm-error-message').append(response.error);

                        // jQuery('.blocked').remove();
                        jQuery('div.ldgr-upload-csv .blocked').addClass('hide');
                        jQuery('.upload-csv-cancel').trigger('click');
                        jQuery('html, body').animate({
                            scrollTop: jQuery('.wdm-notification-messages').offset().top - 100
                        }, 1000);
                    }
                    if (response.users) {
                        add_enrolled_users(response.users);
                        if (!wdm_data.is_unlimited) {
                            update_enrolled_count(response.users.length);
                        }
                    }
                    var percentage = parseInt(response.percentage);
                    jQuery('.wdm-progress-bar').animate({
                        width: percentage + '%',
                    }, 50);
                    batchProcessUploads(parseInt(response.step), data);
                }
            }
        }).fail(function (response) {
            if (window.console && window.console.log) {
                console.log(response);
            }
        });
    }

    function move_progress_bar($selector, start, end) {
        var id = setInterval(frame, 10);
        function frame() {
            if (start >= end) {
                clearInterval(id);
            } else {
                start++;
                $selector.width(start + '%');
            }
        }
    }

    function add_enrolled_users($user_list) {
        if (jQuery.isEmptyObject($user_list)) {
            return;
        }

        var enrolled_users_table = jQuery('#wdm_group').DataTable();
        var row_node = null;
        var col_1 = null;
        var col_2 = null;
        var col_3 = null;
        jQuery.each($user_list, function (ind, obj) {
            row_node = enrolled_users_table.row.add(obj).draw().node();
            col_1 = enrolled_users_table.cells(row_node, 0).nodes();
            col_2 = enrolled_users_table.cells(row_node, 1).nodes();
            col_3 = enrolled_users_table.cells(row_node, 2).nodes();
            col_4 = enrolled_users_table.cells(row_node, 3).nodes();
            jQuery(col_1).addClass('select_action');
            jQuery(col_2).data('title', 'Name');
            jQuery(col_3).data('title', 'Email');
            jQuery(col_4).addClass('ldgr-actions');
        });
    }

    function update_enrolled_count(count) {
        // var existing_count = parseInt(jQuery('.wdm-registration-left').html().match(/\d+/)[0]);
        var existing_count = parseInt(wdm_data.group_limit);
        var new_count = existing_count - count;
        wdm_data.group_limit = new_count;

        var message = jQuery('.ldgr-u-left').html();
        var new_message = message.replace(existing_count, new_count);
        jQuery('.ldgr-u-left').html(new_message);
    }

    // Trigger new user enrollment form submission.
    jQuery('#ldgr-add-users-submit').on('click', function () {
        if (!validateAddUserFormData()) {
            return;
        }
        var email = [];
        jQuery("[name='wdm_members_email[]']").each(function () {
            email.push(jQuery(this).val());
        });
        jQuery.ajax({
            type: "post",
            dataType: 'json',
            url: wdm_data.ajaxurl,
            data: {
                action: 'new_user_validation',
                email: email,
                group_id: wdm_data.group_id,
            },
            timeout: 30000,
            success: function (response) {
                if (typeof (response.users) != "undefined" && response.users !== null) {
                    jQuery("[name='wdm_members_email[]']").each(function () {
                        if (jQuery.inArray(jQuery(this).val(), response.users) !== -1) {
                            jQuery(this).siblings(".ldgr-field-error").html(response.msg);
                            jQuery(this).siblings(".ldgr-field-error").show();
                            jQuery(this).siblings(".ldgr-field-error").attr('display', 'block');
                        }
                    });
                } else {
                    jQuery('form#wdm_add_user_fields').trigger('submit');
                }
            }, error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    // feature detection for drag&drop upload
    var isAdvancedUpload = function () {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();


    // Open file explorer on clicking upload button.
    jQuery('.ldgr-upload-btn').on('click', function () {
        jQuery('#uploadcsv').trigger('click');
    });

    if (isAdvancedUpload) {
        jQuery('form#import-upload-form').on('dragover dragenter drop dragstart dragleave drag', function (e) {
            e.preventDefault();
            e.stopPropagation();
        })
        jQuery('form#import-upload-form').on('dragover dragenter', '.ldgr-uploader', function (e) {
            jQuery(this).addClass('ldgr-drag-hover');
        });
        jQuery('form#import-upload-form').on('dragleave drop', '.ldgr-uploader', function (e) {
            jQuery(this).removeClass('ldgr-drag-hover');
        });
        jQuery('form#import-upload-form').on('drop', '.ldgr-uploader', function (e) {
            var file = e.originalEvent.dataTransfer.files;
            jQuery('#uploadcsv').prop('files', file).trigger('change');
        });
    }

    jQuery('.ldgr-upload-csv-btn').on('click', function (e) {
        e.preventDefault();
        jQuery('.wdm-notification-messages').removeClass('wdm-error-message wdm-update-message').html('');
        var upload_check = jQuery('input[name="wdm_upload_check"]').val();
        var nonce = jQuery('#wdm_ldgr_csv_upload_enroll_field').val();
        var file_data = jQuery('#uploadcsv').prop('files')[0];
        var formData = new FormData();
        formData.append('uploadcsv', file_data);
        formData.append('action', 'wdm_upload_users_csv');
        formData.append('wdm_upload_check', upload_check);
        formData.append('wdm_ldgr_csv_upload_enroll_field', nonce);
        formData.append('wdm_group_id', wdm_data.group_id);

        jQuery('div.ldgr-upload-csv .blocked').removeClass('hide');
        move_progress_bar(jQuery('.wdm-progress-bar'), 1, 10);

        batchProcessUploads(1, formData);
    });

    var group_media_frame = null;
    jQuery('.ldgr-ch-icon').on('click', function () {
        jQuery('.edit-group-cancel').trigger('click');
        if (group_media_frame) {
            group_media_frame.open();
            return;
        }
        group_media_frame = wp.media.frames.group_media_frame = wp.media({
            title: 'Group Media Title',
            button: {
                text: 'Upload'
            },
            multiple: false
        });

        group_media_frame.on('select', function () {
            attachment = group_media_frame.state().get('selection').first().toJSON();
            // console.log(attachment);
            jQuery('.ldgr-group-image > img').attr('src', attachment.url);
            jQuery('#ldgr-edit-group-image').val(attachment.id);
            jQuery('.ldgr-edit-group').trigger('click');
        });

        group_media_frame.open();
    });

    jQuery('.ldgr-main-group-content, .ldgr-sub-group-item').on('click', function () {
        jQuery(this).find('form.wdm_search_submit').trigger('submit');
    });

    var validateAddUserFormData = function () {
        var valid = true;

        //Text-Box validations.
        jQuery('form#wdm_add_user_fields div.ldgr-add-user .ldgr-textbox').each(function (ind, obj) {
            if (jQuery(obj).val().length == 0) {
                jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.empty_msg).show();
                valid = false;
            } else {
                jQuery(obj).siblings('.ldgr-field-error').html(wdm_data).show();
            }
        });

        //Email Validation
        var email = jQuery('#wdm_add_user_fields input[name="wdm_members_email[]"]').val();
        if (/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
            jQuery('#wdm_add_user_fields input[name="wdm_members_email[]"]').siblings('.ldgr-field-error').html(wdm_data).show();
        } else {
            jQuery('#wdm_add_user_fields input[name="wdm_members_email[]"]').siblings('.ldgr-field-error').html(wdm_data.invalid_email).show();
            valid = false;
        }

        //Dynamic textbox field validation
        jQuery('form#wdm_add_user_fields div.ldgr-add-user .ldgr-dynamic-textbox').each(function (ind, obj) {
            if (obj.required) {
                if (jQuery(obj).val().length == 0) {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.empty_msg).show();
                    valid = false;
                } else {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data).show();
                }
            }
        });


        //Dynamic number field validation
        jQuery('form#wdm_add_user_fields div.ldgr-add-user .ldgr-dynamic-number').each(function (ind, obj) {
            if (obj.required) {
                if (jQuery(obj).val().length == 0) {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.invalid_number).show();
                    valid = false;
                } else if (/\D/.test(jQuery(obj).val())) {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.invalid_number).show();
                    valid = false;
                } else {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data).show();
                }
            }
        });

        //Dynamic textarea field validation
        jQuery('form#wdm_add_user_fields div.ldgr-add-user .ldgr-dynamic-textarea').each(function (ind, obj) {
            if (obj.required) {
                if (jQuery(obj).val().length == 0) {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.required_textarea).show();
                    valid = false;
                } else {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data).show();
                }
            }
        });



        //Dynamic fields checkbox Validation
        if (jQuery('.ldgr-dynamic-checkbox.required').length > 0) {
            //forloop for checkbox
            jQuery('.ldgr-dynamic-checkbox.required').each(function (ind, obj) {
                if (!obj.checked) {

                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data.required_checkbox).show();
                    valid = false;

                } else {
                    jQuery(obj).siblings('.ldgr-field-error').html(wdm_data).show();
                }
            });
        }

        return valid;
    }
});
