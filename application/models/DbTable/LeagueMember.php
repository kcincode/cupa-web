<?php

class Cupa_Model_DbTable_LeagueMember extends Zend_Db_Table
{
    protected $_name = 'league_member';
    protected $_primary = 'id';

    public function fetchMember($leagueId, $userId, $teamId = null, $position = 'player')
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('user_id = ?', $userId)
                       ->where('position = ?', $position);
        
        if($teamId) {
            $select->where('team_id = ?', $teamId);
        }
        
        return $this->fetchRow($select);
    }
    
    public function fetchUniqueDirectors()
    {
        $data = array();
        $data['league'] = array();
        foreach(array('Winter', 'Spring', 'Summer', 'Fall') as $season) {
            $select = $this->getAdapter()->select()
                           ->from(array('l' => 'league', array('id')))
                           ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array())
                           ->joinLeft(array('lm' => 'league_member'), 'lm.league_id = l.id', array('user_id'))
                           ->where('li.is_clinic = ?', 0)
                           ->where('li.is_hat = ?', 0)
                           ->where('l.season = ?', $season)
                           ->where('lm.position = ?', 'director')
                           ->order('l.year DESC');

            $stmt = $this->getAdapter()->query($select);
            $prevYear = null;
            foreach($stmt->fetchAll() as $row) {
                if($prevYear == null or $prevYear <= $row['year']) {
                    $data['league'][$season][] = array(
                        'year' => $row['year'],
                        'user_id' => $row['user_id'],
                        'league_id' => $row['id'],
                    );
                    
                    $prevYear = $row['year'];
                }
            }
        }
        
        $data['youth'] = array();
        $data['clinic'] = array();
        $data['tournament'] = array();
        $data['other'] = array();
        
        return $data;
    }
    
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
    
    public function fetchAllPlayersByGender($leagueId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array('*'))
                       ->joinLeft(array('up' => 'user_profile'), 'up.user_id = lm.user_id', array('gender'))
                       ->where('lm.league_id = ?', $leagueId)
                       ->where('lm.position = ?', 'player');
        
        $stmt = $this->getAdapter()->query($select);
        $data = array('male_players' => 0, 'female_players' => 0);
        
        foreach($stmt->fetchAll() as $row) {
            if($row['gender'] == 'Male') {
                $data['male_players']++;
            } else {
                $data['female_players']++;
            }
        }
        
        return $data;
    }
    
    public function fetchAllPlayerData($leagueId, $teamId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array())
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array('*'))
                       ->joinLeft(array('up' =>'user_profile'), 'up.user_id = lm.user_id', array('*'))
                       ->where('league_id = ?', $leagueId)
                       ->where('league_team_id = ?', $teamId)
                       ->where('position = ?', 'player')
                       ->order('u.last_name')
                       ->order('u.first_name');

        return $this->getAdapter()->fetchAll($select);        
    }
    
}