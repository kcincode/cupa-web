<?php

class Model_DbTable_League extends Zend_Db_Table
{
    protected $_name = 'league';
    protected $_primary = 'id';
    
    
    public function fetchCurrentLeaguesBySeason($season, $all = false)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('l' => $this->_name), array('*'))
                       ->joinLeft(array('ls' => 'league_season'), 'ls.id = l.season', array('name AS season'))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array())
                       ->where('ls.name = ?', $season)
                       ->where('li.is_youth = ?', 0)
                       ->where('is_archived = ?', 0)
                       ->order('l.year DESC');
        
        if(!$all) {
            $select->where('l.visible_from <= ?', date('Y-m-d H:i:s'));
        }
        
        $stmt = $this->getAdapter()->query($select);
        
        $data = array();
        foreach($stmt->fetchAll() as $row) {
            $data[] = $this->fetchLeagueData($row['id']);
        }
        
        return $data;
    }
    
    public function fetchLeagueData($leagueId)
    {
        $leagueLimitTable = new Model_DbTable_LeagueLimit();
        $leagueInformationTable = new Model_DbTable_LeagueInformation();
        $leagueLocationTable = new Model_DbTable_LeagueLocation();
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        
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
    
    public function isUnique($year, $season, $day, $name = null)
    {
        $select = $this->select()
                       ->where('year = ?', $year)
                       ->where('season = ?', strtolower($season))
                       ->where('day = ?', $day);
        
        if($name) {
            $select->where('name = ?', $name);
        } else {
            $select->where('name IS NULL');
        }
        
        $result = $this->fetchRow($select);
        
        if(isset($result->id)) {
            return false;
        }
        
        return true;
    }
    
    public function createBlankLeague($year, $season, $day, $name, $userId)
    {
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $leagueId = $this->insert(array(
            'year' => $year,
            'season' => $leagueSeasonTable->fetchId($season),
            'day' => $day,
            'name' => (empty($name)) ? null : $name,
            'info' => 'Enter a quick description of the league here or remove.',
            'registration_begin' => '2010-01-01 00:00:00',
            'registration_end' => '2010-01-01 00:00:00',
            'visible_from' => '2100-01-01 00:00:00',
            'is_archived' => 0,
        ));
        
        if(is_numeric($leagueId)) {
            $leagueInformationTable = new Model_DbTable_LeagueInformation();
            $leagueInformationTable->insert(array(
                'league_id' => $leagueId,
                'is_youth' => 0,
                'user_teams' => 0,
                'is_pods' => 0,
                'is_hat' => 0,
                'is_clinic' => 0,
                'contact_email' => null,
                'cost' => 0,
                'paypal_code' => null,
                'description' => 'Enter the description and any other information you want displayed on the webpage here.',
            ));
            
            $leagueLimitTable = new Model_DbTable_LeagueLimit();
            $leagueLimitTable->insert(array(
                'league_id' => $leagueId,
                'male_players' => null,
                'female_players' => null,
                'total_players' => 60,
                'teams' => 4,
            ));
            
            $leagueLocationTable = new Model_DbTable_LeagueLocation();
            $leagueLocationTable->insert(array(
                'league_id' => $leagueId,
                'type' => 'league',
                'location' => 'TBD', 
                'map_link' => 'http://cincyultimate.org',
                'photo_link' => null,
                'address_street' => 'TBD',
                'address_city' => 'Cincinnati',
                'address_state' => 'OH',
                'address_zip' => '45209',
                'start' => date('Y-m-d H:i:s'),
                'end' => date('Y-m-d H:i:s'),
            ));
            
            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $leagueMemberTable->insert(array(
                'league_id' => $leagueId,
                'user_id' => $userId,
                'position' => 'director',
                'league_team_id' => null,
                'paid' => 0,
                'release' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => date('Y-m-d H:i:s'),
                'modified_by' => $userId,
            ));
            
            $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
            $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
            foreach(array('new_player', 'pair', 'shirt', 'captain', 'comments') as $questionName) {
                $required = 1;
                if($questionName == 'pair' or $questionName == 'comments') {
                    $required = 0;
                }
                $question = $leagueQuestionTable->fetchQuestion($questionName);
                $leagueQuestionListTable->addQuestionToLeague($leagueId, $question->id, $required);
            }
        }
        
        return $leagueId;   
    }
}
