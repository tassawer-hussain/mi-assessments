/**
 * Add dynamic fields mapping
 */
jQuery(document).ready(function () {

    var addButton = jQuery('.add_button'); //Add button selector
    var wrapper = jQuery('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="tti-mapping-fields"><div class="tti-mapping-fiel-sect"><input placeholder="Input Response Tag" id="response_id" name="response_id[]" /></div><div class="tti-mapping-fiel-sect"><input placeholder="Input Instrument ID" id="instrument_id" name="instrument_id[]" /></div><a href="javascript:void(0);" class="remove_button"><img src="http://demos.codexworld.com/add-remove-input-fields-dynamically-using-jquery/images/remove-icon.png"/></a></div><div class="clear-fl-effect"></div>'; //New input field html 
    var x = 1; //Initial field counter is 1

    //Once add button is clicked
    jQuery(addButton).click(function () {
        //Check maximum number of input fields
        jQuery(wrapper).append(fieldHTML); //Add field html

    });

    //Once remove button is clicked
    jQuery(wrapper).on('click', '.remove_button', function (e) {
        e.preventDefault();
        jQuery(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });

    /* Save mapping rules click event */
    jQuery(document).on("click", "#tti-mapping-submit", function (e) {
        e.preventDefault();
        if (validate_the_fields() === false) {
            assess_empty();
        } else {
            var datastring = jQuery("#tti-map-form").serialize();
            save_mapping_data(datastring);
        }

    });

    /**
    * Function to save mapping data
    */
    function save_mapping_data(data) {
        valid = true;
        if (data) {
            jQuery.ajax({
                url: tti_platform_admin_user_obj.ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'save_mapping_data',
                    data: data,
                },
                beforeSend: function () {
                    jQuery('#tti-mapping-submit').prop('disabled', true);
                    jQuery('#loader_save_mapp').css('display', 'block');
                },
                success: function (response) {
                    if (response.status == 'success') {
                        map_success_add();
                    } else {
                        assess_mapp_add();
                    }
                    jQuery('#tti-mapping-submit').prop('disabled', false);
                    jQuery('#loader_save_mapp').css('display', 'none');
                },
                error: function () {
                    console.log('Error!');
                }
            });
        } else {
            jQuery('#tti-mapping-submit').prop('disabled', false);
            jQuery('#loader_save_mapp').css('display', 'block');
        }

    }

    /**
    * Validate the fields
    */
    function validate_the_fields() {
        jQuery('#tti-map-form input').each(function () {
            if (jQuery(this).val() == "") {
                return false;
            }
        });
    }

    /**
   * Add wrong assessment alert
   */
    function assess_empty() {
        Swal.fire({
            title: 'Error',
            text: "All fields are required",
            type: 'error',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    }

    /**
    * Add wrong assessment alert
    */
    function assess_mapp_add() {
        Swal.fire({
            title: 'Error',
            text: "Please try again!",
            type: 'error',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    }

    /**
    * Add wrong assessment alert
    */
    function map_success_add() {
        Swal.fire({
            title: 'Mapping Data Successfully Saved',
            text: "",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }

});