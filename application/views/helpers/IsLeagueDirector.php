<?php

class My_View_Helper_IsLeagueDirector extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isLeagueDirector($leagueId = null)
    {
        if($this->view->hasRole('admin') || $this->view->hasRole('manager')) {
            return true;
        }

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return false;
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if($leagueId) {
            foreach($leagueMemberTable->fetchAllByType($leagueId, 'director') as $member) {
                if($member->user_id == Zend_Auth::getInstance()->getIdentity()) {
                    return true;
                }
            }
        } else {
            return $leagueMemberTable->isALeagueDirector(Zend_Auth::getInstance()->getIdentity());
        }

        return false;
    }
}
