(function ($) {
    'use strict';

    //$(".tti-asses-history-sliderbutton").append($(".tti-asses-history-slider"));


    $(".tti-asses-history-slider").insertAfter($(".assessment_button"));
    $(".tti-asses-history-sliderbutton").insertAfter($(".assessment_button"));

    if ( ! $(".tti-asses-history-slider ol li").length) {
        $(".tti-activity-feed h3").text('No Assessment History');
    }
    
    $(".tti-asses-history-sliderbutton a").click(function () {
        $(".tti-asses-history-slider").slideToggle('slow');
    });

})(jQuery);