<?php

class Cupa_Model_Email {
    
    static public function sendActivationEmail($user)
    {
        $mail = new Zend_Mail();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/activate/' . $user->activation_code;
        
        //$mail->setFrom('user-registration@cincyultimate.org');
        $mail->setSubject('[CUPA] User Account Activation');
        $mail->setBodyText("Hello\r\n    Thank you for requesting an account 
            with the CUPA web system.  If you did not create an account on the 
            CUPA website then you can safely ignore this message.  If you did 
            create an account on the website then please follow the directions 
            below.\r\n\r\nFollow the link below to activate your account:\r\n
            $url\r\n\r\nThank you,\r\nCUPA Webmaster\r\n");
        
        if(APPLICATION_ENV == 'production') {
            // send email to user
            $mail->addTo($user->email);
            $mail->send();
        } else {
            // send email to me with user address pre-pended
            $mail->addTo('kcin1018@gmail.com');
            $mail->setBodyText("To: " . $user->email . "\r\n\r\n" . $mail->getBodyText()->getContent());
            Zend_Debug::dump($mail);
            $mail->send();
        }
    }
}