$(document).ready(function (){
    
   $('#email').keyup(function(e) {
       if(($('#email').val() != '' && $('#email').val().length > 5)) {
           // check for valid email address
           $.ajax({
              type: 'post',
              url: BASE_URL + '/register/' + $('#email').val(),
              success: function(response) {
                 if(response == 'ok') {
                     $('#email-status').html('<span class="ok">Email Ok</span>');
                 } else if(response == 'error') {
                     $('#email-status').html('<span class="error">Email already in use</span>');
                 } else if(response == 'invalid') {
                     $('#email-status').html('<span class="error">Invalid Email</span>');
                 } else {
                     $('#email-status').html('<span class="error">Unknown error please try again.</span>');
                 }
              }
           });
       }
   });
   


   $('#reg-submit-button').click(function(e) {
       e.preventDefault();
       
       if($('#email-status').html().toLowerCase() == '<span class="ok">email ok</span>' || $('#email-status').html().toLowerCase() == '<span class=ok>email ok</span>') {
           if($('#first_name').val() && $('#last_name').val()) {
               $('#registration-form').submit();
           } else {
               $('#email-status').html('<span class="error">Please enter first and last name.</span>');
           }
       } else {
           $('#email-status').html('<span class="error">Please enter all information.</span>');
       }
   });
   
});