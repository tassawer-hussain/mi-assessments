(function( $ ) {
    'use strict';
    	
       /* Initialize the  completed profile datatables */
       let tti_assessment_cp_table = $('#tti_assessment_cp_table').DataTable({
        'columnDefs': [{
            'targets': 1,
            'checkboxes': {
               'selectRow': true
            }
        }],
        'pageLength': 50,
        'select': {
            'style': 'multi'
        },
         "language": {
            "lengthMenu": mi_completed_profiles.menu_display,
            "zeroRecords": mi_completed_profiles.zeroRecords,
            "info": mi_completed_profiles.info,
            "infoEmpty": mi_completed_profiles.infoEmpty,
            "infoFiltered": mi_completed_profiles.infoFiltered,
            "search": mi_completed_profiles.Search,
            "paginate": {
                "first" :     mi_completed_profiles.First,
                "previous" :  mi_completed_profiles.Previous,
                "next" :      mi_completed_profiles.Next,
                "last" :      mi_completed_profiles.Last
            },
        },
        responsive: true
    });

    // $('#tti_cp_download_btn').on('click', function () { 
    //     var email = $(this).data('email');
    //     var asses_id = $(this).data('assess');
    //     var href = $(this).data('href');
    //     console.log(email);
    //     console.log(asses_id);
    //     console.log(href);

    // });

   
})( jQuery );