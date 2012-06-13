<?php

class My_View_Helper_GetLeagueTeamPointDiff extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * This helper will return the page name of the parent page
     *
     * @return string
     */
    public function getLeagueTeamPointDiff($leagueId, $teamId)
    {
        $leagueGameTable = new Model_DbTable_LeagueGame();
        return $leagueGameTable->fetchTeamPointDiff($leagueId, $teamId) . ' point diff';
    }
}
