$(document).ready(function() {
	$('#teams-select').change(function() {
	    window.location = BASE_URL + '/league/' + leagueId + '/manage/' + $(this).val();
	});
});