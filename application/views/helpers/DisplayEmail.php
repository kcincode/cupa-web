<?php

class My_View_Helper_DisplayEmail extends Zend_View_Helper_Abstract
{
    public function displayEmail($email)
    {
        if(is_numeric($email)) {
            $userTable = new Cupa_Model_DbTable_User();
            $user = $userTable->find($email)->current();
            if($user) {
                return str_replace('.', ' DOT ', str_replace('@', ' AT ', $user->email));
            }
            
            return 'Unknown';
        } else {
            return str_replace('.', ' DOT ', str_replace('@', ' AT ', $email));
        }
    }
}