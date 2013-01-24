$(document).ready(function() {
    $('#agree').hide();

    $('#waiver-text').bind('scroll', function() {
        if($(this)[0].scrollHeight - $(this).scrollTop() >=  $(this).innerHeight()) {
            $('#agree').show();
        }
    });
});
