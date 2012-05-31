<?php

class My_View_Helper_GenerateEmailLink extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function generateEmailLink($email, $display = null)
    {
        if(is_numeric($email)) {
            $userTable = new Model_DbTable_User();
            $user = $userTable->find($email)->current();
            $email = $user->email;
        }
        if($display) {
            return '<a href="' . $this->convert('mailto:' . $email) . '">' . $this->convert($display) . '</a>';
        } else {
            return '<a href="' . $this->convert('mailto:' . $email) . '">' . $this->convert($email) . '</a>';
        }
    }
    
    private function convert($string)
    {
        $newString = '';
        for($i = 0; $i < strlen($string); $i++) {
            $newString .= '&#' . ord($string[$i]);
        }
        
        return $newString;
    }
}
