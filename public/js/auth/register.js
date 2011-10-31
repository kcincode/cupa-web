$(document).ready(function (){
    
   $('#email').blur(function(e) {
       if(($('#email').val() != '')) {
           // check for valid email address
           $.ajax({
              type: 'post',
              url: BASE_URL + '/register/' + $('#email').val(),
              success: function(response) {
                 if(response == 'ok') {
                     $('#email-status').html('<span class="ok">Ok</span>');
                 } else if(response == 'error') {
                     $('#email-status').html('<span class="error">Already in use</span>');
                 } else if(response == 'invalid') {
                     $('#email-status').html('<span class="error">Invalid Email</span>');
                 } else {
                     $('#email-status').html('<span class="error">Unknown error please try again.</span>');
                 }
              },
           });
       }
   });
   


   $('#reg-submit-button').click(function(e) {
       e.preventDefault();
       
       if($('#email-status').html() == '<span class="ok">Ok</span>') {
           if($('#first_name').val() && $('#last_name').val()) {
               $('#registration-form').submit();
           } else {
               $('#email-status').html('<span class="error">Please enter first and last name.</span>');
           }
       }
   });
   
});