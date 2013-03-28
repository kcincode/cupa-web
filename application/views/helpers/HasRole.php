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
    public function hasRole($role, $pageId = null)
    {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            // check in db
            $userRoleTable = new Model_DbTable_UserRole();
            if($pageId and $role == 'editor') {
                return $userRoleTable->hasRole(Zend_Auth::getInstance()->getIdentity(), $role, $page->id);
            } else {
                return $userRoleTable->hasRole(Zend_Auth::getInstance()->getIdentity(), $role);
            }
        }

        return false;
    }
}
