<?php

class Cupa_Model_DbTable_LeagueTeam extends Zend_Db_Table
{
    protected $_name = 'league_team';
    protected $_primary = 'id';
    
    public function fetchAllTeams($leagueId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->order('name');
        
        return $this->fetchAll($select);
    }
    
}
