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

    public function getUserLeagues($userId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array())
                       ->join(array('l' => 'league'), 'l.id = lm.league_id', array('id AS league_id', 'year'))
                       ->joinLeft(array('lt' => 'league_team'), 'lt.id = lm.league_team_id', array('id AS team_id', 'name AS team'))
                       ->where('l.season IS NOT NULL')
                       ->where('lm.league_team_id IS NOT NULL')
                       ->where('lm.user_id = ?', $userId)
                       ->where('lm.position = ?', 'player')
                       ->order('l.registration_end DESC');

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchAllEmails($leagueId, $user, $isDirector)
    {
        $data = array();

        $data['all-directors'] = $this->fetchMemberEmails($leagueId, 'director');

        if($user) {
            if($isDirector) {
                $data['all-players'] = $this->fetchMemberEmails($leagueId, 'player');
                $data['all-captains'] = $this->fetchMemberEmails($leagueId, 'captain');
            }


            $teamId = $this->fetchLeagueTeamFromUser($leagueId, $user);
            if(is_numeric($teamId)) {
                $data['my-captain'] = $this->fetchMemberEmails($leagueId, 'captain', $teamId);
                $data['my-team'] = $this->fetchMemberEmails($leagueId, 'player', $teamId);
            }
        }
        
        return $data;
    }
    
    public function fetchMemberEmails($leagueId, $type, $teamId = null)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array())
                       ->join(array('u' => 'user'), 'u.id = lm.user_id', array('email'))
                       ->where('lm.league_id = ?', $leagueId)
                       ->where('lm.position = ?', $type);
        
        if($teamId) {
            $select->where('lm.league_team_id = ?', $teamId);
        }
        
        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $email) {
            $data[] = $email['email'];   
        }
        
        return $data;
    }
    
    public function fetchLeagueTeamFromUser($leagueId, $user)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('user_id = ?', $user->id)
                       ->where("position = 'player' OR position = 'captain'");
        
        $result = $this->fetchRow($select);
        if(isset($result->league_team_id)) {
            return $result->league_team_id;
        }
        
        return null;
    }
    
    public function fetchPlayerStatuses($leagueId, $year)
    {
        $sql = "SELECT lm.user_id, 
                (SELECT uw.year FROM user_waiver uw WHERE uw.user_id = u.id 
                ORDER BY year DESC LIMIT 1) AS waiver, lm.release, lm.paid, 
                (SELECT SUM(li.cost)
                FROM league_member lm2
                LEFT JOIN user u2 ON u2.id = lm2.user_id
                LEFT JOIN league l ON l.id = lm2.league_id
                LEFT JOIN league_season ls ON ls.id = l.season
                LEFT JOIN league_location ll ON ll.league_id = lm2.league_id
                LEFT JOIN league_information li ON li.league_id = lm2.league_id
                WHERE (ll.type = 'league' AND ll.end < now()) 
                AND l.year >= 2011
                AND lm2.position = 'player' 
                AND lm2.paid = 0
                AND lm2.user_id = lm.user_id) AS balance
                FROM league_member lm 
                LEFT JOIN user u ON u.id = lm.user_id
                WHERE lm.position = 'player' AND lm.league_id = ?
                ORDER BY u.last_name, u.first_name";
        
        $stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute(array($leagueId));
        return $stmt->fetchAll();
    }
    
    public function fetchPlayerInformation($leagueId)
    {
        $sql = "SELECT lm.id, lm.user_id, u.first_name, u.last_name, u.email, up.gender, up.birthday, up.phone, up.nickname, up.height, ul.name AS user_level, up.experience, lq.name, la.answer
FROM league_member lm
LEFT JOIN user u ON u.id = lm.user_id
LEFT JOIN league_question_list lql ON lql.league_id = lm.league_id
LEFT JOIN league_question lq ON lq.id = lql.league_question_id
LEFT JOIN league_answer la ON la.league_member_id = lm.id AND la.league_question_id = lq.id
LEFT JOIN user_profile up ON up.user_id = lm.user_id
LEFT JOIN user_level ul ON ul.id = up.level
WHERE lm.league_id = ?
ORDER BY u.last_name, u.first_name, lql.weight ASC";
        
        $stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute(array($leagueId));  
        
        $data = array();
        foreach($stmt->fetchAll() as $row) {
            if(isset($data[$row['user_id']])) {
                $data[$row['user_id']]['answers'][$row['name']] = $row['answer'];
            } else {
                $data[$row['user_id']] = array(
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'profile' => array(
                        'gender' => $row['gender'],
                        'birthday' => $row['birthday'],
                        'phone' => $row['phone'],
                        'nickname' => $row['nickname'],
                        'height' => $row['height'],
                        'level' => $row['user_level'],
                        'experience' => $row['experience'],
                    ),
                    'answers' => array($row['name'] => $row['answer']),
                );
            }
        }
        
        return $data;
    }
}
