$('document').ready(function(){
    $('#birthday').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1940:y-4',
        dateFormat: 'yy-mm-dd',
        maxDate: '-4Y'
    });
});
