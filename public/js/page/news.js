$(document).ready(function() {
    $('#add-news-container').dialog({
        modal: true,
        width: 350,
        height: 180,
        autoOpen: false,
        title: 'Create a News Item',
        buttons: {
            "Create": function() {
                if($('#news-title').val() == '') {
                    $('#error-string').html('Please enter a news title.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/news/add',
                    data: 'title='+$('#news-title').val()+'&category='+$('#news-type').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/news/' + obj.data + '/edit';
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $('#add-news').click(function(e) {
        e.preventDefault();
        $('#add-news-container').load(BASE_URL + '/news/add').dialog('open');
        $('#news-title').focus();
    });
    
    $('#remove_at').datetimepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        changeMonth: true,
        changeYear: true
    });
});
