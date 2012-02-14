<?php

class My_View_Helper_HasRole extends Zend_View_Helper_Abstract
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
    public function hasRole($role)
    {
        if($this->view->user) {
            $userRoleTable = new Model_DbTable_UserRole();
            if($this->view->page and $role == 'editor') {
                return $userRoleTable->hasRole($this->view->user->id, $role, $this->view->page->id);
            } else {
                return $userRoleTable->hasRole($this->view->user->id, $role);
            }
        }
        
        return false;
    }
}
