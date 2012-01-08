$(document).ready(function() {
    $('#add-season-container').dialog({
        modal: true,
        width: 365,
        height: 175,
        autoOpen: false,
        title: 'Create a Season',
        buttons: {
            "Save": function() {
                if($('#season-name').val() == '') {
                    $('#error-string').html('Please enter a season name.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/leagues_season/add',
                    data: 'name='+$('#season-name').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/leagues_season/' + obj.data + '/edit';
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            },
        }
    });


    $('#add-season').click(function(e) {
        e.preventDefault();
        $('#add-season-container').load(BASE_URL + '/leagues_season/add').dialog('open');
        $('#season-name').focus();
    });

    $('.links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});
