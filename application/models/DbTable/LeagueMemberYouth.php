<?php

class Model_DbTable_LeagueMemberYouth extends Zend_Db_Table
{
    protected $_name = 'league_member_youth';
    protected $_primary = 'id';

    public function fetchAllByType($leagueId, $position, $teamId = null)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('position = ?', $position);


        if($teamId) {
           $select->where('league_team_id = ?', $teamId);
        }

        return $this->fetchAll($select);
    }

    public function fetchAllPlayerData($leagueId, $teamId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('league_team_id = ?', $teamId)
                       ->where('position = ?', 'player')
                       ->order('last_name')
                       ->order('first_name');

        return $this->fetchAll($select);
    }
}
