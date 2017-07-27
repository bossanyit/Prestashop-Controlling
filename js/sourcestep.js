window.onload = function() {
    var selected2 = $("#step2source_type").val();   
    if(selected2 == "9") {
        $('#label_step2_source').text("UTM Metrics:");
        $('.utm2').show();
    } else {
        $('#label_step2_source').text("Source:");
        $('.utm2').hide();
    }
    if(selected2 == "12") {
        $('#label_step2_source').text("Campaign:");
        $('#label_step2_dimensions').text("Status ID:");
        $('.utm2').show();
    }

    
    var selected3 = $("select[name=step3_source_type]").val();   
    if(selected3 == "10") {
        $('#label_step3_source').text("Coupons:");
        $('.utm3').show();
    } else {
        $('#label_step3_source').text("Source:");
        $('.utm3').hide();
    }
    
    var selected4 = $("select[name=step4_source_type]").val();   
    if(selected4 == "10") {
        $('#label_step4_source').text("Coupons:");
        $('.utm4').show();
    } else {
        $('#label_step4_source').text("Source:");
        $('.utm4').hide();
    }

};

$(document).ready(function() {
    
        
    $("#step2source_type").change(function() {
            var selected= this.value;
            if(selected == "9" ) 
            {
                $('#label_step2_source').text("UTM Metrics:");
                $('.utm2').show();
            } else {
                $('#label_step2_source').text("Source:");
                $('.utm2').hide();
            }
            if(selected == "12" ) 
            {
                $('#label_step2_source').text("Campaign:");
                $('#label_step2_dimensions').text("Status ID:");
                $('.utm2').show();
            }
    });
    
    $("#step3_source_type").change(function() {
            var selected= this.value;
            if(selected == "10" ) 
            {
                $('#label_step3_source').text("Coupons:");
                $('.utm3').show();
            } else {
                $('#label_step3_source').text("Source:");
                $('.utm3').hide();
            }
    });
    
    $("#step4_source_type").change(function() {
            var selected= this.value;
            if(selected == "10" ) 
            {
                $('#label_step4_source').text("Coupons:");
                $('.utm4').show();
            } else {
                $('#label_step4_source').text("Source:");
                $('.utm4').hide();
            }
    });
    
});