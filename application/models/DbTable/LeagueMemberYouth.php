<?php

class Model_DbTable_LeagueMemberYouth extends Zend_Db_Table
{
    protected $_name = 'league_member_youth';
    protected $_primary = 'id';

    public function fetchAllByType($leagueId, $position, $teamId = null)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId);

        if($position == 'coaches') {
            $select->where('position LIKE ?', '%coach%')
                   ->order('position DESC')
                   ->order('last_name')
                   ->order('first_name');
        } else {
            $select->where('position = ?', $position)
                   ->order('position DESC')
                   ->order('last_name')
                   ->order('first_name');
        }

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

    public function fetchAllCoachesWithTeams($leagueId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lmy' => $this->_name), array('*'))
                       ->joinLeft(array('lt' => 'league_team'), 'lt.id = lmy.league_team_id', array('name'))
                       ->joinLeft(array('u' => 'user'), 'u.email = lmy.email', array('first_name', 'last_name'))
                       ->where('lmy.position LIKE ?', '%coach%')
                       ->order('lt.name')
                       ->order('lmy.position DESC')
                       ->order('u.last_name')
                       ->order('u.first_name');

        return $this->getAdapter()->fetchAll($select);
    }
}
