$(document).ready(function(){
    $('#leagues').tabs();
    
    $('.edit-links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});