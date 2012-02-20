<?php

class My_View_Helper_IsClubCaptain extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function isClubCaptain($clubId)
    {        
        if($this->view->hasRole('admin')) {
            return true;
        }

        $clubCaptainTable = new Model_DbTable_ClubCaptain();

        foreach($clubCaptainTable->fetchAllByClub($clubId) as $captain) {
            if($this->view->user->id == $captain['user_id']) {
                return true;
            }
        }
        
        return false;
    }
}
