<?php

class My_View_Helper_Fullname extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function fullname($user, $leagueId = false)
    {
        if(!$leagueId) {
            if(is_numeric($user)) {
                $userTable = new Model_DbTable_User();
                $userObject = $userTable->find($user)->current();
                if($userObject) {
                    return $this->view->escape($userObject->first_name) . ' ' . $this->view->escape($userObject->last_name);
                }
                
                return 'Unknown';
            } else if(is_object($user) and get_class($user) == 'Zend_Db_Table') {
                return $this->view->escape($user->first_name) . ' ' . $this->view->escape($user->last_name);
            } else {
                return 'Unknown';
            }
        } else {
            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $userTable = new Model_DbTable_User();
            if(is_numeric($user)) {
                $member = $leagueMemberTable->find($user)->current();
                if($member) {
                    $userObject = $userTable->find($member->user_id)->current();
                    return $this->view->escape($userObject->first_name) . ' ' . $this->view->escape($userObject->last_name);
                }
                
                return 'Unknown';
            } else if(is_object($user) and get_class($user) == 'Zend_Db_Table') {
                $user = $userTable->find($user->user_id)->current();
                return $this->view->escape($user->first_name) . ' ' . $this->view->escape($user->last_name);
            } else {
                return 'Unknown';
            }
        }
    }
}
