$(document).ready(function() {
	$('#teams-select').change(function() {
	    window.location = BASE_URL + '/league/' + leagueId + '/manage/' + $(this).val();
	});
    
    $('#add-players').chosen();
    $('#remove-players').chosen();
    
    $('#add-players-button').click(function(e) {
        $.ajax({
            url: BASE_URL + '/league/' + leagueId + '/addplayer',
            data: 'players=' + $('#add-players').val(),
            success: function(resp) {
                window.location.reload();
            }
        });
    });
    
    $('#remove-players-button').click(function(e) {
        $.ajax({
            url: BASE_URL + '/league/' + leagueId + '/removeplayer',
            data: 'players=' + $('#remove-players').val(),
            success: function(resp) {
                window.location.reload();
            }
        });
    });
});
