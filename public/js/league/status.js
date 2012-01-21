$(document).ready(function(){
    $('#all').click(function(){
        window.location = BASE_URL + '/league/' + leagueId + '/status/all';
    });
    
    $('#need-action').click(function(){
        window.location = BASE_URL + '/league/' + leagueId + '/status';        
    });
    
    $('input.update').click(function(){
        var data = $(this).prop('name');
        
        $.ajax({
            type: 'post',
            url: document.URL,
            data: 'data=' + data + '-' + $(this).prop('checked') + '&year=' + $(this).data('year'),
            error: function(msg) {
                alert('An error occured and did not save your modifications.');
            }
        });
    });
    
});