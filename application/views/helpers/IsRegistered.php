<?php

class My_View_Helper_IsRegistered extends Zend_View_Helper_Abstract
{
    
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isRegistered($leagueId, $userId)
    {
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $member = $leagueMemberTable->fetchUserRegistrants($leagueId, $userId);

        if($member) {
            return true;
        }

        
        return false;
    }
}
