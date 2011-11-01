<?php

class My_View_Helper_Fullname extends Zend_View_Helper_Abstract
{
    
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    /**
     * This helper will return true if the userId has the role
     * specified and false otherwise 
     * 
     * @param Zend_Db_Rowset $user
     * @param string $role 
     * @return boolean
     */
    public function fullname($user)
    {
        if(is_numeric($user)) {
            $userTable = new Cupa_Model_DbTable_User();
            $userObject = $userTable->find($user)->current();
            return $userObject->first_name . ' ' . $userObject->last_name;
        } else if(get_class($user) == 'Zend_Db_Table_Row') {
            return $user->first_name . ' ' . $user->last_name;
        } else {
            return 'Unknown';
        }
    }
}