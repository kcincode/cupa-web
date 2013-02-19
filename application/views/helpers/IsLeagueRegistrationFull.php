<?php

class My_View_Helper_IsLeagueRegistrationFull extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isLeagueRegistrationFull($leagueId)
    {
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $start = strtotime($league->registration_begin);
        $end = strtotime($league->registration_end);
        $date = time();
        if($date >= $start and $date < $end) {
            if(strstr($this->view->getLeagueRegistrationMessage($leagueId), 'League registration is full with') !== false) {
                return true;
            }

            return false;
        }

        return false;
    }
}
