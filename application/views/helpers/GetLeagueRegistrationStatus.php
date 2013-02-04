<?php

class My_View_Helper_GetLeagueRegistrationStatus extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function getLeagueRegistrationStatus($leagueId)
    {
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $msg = $this->view->getLeagueRegistrationMessage($leagueId);
        if($msg === null) {
            return '<span class="muted"><strong>UNKNOWN</strong></span>';
        } else if(strstr($msg, 'League registration has closed.') !== false) {
            return '<span class="text-error"><strong>CLOSED</strong></span>';
        } else if(strstr($msg, 'League registration has not yet begun.') !== false) {
            return '<span class="text-error"><strong>NOT OPEN YET</strong></span>';
        } else if(strstr($msg, 'League registration is full with') !== false) {
            return '<span class="text-error"><strong>LEAGUE FULL</strong></span>';
        } else if($msg === true) {
            return '<span class="text-success"><strong>OPEN</strong></span>';
        }
    }
}
