<?php

class My_View_Helper_DisplayEmail extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function displayEmail($email)
    {
        if(is_numeric($email)) {
            $userTable = new Cupa_Model_DbTable_User();
            $user = $userTable->find($email)->current();
            if($user) {
                return $this->view->escape(str_replace('.', ' DOT ', str_replace('@', ' AT ', $user->email)));
            }
            
            return 'Unknown';
        } else {
            return $this->view->escape(str_replace('.', ' DOT ', str_replace('@', ' AT ', $email)));
        }
    }
}