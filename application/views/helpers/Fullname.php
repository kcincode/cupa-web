<?php

class My_View_Helper_Fullname extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function fullname($user)
    {
        if(is_numeric($user)) {
            $userTable = new Cupa_Model_DbTable_User();
            $userObject = $userTable->find($user)->current();
            if($userObject) {
                return $this->view->escape($userObject->first_name) . ' ' . $this->view->escape($userObject->last_name);
            }
            
            return 'Unknown';
        } else if(get_class($user) == 'Zend_Db_Table') {
            return $this->view->escape($user->first_name) . ' ' . $this->view->escape($user->last_name);
        } else {
            return 'Unknown';
        }
    }
}