<?php

class My_View_Helper_DisplayLeagueGameOutcome extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function displayLeagueGameOutcome($gameId, $teamId)
    {
        $leagueGameDataTable = new Model_DbTable_LeagueGameData();
        return $leagueGameDataTable->fetchOutcome($gameId, $teamId);
    }
}
