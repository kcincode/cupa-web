$(document).ready(function(){
    $('#home_field').parent().parent().hide();
    
    $('#home_advantage').change(function(){
        if($(this).val() != '0') {
            $('#home_field').parent().parent().show();
        } else {
            $('#home_field').parent().parent().hide();
        }
    });
});