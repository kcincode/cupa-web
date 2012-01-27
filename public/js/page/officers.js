$(document).ready(function() {

    $('#add-officers-container').dialog({
        modal: true,
        width: 340,
        height: 180,
        autoOpen: false,
        title: 'Create an Officer',
        buttons: {
            "Create": function() {
                if($('#officers-position').val() == '') {
                    $('#error-string').html('Please enter a position.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/officers/add',
                    data: 'position='+$('#officers-position').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/officers/' + obj.data;
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });


    $('#add-officer').click(function(e) {
        e.preventDefault();
        $('#add-officers-container').load(BASE_URL + '/officers/add').dialog('open');
        $('#officers-position').focus();
    });

    
    $('.links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});
