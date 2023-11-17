(function( $ ) {
    'use strict';

        // Trigger Create Sub-Group Action.
        jQuery('.cnsg-btn.tti-stack-btn').on('click', function( event ) {
            jQuery('li.tab-link[data-tab="tab-3"]').click();
            jQuery('span.ldgr-btn.cnsg-btn').click();

        });
        // Update the course title.
        jQuery('.ldgr-group-courses-item span').html(jQuery('.ldgr-group-courses-item span').attr('title'));

        // Hide Sub-Group tab on page load.
        jQuery(window).load(function() {
            if( jQuery('#tab-3').length ) {
                jQuery('#tab-3').removeClass('current');
            }

            if( jQuery('#tab-1').length ) {
                jQuery('#tab-1').addClass('current');
            }
        });

        // Enroll Users Page - Start
        jQuery(document).ready(function() { 
            /* Changed Group to Product */
            jQuery('.wdm-select-wrapper .wdm-select-wrapper-content h3').html('Product');
            jQuery('.wdm-select-wrapper .ldgr-group-settings-wrap').remove();

        });
        // Enroll Users Page - End
        // Tassawer Added - User Removal updated.
        jQuery(".tti-user-removal").on( 'click', function() {
            var status = $(this).data('assessment-status');
            console.log(status);
            if( "complete" == status || "in_progress" == status ) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This user has completed the assignment or is in the process of completing the assignment. Would you like to remove?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove!'
                }).then((result) => { console.log(result);
                    if (result.value) {
                        $(this).next().click();
                    };
                });
            } else {
                $(this).next().click();
            }
            
        });

      let applicantData = $('#tti_group_leader_retake').DataTable({
        'aoColumnDefs': [{
            'bSortable': false,
            'aTargets': [-1,-2], /* 1st colomn, starting from the right */
        }],
        'pageLength': 50,
        'columnDefs': [{
            'targets': 1,
            'checkboxes': {
               'selectRow': true
            }
        }],
        'select': {
            'style': 'multi'
        },
         "language": {
            "lengthMenu": mi_assessments_ldgr.menu_display,
            "zeroRecords": mi_assessments_ldgr.zeroRecords,
            "info": mi_assessments_ldgr.info,
            "infoEmpty": mi_assessments_ldgr.infoEmpty,
            "infoFiltered": mi_assessments_ldgr.infoFiltered,
            "search": mi_assessments_ldgr.Search,
            "paginate": {
                "first" :     mi_assessments_ldgr.First,
                "previous" :  mi_assessments_ldgr.Previous,
                "next" :      mi_assessments_ldgr.Next,
                "last" :      mi_assessments_ldgr.Last
            },
        },
        responsive: true
    });

    function show_sweet_alert_loading() {
        swal({
             title: 'Updating user limit. Please wait...',
        });
        swal.showLoading();
    }


    /* Retake assessment click event */

    $("#tti_group_leader_retake").on("click", ".tti_download_user_pdf", function(event) {
        var email = $(this).data('mail');
        var user_id = $(this).data('user_id');
        var link_id = $(this).data('link_id');
        var group_leader_id = $(this).data('group_leader_id');

        if(email && user_id && group_id && link_id && group_leader_id) {
            tti_get_user_assessment_ajax(email, user_id, link_id, group_leader_id);   
        }

         /* Restrict reload the page */
        return false;
    });
    
    
        var isIE = false;
        var ua = window.navigator.userAgent;
        var old_ie = ua.indexOf('MSIE ');
        var new_ie = ua.indexOf('Trident/');
        
        if ((old_ie > -1) || (new_ie > -1)) {
            isIE = true;
        }
        
        if ( isIE ) {
            $(".gl-action-drodown").css("position","relative")
        }



    /**
    * Retaking assessment ajax call function.
    */
    function tti_get_user_assessment_ajax(email, user_id, group_id, group_leader_id) {
        var dataToSend = {
            action : 'tti_get_user_assessment_pdfs',
            user_id : user_id,
            link_id: link_id,
            email : email,
            group_leader_id: group_leader_id
        };
        jQuery.ajax({
                url : mi_assessments_ldgr.ajaxurl,
                type : 'post',
                dataType: 'json',
                data : dataToSend,
                beforeSend: function() {
                   
                },
                success : function( response ) {
                    console.log(response);
                    
                },
                error:function (){
                    console.log('Ajax Error!');
                   
                }
            });
    }


    /* Retake assessment click event */
    $('#tti_group_leader_retake tbody').on('click', '.tti_retake_assessment', function () {
        var email = $(this).data('mail');
        var user_id = $(this).data('user_id');
        var group_id = $(this).data('group_id');
        var link_id = $(this).data('link_id');
        var group_leader_id = $(this).data('group_leader_id');
        var reg_left = $(this).data('reg-left');
        
        var limit_check = check_reduce_limit(reg_left);
        
        /* Start retaking assessment ajax call */
        if(email && user_id && group_id && limit_check && link_id) {
            confirming_retaking_assessment(email, user_id, group_id, link_id, group_leader_id); 
        } else if(!limit_check) {
            var message = mi_assessments_ldgr.limit_ends;
            show_limit_exceed(message, status);
        } else {
            Swal.fire({
              title: 'Invalid Data',
              text: 'Some data is missing',
              type: 'error',
              showCancelButton: false,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Close'
            }).then(function (result) {
          
            });
            console.log('Not enough data for retaking assessment.');
        }

        /* Restrict reload the page */
        return false;
    });



    /**
    * Confirm before proceding with retaking assessment request.
    */
    function confirming_retaking_assessment(email, user_id, group_id, link_id, group_leader_id) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, I am sure!'
        }).then(function (result) {
          if (result.value) {
            tti_retaking_assessment_ajax(email, user_id, group_id, link_id, group_leader_id);
          }
        });
    }


    /**
    * Retaking assessment ajax call function.
    */
    function tti_retaking_assessment_ajax(email, user_id, group_id, link_id, group_leader_id) {
        var dataToSend = {
            action : 'tti_retaking_assessment',
            user_id : user_id,
            group_id: group_id,
            email : email,
            link_id : link_id,
            group_leader_id : group_leader_id,
            nonce : mi_assessments_ldgr.mildgr_nonce,
        };
        jQuery.ajax({
                url : mi_assessments_ldgr.ajaxurl,
                type : 'post',
                dataType: 'json',
                data : dataToSend,
                beforeSend: function() {
                    add_disbaled_attr("tti_retake_assessment");
                    show_sweet_alert_loading();
                    display_block("userid-"+user_id+"-"+link_id+" #retake_assessment_loader_" + user_id);
                },
                success : function( response ) {
                    console.log(response);
                    if(response.status == 1) {
                        reduce_limit();
                        increase_current_user_limit(user_id,link_id);
                        show_limit_exceed(response.message, response.status);
                    } else {
                        show_limit_exceed(response.message, response.status);
                    }
                    remove_disbaled_attr("tti_retake_assessment");
                    display_none("userid-"+user_id+"-"+link_id+" #retake_assessment_loader_" + user_id);
                },
                error:function (){
                    console.log('Ajax Error!');
                    remove_disbaled_attr("tti_retake_assessment");
                    display_none("userid-"+user_id+"-"+link_id+" #retake_assessment_loader_" + user_id);
                }
            });
    }

    /**
    * Checking decrease the limit
    */
    function increase_current_user_limit(u_id,link_id) {
        var group_reg_left_text = $('#tti_group_leader_retake_wrapper #userid-'+u_id+'-'+link_id+' .ldgr-user-limit .ttisi-info-limit').text();
        var group_reg_left = group_reg_left_text.replace( /^\D+/g, '');
        console.log(group_reg_left);
        group_reg_left = parseInt(group_reg_left) + 1;
        console.log(group_reg_left);
        if(group_reg_left < 0) {
            group_reg_left = 0;
        }   
        $('#tti_group_leader_retake_wrapper #userid-'+u_id+'-'+link_id+' .ldgr-user-limit .ttisi-info-limit').text('Remaining User Limit : ' + group_reg_left);
    }
    
    /**
    * Checking decrease the limit
    */
    function check_reduce_limit(reg_left ) {
        var group_reg_left_text = $('.wdm-registration-left').text();
        var group_reg_left = group_reg_left_text.replace( /^\D+/g, '');
        if(reg_left == 0) {
            return false;
        }   
        return true;
    }

    /**
    * Decrease the limit
    */
    function reduce_limit() {
        var group_reg_left_text = $('#wdm_search_submit .wdm-select-wrapper .wdm-registration-wrapper .wdm-registration-left').first().text();
        var group_reg_left = group_reg_left_text.replace( /^\D+/g, '');
        group_reg_left = group_reg_left - 1;
        if(group_reg_left< 0) {
            group_reg_left = 0;
        }   
        $('.wdm-registration-left').text('Users Registration Left : ' + group_reg_left);
    }


    /**
    * Limit exceeds popup
    */
    function show_limit_exceed(message, status) {
        var symbol = 'warning';
        if(status == 1) {
            symbol = 'success';
        }
        Swal.fire({
          title: '',
          text: message,
          type: symbol,
          showCancelButton: false,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Close'
        }).then(function (result) {
          
        });
    }

    /**
    * Empty the text of given element.
    */
    function retake_ass_text(ele_class) {
        $('#' + ele_class).text('Retake Assessment');
    }


    /**
    * Empty the text of given element.
    */
    function empty_text(ele_class) {
        $('#' + ele_class).text('');
    }

    /**
    * Display block.
    */
    function display_block(ele_class) {
        $('#' + ele_class).css('display','block');
    }

    /**
    * Display none.
    */
    function display_none(ele_class) {
        $('#' + ele_class).css('display','none');
    }

    /**
    * Add the disabled attribute.
    */
    function add_disbaled_attr(ele_class) {
        $('.' + ele_class).attr('disabled', 'disabled');
    }

    /**
    * Add the disabled attribute.
    */
    function remove_disbaled_attr(ele_class) {
        $('.' + ele_class).removeAttr('disabled');
    }
   

    var check_same_id;

    $("#tti_group_leader_retake").on("click", ".gl-action-btn", function(event) {
       
       var id = $(this).attr("id");
        $(".gl-action-drodown").css('display','none');
        
        if($("." + id).hasClass('open')) {
            $("." + id).removeClass('open');
            $("." + id).css('display','none');  
            $(".gl-action-drodown").removeClass('open');  
            $(".gl-action-drodown").removeClass('close');   
            $("." + id).addClass('close');   
        } else if($("." + id).hasClass('close')) {
            
            $(".gl-action-drodown").removeClass('open');  
            $(".gl-action-drodown").removeClass('close');   
            $("." + id).removeClass('close');
            $("." + id).addClass('open');
            $("." + id).css('display','block'); 
        } else {
            $("." + id).removeClass('close');
            $(".gl-action-drodown").removeClass('open');  
            $(".gl-action-drodown").removeClass('close');  
            $("." + id).addClass('open');
            $("." + id).css('display','block'); 
        }

        //$(".gl-action-drodown").removeClass('open');   

    });


    /* Click event to save group settings */
    

    /* Click event to save group settings */
    //$(".pt-group-form-settings-btn").on("click",  function(event) {.switch-gp-settings
    $(".switch-gp-settings").on("click",  function(event) { 

        save_group_settings();
    });

    function save_group_settings() {

        var block_email = '';
        var group_leader_id = $('.group-leader-gp-settings-glid').data('group_leader_id');
        var group_id = $('.group-leader-gp-settings-gid').data('group_id');

        if($('.switch-gp-settings').hasClass('switch-gp-settings-left-on')) {
            block_email = 'true';
            $('.switch-gp-settings').removeClass('switch-gp-settings-left-on');
            $('.switch-gp-settings').addClass('switch-gp-settings-left-off');
        } else {
            block_email = 'false';
            $('.switch-gp-settings').addClass('switch-gp-settings-left-on');
            $('.switch-gp-settings').removeClass('switch-gp-settings-left-off');
        }


        var dataToSend = {
            group_leader_id : group_leader_id,
            action : 'tti_group_save_settings',
            block_email : block_email,
            group_id : group_id,
            nonce : mi_assessments_ldgr.mildgr_nonce,
        };
        console.log(dataToSend);
        jQuery.ajax({
                url : mi_assessments_ldgr.ajaxurl,
                type : 'post',
                dataType: 'json',
                data : dataToSend,
                beforeSend: function() {
                    swal({
                             text: 'Updating Settings Saved. Please wait...',
                             showCloseButton: false,
                             showConfirmButton: false,
                             showCancelButton: false,
                        });
                    add_disbaled_attr("pt-group-form-settings-form-btn");
                },
                success : function( response ) {
                    console.log(response);
                    if(response.status == 1) {
                         
                        Swal.fire({
                            type: 'success',
                            title: '',
                            text: 'Successfully Setting Saved',
                            footer: '',
                            confirmButtonText: 'Close'
                        });
                    } else {
                        
                    }
                    remove_disbaled_attr("pt-group-form-settings-form-btn");
                },
                error:function (){
                    console.log('Ajax Error!');
                    remove_disbaled_attr("tti_retake_assessment");
                }
            });
    }



})( jQuery );