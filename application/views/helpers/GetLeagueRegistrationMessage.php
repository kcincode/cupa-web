<?php

class My_View_Helper_GetLeagueRegistrationMessage extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function getLeagueRegistrationMessage($leagueId)
    {
        $leagueTable = new Model_DbTable_League();
        $leagueLimitTable = new Model_DbTable_LeagueLimit();
        $leagueInformationTable = new Model_DbTable_LeagueInformation();

        $league = $leagueTable->find($leagueId)->current();
        if(!$league) {
            return null;
        }

        $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);
        if(!$leagueInformation) {
            return null;
        }

        $leagueLimit = $leagueLimitTable->fetchLimits($leagueId);
        if(!$leagueLimit) {
            return null;
        }

        if(strtotime(date('Y-m-d H:i:s')) > strtotime($league->registration_end)) {
            return 'League registration has closed.  It closed at ' . date('l, F dS, Y h:i:s A', strtotime($league->registration_end));
        }

        if(strtotime(date('Y-m-d H:i:s')) < strtotime($league->registration_begin)) {
            return 'League registration has not yet begun.  It will open at ' . date('l, F dS, Y h:i:s A', strtotime($league->registration_begin));
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $genders = $leagueMemberTable->fetchAllPlayersByGender($leagueId);
        $totalPlayers = $genders['male_players'] + $genders['female_players'];
        if($leagueLimit->total_players !== null and $totalPlayers >= $leagueLimit->total_players) {
            return 'League registration is full with ' . $leagueLimit->total_players . ' players.';
        }

        if(!empty($leagueLimit->male_players) and $genders['male_players'] >= $leagueLimit->male_players) {
            return 'League registration is full for male players with ' . $leagueLimit->male_players . ' male players.';
        }
        if(!empty($leagueLimit->female_players) and $genders['female_players'] >= $leagueLimit->female_players) {
            return 'League registration is full for female players with ' . $leagueLimit->female_players . ' male players.';
        }

        return null;
    }
}
