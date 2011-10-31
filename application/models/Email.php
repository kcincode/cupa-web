<?php

class Cupa_Model_Email {
    
    private static $_mail;
    private static $_user;
    
    static public function sendActivationEmail($user)
    {
        self::$_user = $user;
        self::$_mail = new Zend_Mail();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/activate/' . self::$_user->activation_code;
        
        self::$_mail->setFrom('user-registration@cincyultimate.org');
        self::$_mail->setSubject('[CUPA] User Account Activation');
        self::$_mail->setBodyText("Hello/r/n    Thank you for requesting an account 
            with the CUPA web system.  If you did not create an account on the 
            CUPA website then you can safely ignore this message.  If you did 
            create an account on the website then please follow the directions 
            below.\r\n\r\nFollow the link below to activate your account:\r\n
            $url\r\n\r\nThank you,\r\nCUPA Webmaster");
        
        self::send();
       
    }
    
    static private function send()
    {
        if(APPLICATION_ENV == 'production') {
            // send email to user
            self::$_mail->addTo($user->email);
            self::$_mail->send();
        } else {
            // send email to me with user address pre-pended
            self::$_mail->addTo('kcin1018@gmail.com');
            self::$_mail->setBodyText("To: " . self::$_user->email . "/r/n/r/n" . self::$_mail->getBodyText()->getContent());
            self::$_mail->send();
        }
    }
}