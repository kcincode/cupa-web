$(document).ready(function() {
    $('#add-form-container').dialog({
        modal: true,
        width: 315,
        height: 235,
        autoOpen: false,
        title: 'Create a new Form',
        buttons: {
            "Create": function() {
                if($('#form-name').val() == '') {
                    $('#error-string').html('Please enter a name for the form.');
                    return;
                }

                if($('#form-year').val() == '') {
                    $('#error-string').html('Please enter a year for the form.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/forms/add',
                    data: 'name='+$('#form-name').val()+'&year='+$('#form-year').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                    	if(obj.message == 'success') {
                            window.location = BASE_URL + '/forms/' + obj.formId + '/edit';
                        } else {
                        	$('#error-string').html('Duplicate form exists.');
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });


    $('#add-form').click(function(e) {
        e.preventDefault();
        $('#add-form-container').load(BASE_URL + '/forms/add').dialog('open');
        $('#form-name').focus();
    });});