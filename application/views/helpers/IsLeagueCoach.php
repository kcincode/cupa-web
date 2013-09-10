<?php

class My_View_Helper_IsLeagueCoach extends Zend_View_Helper_Abstract
{

    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isLeagueCoach($data)
    {
        if($this->view->isViewable('league_players')) {
            return true;
        }

        if($data['user_id'] == $this->view->user->id) {
            return true;
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        foreach($leagueMemberTable->fetchAllByType($data['league_id'], 'coach', $data['team_id']) as $coach) {
            if($coach['user_id'] == $this->view->user->id) {
                return true;
            }
        }

        return false;
    }
}
