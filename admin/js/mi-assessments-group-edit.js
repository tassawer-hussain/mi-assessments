jQuery(document).ready(function ($) {
    $('.wdm-select-wrapper .ldgr-group-settings-wrap').remove();
    $('.wdm-select-wrapper .wdm-select-wrapper-content > h3').text('Product');
    $('#wdm_groups_tab #bulk_remove').remove();
    $("tr#wdm_members_name td:nth-child(2) input").prop('required', true);
});