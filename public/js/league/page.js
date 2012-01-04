$(document).ready(function() {
    $('#add-league-container').dialog({
        modal: true,
        width: 290,
        height: 200,
        autoOpen: false,
        title: 'Create a League',
        open: function(event, ui) {
            $.ajax({
                type: 'get',
                url: BASE_URL + '/league/add',
                success: function(response) {
                    $('#add-league-container').html(response);
                }
            });
        },
        buttons: {
            "Save": function() {
                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/league/add',
                    data: 'year='+$('#league-year').val()+'&season='+$('#add-league').data('season')+'&day='+$('#league-day').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            //window.location = BASE_URL + '/leagues/' + obj.data;
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            },
        }
    });

   
    $('#add-league').click(function(e) {
        e.preventDefault();
        $('#add-league-container').dialog('open');
    });

    $('#leagues').tabs();
    
    $('.edit-links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});
