<?php

class My_View_Helper_IsLeagueDirector extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function isLeagueDirector($leagueId)
    {        
        if($this->view->hasRole('admin')) {
            return true;
        }
        
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if(Zend_Auth::getInstance()->hasIdentity()) {
            foreach($leagueMemberTable->fetchAllByType($leagueId, 'director') as $member) {
                if($member->user_id == $this->view->user->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
