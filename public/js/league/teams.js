$(document).ready(function () {
    $('.player-container').hide();

    $('.info').hover(function() {
        $(this).addClass('selected');
    }, function() {
        $(this).removeClass('selected');    
    });
});
  
function loadPlayers(teamId) {
    if($('.team-' + teamId).is(':visible')) {
        $('.team-' + teamId).toggle();
        $('.team-' + teamId).html('');
    } else {
        $.ajax({
           url: BASE_URL + '/league/load_players/' + teamId,
           success: function(response) {
               $('.team-' + teamId).html(response);  
               $('.team-' + teamId).toggle();
           }
        });
    }
}
