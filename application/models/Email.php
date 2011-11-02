<?php

class Cupa_Model_Email {
    
    static public function sendActivationEmail($user)
    {
        $mail = new Zend_Mail();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/activate/' . $user->activation_code;
        
        //$mail->setFrom('user-registration@cincyultimate.org');
        $mail->setSubject('[CUPA] User Account Activation');
        $mail->setBodyText("Hello {$user->first_name} {$user->last_name}\r\n    
            Thank you for requesting an account with the CUPA web system.  If you 
            did not create an account on the CUPA website then you can safely ignore 
            this message.  If you did create an account on the website then please 
            follow the directions below.\r\n\r\nFollow the link below to activate 
            your account:\r\n$url\r\n\r\nThank you,\r\nCUPA Webmaster\r\n");
        
        self::send($user, $mail);
    }
    
    static public function sendPasswordResetEmail($user, $passwordReset)
    {
        $mail = new Zend_Mail();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/reset/' . $passwordReset->code;
        
        //$mail->setFrom('user-registration@cincyultimate.org');
        $mail->setSubject('[CUPA] User Account Activation');
        $mail->setBodyText("Hello {$user->first_name} {$user->last_name}\r\n    
            Thank you for requesting a password reset.  If you did not request a 
            reset of the password you can safely ignore this message.  If you did 
            request a reset of the password for this email addresss then please 
            follow the directions below.\r\n\r\nFollow the link below to reset your 
            password:\r\n$url\r\n\r\nThank you,\r\nCUPA Webmaster\r\n");

        self::send($user, $mail);
    }
    
    static public function sendContactEmail($data)
    {
        // create the mail object and set the variables
        $mail = new Zend_Mail();
        $mail->addTo($data['to']);
        $mail->addBcc('kcin1018@gmail.com');
        $mail->setFrom($data['from']);
        $mail->setSubject($data['subject']);
        $mail->setBodyText($data['content']);
                
        self::send($user, $mail);
    }
    
    static private function send($user, $mail)
    {
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