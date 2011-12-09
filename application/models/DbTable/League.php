<?php

class Cupa_Model_DbTable_League extends Zend_Db_Table
{
    protected $_name = 'league';
    protected $_primary = 'id';
    
    
    public function fetchCurrentLeaguesBySeason($season)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('l' => $this->_name), array('*'))
                       ->joinLeft(array('ls' => 'league_season'), 'ls.id = l.season', array('name AS season'))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array())
                       ->where('ls.name = ?', $season)
                       ->where('li.is_youth = ?', 0)
                       ->where('l.visible_from <= ?', date('Y-m-d H:i:s'))
                       ->order('l.year DESC');
        
        $stmt = $this->getAdapter()->query($select);
        
        $data = array();
        $year = null;
        foreach($stmt->fetchAll() as $row) {
            if($year != null and $year != $row['year']) {
                break;
            }
            $data[] = $this->fetchLeagueData($row['id']);
            $year = $row['year'];
        }
        
        return $data;
    }
    
    public function fetchLeagueData($leagueId)
    {
        $leagueLimitTable = new Cupa_Model_DbTable_LeagueLimit();
        $leagueInformationTable = new Cupa_Model_DbTable_LeagueInformation();
        $leagueLocationTable = new Cupa_Model_DbTable_LeagueLocation();
        $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        
        $league = $this->find($leagueId)->current();
        if($league) {
            $row = array();
            $row = $league->toArray();
            $row['limits'] = $leagueLimitTable->fetchLimits($row['id'])->toArray();
            $row['information'] = $leagueInformationTable->fetchInformation($row['id'])->toArray();
            $row['locations'] = $leagueLocationTable->fetchLocations($row['id']);
            $row['directors'] = $leagueMemberTable->fetchAllByType($row['id'], 'director')->toArray();
            $row['teams'] = count($leagueTeamTable->fetchAllTeams($row['id']));
            $row['total_players'] = count($leagueMemberTable->fetchAllByType($row['id'], 'player'));
            $playerGenders = $leagueMemberTable->fetchAllPlayersByGender($row['id']);
            $row['male_players'] = $playerGenders['male_players'];
            $row['female_players'] = $playerGenders['female_players'];
            return $row;
        }
        
    }
}