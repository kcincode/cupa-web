<?php

class My_View_Helper_GetLeagueTeamName extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function getLeagueTeamName($teamId)
    {
        if(is_numeric($teamId)) {
            $leagueTeamTable = new Model_DbTable_LeagueTeam();
            $team = $leagueTeamTable->find($teamId)->current();
            return $this->view->escape($team->name);
        }

        return $this->view->escape($teamId);
    }
}
