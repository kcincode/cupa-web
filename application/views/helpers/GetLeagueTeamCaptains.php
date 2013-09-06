<?php

class My_View_Helper_GetLeagueTeamCaptains extends Zend_View_Helper_Abstract
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
    public function getLeagueTeamCaptains($leagueId, $teamId, $youth = false)
    {
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        return ($youth) ? $leagueMemberTable->fetchAllByType($leagueId, 'coaches', $teamId) : $leagueMemberTable->fetchAllByType($leagueId, 'captain', $teamId);
    }
}
