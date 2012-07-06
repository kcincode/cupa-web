<?php

class My_View_Helper_IsTournamentAdmin extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isTournamentAdmin($tournamentId)
    {
        if($this->view->hasRole('admin') or $this->view->hasRole('manager')) {
            return true;
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if(Zend_Auth::getInstance()->hasIdentity()) {
            foreach($leagueMemberTable->fetchAllByType($tournamentId, 'director') as $member) {
                if($member->user_id == $this->view->user->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
