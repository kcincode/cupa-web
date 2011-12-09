$(document).ready(function() {
    $('#directors').chosen();
    
    $('#league_start').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        onSelect: function ( selectedDateTime ) {
            var start = $(this).datetimepicker('getDate');
            $('#league_end').datetimepicker('option', 'minDate', new Date(start));
        },
    }) 
    
    $('#league_end').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        minDate: $('#league_start').val(),
    })     
    
    $('#tournament_start').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        onSelect: function ( selectedDateTime ) {
            var start = $(this).datetimepicker('getDate');
            $('#tournament_end').datetimepicker('option', 'minDate', new Date(start));
        },
    }) 
    
    $('#tournament_end').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        minDate: $('#tournament_start').val(),
    })     

    $('#draft_start').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        onSelect: function ( selectedDateTime ) {
            var start = $(this).datetimepicker('getDate');
            $('#draft_end').datetimepicker('option', 'minDate', new Date(start));
        },
    }) 
    
    $('#draft_end').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        minDate: $('#draft_start').val(),
    })     


    $('#registration_begin').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
    }) 
    
    $('#registration_end').datetimepicker({
        changeMonth: true,
	changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        minDate: $('#registration_begin').val(),
    }) 
    
    $('#tournament_ignore').click(function(){
        if($(this).prop('checked')) {
            if(confirm('This will remove the current data that is entered.  Do you want to continue?')) {
                hideLocation('tournament');
            }
        } else {
            showLocation('tournament');
        }
    });
    
    $('#draft_ignore').click(function(){
        if($(this).prop('checked')) {
            if(confirm('This will remove the current data that is entered.  Do you want to continue?')) {
                hideLocation('draft');
            }
        } else {
            showLocation('draft');
        }
    });
    
    $('#copy_league').click(function() {
        $('#tournament_name').val($('#league_name').val());

        $('#tournament_address_street').val($('#league_address_street').val());
        $('#tournament_address_city').val($('#league_address_city').val());
        $('#tournament_address_state').val($('#league_address_state').val());
        $('#tournament_address_zip').val($('#league_address_zip').val());
    });
    
    if($('#tournament_ignore').prop('checked')) {
        hideLocation('tournament');
    }
    
    if($('#draft_ignore').prop('checked')) {
        hideLocation('draft');
    }
        
});

function hideLocation(type)
{
    $('#' + type + '_name').val('');
    $('#' + type + '_name-label').hide('');
    $('#' + type + '_name-element').hide('');

    $('#' + type + '_address_street').val('');
    $('#' + type + '_address_city').val('');
    $('#' + type + '_address_state').val('');
    $('#' + type + '_address_zip').val('');
    $('#' + type + '_address-label').hide('');
    $('#' + type + '_address-element').hide('');

    $('#' + type + '_start').val('');
    $('#' + type + '_start-label').hide('');
    $('#' + type + '_start-element').hide('');

    $('#' + type + '_end').val('');
    $('#' + type + '_end-label').hide('');
    $('#' + type + '_end-element').hide('');

    $('#' + type + '_map_link').val('');
    $('#' + type + '_map_link-label').hide('');
    $('#' + type + '_map_link-element').hide('');

    $('#' + type + '_photo_link').val('');
    $('#' + type + '_photo_link-label').hide('');
    $('#' + type + '_photo_link-element').hide('');
    
    if(type == 'tournament') {
        $('#copy_league').hide();
    }
}

function showLocation(type)
{
    $('#' + type + '_name-label').show('');
    $('#' + type + '_name-element').show('');

    $('#' + type + '_address-label').show('');
    $('#' + type + '_address-element').show('');
    
    $('#' + type + '_start-label').show('');
    $('#' + type + '_start-element').show('');
    
    $('#' + type + '_end-label').show('');
    $('#' + type + '_end-element').show('');
    
    $('#' + type + '_map_link-label').show('');
    $('#' + type + '_map_link-element').show('');
    
    $('#' + type + '_photo_link-label').show('');
    $('#' + type + '_photo_link-element').show('');
    
    if(type == 'tournament') {
        $('#copy_league').show();
    }
}