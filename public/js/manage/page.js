$(document).ready(function() {
    $('#pages').chosen({
        search_contains: true
    }); 
    
    $('#goto-page').click(function(e){
        window.location = BASE_URL + '/' + $('#pages').val();
    });
});