$(document).ready(function() {
    $('#description .link').hide();
    
    $('#description').hover(function(){
         $('#description .link').show();
         $(this).addClass('highlight');
    }, function() {
        $('#description .link').hide();
         $(this).removeClass('highlight');
    });
    
});