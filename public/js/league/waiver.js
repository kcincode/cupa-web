$(document).ready(function() {
    $('#agree').hide();
    
    $('#waiver-text').bind('scroll', function(){
        if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            $('#agree').show();
        } 
    });
});