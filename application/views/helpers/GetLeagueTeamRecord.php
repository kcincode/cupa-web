<?php

class My_View_Helper_GetLeagueTeamRecord extends Zend_View_Helper_Abstract
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
    public function getLeagueTeamRecord($leagueId, $teamId)
    {
        $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
        return $leagueGameTable->fetchRecord($leagueId, $teamId);
    }
}