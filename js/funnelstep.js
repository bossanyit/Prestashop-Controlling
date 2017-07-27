window.onload = function() {
    var selected4 = $("select[name=step4_source_type]").val();
    var selected5 = $("select[name=step5_source_type]").val();
    if(selected4 == "5") {
        $('#label_step4_funnel_source').text("UTM Metrics:");
        $('.utm4').show();
    } else {
        $('#label_step4_funnel_source').text("Funnel source:");
        $('.utm4').hide();
    }
    
    if(selected5 == "5") {
        $('#label_step5_funnel_source').text("UTM Metrics:");
        $('.utm5').show();
    } else {
        $('#label_step5_funnel_source').text("Funnel source:");
        $('.utm5').hide();
    }
};

$(document).ready(function() {
    
        
    $("#step4_source_type").change(function() {
            var selected= this.value;
            if(selected == "4" || selected == "5") 
            {
                $('#label_step4_funnel_source').text("UTM Metrics:");
                $('.utm4').show();
            } else {
                $('#label_step4_funnel_source').text("Funnel source:");
                $('.utm4').hide();
            }
    });
    
    $("#step5_source_type").change(function() {
            var selected= this.value;
            if(selected == "4" || selected == "5") 
            {
                $('#label_step5_funnel_source').text("UTM Metrics:");
                $('.utm5').show();
            } else {
                $('#label_step5_funnel_source').text("Funnel source:");
                $('.utm5').hide();
            }
    });
});