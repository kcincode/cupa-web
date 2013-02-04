<?php

class My_View_Helper_IsLeagueRegistrationOpen extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isLeagueRegistrationOpen($leagueId)
    {
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $start = strtotime($league->registration_begin);
        $end = strtotime($league->registration_end);
        $date = time();
        if($date >= $start and $date < $end) {
            if($this->view->getLeagueRegistrationMessage($leagueId) === true) {
                return true;
            }

            return false;
        }

        return false;
    }
}
