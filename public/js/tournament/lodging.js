$(document).ready(function(){
    $('#lodging .actions a img').hover(function() {
        $(this).parent().parent().parent().addClass('highlight');
    }, function() {
        $(this).parent().parent().parent().removeClass('highlight');
    });

    $('#lodging .actions a.delete').click(function(e){
        if(!confirm('Are you sure you want to delete this lodging?')) {
            e.preventDefault();
        }
    });
});