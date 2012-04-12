$(document).ready(function() {
   $('#since').datepicker({
       dateFormat: 'yy-mm-dd',
       changeMonth: true,
       changeYear: true
   });

   $('#to').datepicker({
       dateFormat: 'yy-mm-dd',
       changeMonth: true,
       changeYear: true,
       minDate: $('#since').val()
   });

   $('#user_id').chosen();
});