$(document).ready(function(){
    $('#lodging .actions a img').hover(function() {
        $(this).parent().parent().parent().addClass('highlight');
    }, function() {
        $(this).parent().parent().parent().removeClass('highlight');
    });
});