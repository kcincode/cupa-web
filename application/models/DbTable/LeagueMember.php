<?php

class Model_DbTable_LeagueMember extends Zend_Db_Table
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
        $select = $this->getAdapter()->select()
                       ->from(array('l' => 'league', array('id', 'year', 'day', 'name', 'season')))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array())
                       ->joinLeft(array('lm' => 'league_member'), 'lm.league_id = l.id', array('user_id'))
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array())
                       ->where('lm.position = ?', 'director')
                       ->where('l.is_archived = ?', 0)
                       ->order('u.last_name')
                       ->order('u.first_name')
                       ->order('l.year DESC');
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $league = $row['year'] . ' ' . $row['day'] . ' ' . $row['name'];
            if(!empty($row['season'])) {
                $league .= ' League';
            }
            $data['league'][$row['user_id']][] = array(
                'name' => $league,
                'link' => '/league/' . $row['id'],
            );
        }

        $select = $this->getAdapter()->select()
                       ->from(array('t' => 'tournament', array('id', 'year', 'display_name')))
                       ->joinLeft(array('tm' => 'tournament_member'), 'tm.tournament_id = t.id', array('user_id', 'name AS overName'))
                       ->joinLeft(array('u' => 'user'), 'u.id = tm.user_id', array())
                       ->where('tm.type = ?', 'director')
                       ->where('t.is_visible = ?', 1)
                       ->order('t.year DESC');

        foreach($this->getAdapter()->fetchAll($select) as $row) {
            if(!empty($row['user_id'])) {
                $data['tournament'][$row['user_id']][] = array(
                    'name' => $row['year'] . ' ' . $row['display_name'],
                    'link' => '/tournament/' . $row['name'] . '/' . $row['year'],
                );
            }
        }

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
        $data = array('male_players' => 0, 'female_players' => 0, 'unknown' => 0);

        foreach($stmt->fetchAll() as $row) {
            if($row['gender'] == 'Male') {
                $data['male_players']++;
            } else if($row['gender'] == 'Female') {
                $data['female_players']++;
            } else {
                $data['unknown']++;
            }
        }

        return $data;
    }

    public function fetchAllPlayerData($leagueId, $teamId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array())
                       ->joinLeft(array('l' => 'league'), 'lm.league_id = l.id', array())
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array('*'))
                       ->joinLeft(array('up' => 'user_profile'), 'up.user_id = lm.user_id', array('*'))
                       ->joinLeft(array('cm' => 'club_member'), 'cm.user_id = u.id AND l.year <= cm.year', array('club_id'))
                       ->joinLeft(array('la' => 'league_answer'), 'la.league_member_id = lm.id && la.league_question_id = 32', array('answer AS club'))
                       ->where('lm.league_id = ?', $leagueId)
                       ->where('league_team_id = ?', $teamId)
                       ->where('lm.position = ?', 'player')
                       ->order('u.last_name')
                       ->order('u.first_name');

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchUserLeagues($userId, $leaguesOnly = true)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array('paid', 'release', 'position'))
                       ->joinLeft(array('l' => 'league'), 'l.id = lm.league_id', array('id AS league_id', 'year'))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array('cost'))
                       ->joinLeft(array('lt' => 'league_team'), 'lt.id = lm.league_team_id', array('id AS team_id', 'name AS team'))
                       //->where('lm.league_team_id IS NOT NULL')
                       ->where('lm.user_id = ?', $userId)
                       ->where("lm.position = 'player' OR lm.position = 'waitlist'")
                       ->where('l.id NOT IN (43,44)') // remove steamboat leagues since clubs does it now
                       ->order('l.registration_end DESC');

        if($leaguesOnly) {
            $select->where('l.season IS NOT NULL');
        }

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchAllEmails($leagueId, $user, $isDirector)
    {
        $data = array();

        $leagueInformationTable = new Model_DbTable_LeagueInformation();
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $leagueInfo = $leagueInformationTable->fetchInformation($leagueId);
        if(empty($leagueInfo->contact_email)) {
            $data['all-directors'] = $this->fetchMemberEmails($leagueId, 'director');
        } else {
            $data['all-directors'] = array($leagueInfo->contact_email);
        }

        if($user) {
            if($isDirector) {
                $data['all-players'] = $this->fetchMemberEmails($leagueId, 'player');
                $data['all-captains'] = $this->fetchMemberEmails($leagueId, 'captain');

                $data['unpaid-players'] = array();
                foreach($this->fetchUnpaidPlayers($leagueId) as $row) {
                    $email = (empty($row['email'])) ? $row['parent'] : $row['email'];
                    $data['unpaid-players'][$email] = $email;
                }

                foreach($leagueTeamTable->fetchAllTeams($leagueId) as $team) {
                    $key = str_replace(' ', '-', strtolower($team->name));
                    $data[$key] = $this->fetchMemberEmails($leagueId, 'player', $team->id);
                }
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
                       ->where('lm.position = ?', $type)
                       ->where('u.email IS NOT NULL');

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
                AND uw.year <= $year ORDER BY year DESC LIMIT 1) AS waiver, lm.release, lm.paid,
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

    public function fetchPlayerInformation($leagueId, $status = 'player')
    {
        $sql = "SELECT lm.id, lm.user_id, lm.created_at, u.first_name, u.last_name, u.email, up.gender, up.birthday, up.phone, up.nickname, up.height, ul.name AS user_level, lt.name AS team, up.experience, lq.name, la.answer
FROM league_member lm
LEFT JOIN user u ON u.id = lm.user_id
LEFT JOIN league_question_list lql ON lql.league_id = lm.league_id
LEFT JOIN league_question lq ON lq.id = lql.league_question_id
LEFT JOIN league_answer la ON la.league_member_id = lm.id AND la.league_question_id = lq.id
LEFT JOIN league_team lt ON lt.id = lm.league_team_id
LEFT JOIN user_profile up ON up.user_id = lm.user_id
LEFT JOIN user_level ul ON ul.id = up.level
WHERE lm.league_id = ? AND
lm.position = ?";
        if($status == 'player') {
            $sql .= " ORDER BY u.last_name, u.first_name, lql.weight ASC";
        } else {
            $sql .= " ORDER BY lm.created_at, u.last_name, u.first_name, lql.weight ASC";
        }


        $stmt = $this->getAdapter()->prepare($sql);
        $stmt->execute(array($leagueId, $status));

        $data = array();
        foreach($stmt->fetchAll() as $row) {
            if(isset($data[$row['user_id']])) {
                $data[$row['user_id']]['answers'][$row['name']] = $row['answer'];
            } else {
                $data[$row['user_id']] = array(
                    'id' => $row['id'],
                    'created_at' => $row['created_at'],
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'team' => $row['team'],
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

    public function fetchAllEmergencyContacts($leagueId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array())
                       ->joinLeft(array('ue' => 'user_emergency'), 'ue.user_id = lm.user_id')
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array())
                       ->where('lm.position = ?', 'player')
                       ->where('lm.league_id = ?', $leagueId)
                       ->order('u.last_name')
                       ->order('u.first_name')
                       ->order('ue.weight ASC');

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $data[$row['user_id']][] = array(
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'phone' => $row['phone'],
            );
        }

        return $data;
    }

    public function fetchUserRegistrants($leagueId, $userIds)
    {
        $select = $this->select()
                       ->where('position = ?', 'player')
                       ->where('league_id = ?', $leagueId);

        if(is_array($userIds)) {
            $select->where('user_id IN (' . implode(',', $userIds) . ')');
        } else if(is_numeric($userIds)) {
            $select->where('user_id = ?', $userIds);
        }

        if(is_array($userIds)) {
            return $this->fetchAll($select);
        } else if(is_numeric($userIds)) {
            return $this->fetchRow($select);
        }
    }

    public function fetchUserWaitlists($leagueId, $userIds)
    {
        $select = $this->select()
                       ->where('position = ?', 'waitlist')
                       ->where('league_id = ?', $leagueId);

        if(is_array($userIds)) {
            $select->where('user_id IN (' . implode(',', $userIds) . ')');
        } else if(is_numeric($userIds)) {
            $select->where('user_id = ?', $userIds);
        }

        if(is_array($userIds)) {
            return $this->fetchAll($select);
        } else if(is_numeric($userIds)) {
            return $this->fetchRow($select);
        }
    }

    public function fetchPlayersByTeam($leagueId, $teamId = null)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array('id', 'user_id'))
                       ->joinLeft(array('lt' => 'league_team'), 'lt.id = lm.league_team_id', array('id AS team_id', 'name AS team', 'color_code', 'text_code'))
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array('first_name', 'last_name'))
                       ->where('lm.position = ?', 'player')
                       ->where('lm.league_id = ?', $leagueId)
                       ->order('lt.name')
                       ->order('u.last_name')
                       ->order('u.first_name');

        if($teamId === 0) {
            return array();
        } else if(empty($teamId)) {
            $select->where('lm.league_team_id IS NULL');
        } else {
            $select->where('lm.league_team_id = ?', $teamId);
        }

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchPlayersByLeague($leagueId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array('id', 'user_id'))
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array('first_name', 'last_name'))
                       ->where('lm.position = ?', 'player')
                       ->where('lm.league_id = ?', $leagueId)
                       ->order('u.last_name')
                       ->order('u.first_name');

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchUnpaidPlayers($leagueId = null, $noDate = null)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lm' => $this->_name), array('id', 'user_id'))
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array('first_name', 'last_name', 'email'))
                       ->joinLeft(array('up' => 'user'), 'up.id = u.parent', array('email AS parent'))
                       ->joinLeft(array('l' => 'league'), 'l.id = lm.league_id', array('l.id AS league'))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array('cost'))
                       ->joinLeft(array('ll' => 'league_location'), 'll.league_id = l.id', array())
                       ->where('lm.position = ?', 'player')
                       ->where('lm.paid = ?', 0)
                       ->where('l.year >= ?', 2011)
                       ->where('ll.type = ?', 'league')
                       ->where('cost IS NOT NULL AND cost > ?', 0)
                       ->order('u.last_name')
                       ->order('u.first_name');

        if($leagueId !== null) {
            $select = $select->where('lm.league_id = ?', $leagueId);
        }

        return $this->getAdapter()->fetchAll($select);
    }

    public function addNewPlayer($leagueId, $playerId)
    {
        $player = $this->fetchMember($leagueId, $playerId);

        if(!$player) {
            // add to league member table
            $this->insert(array(
                'league_id' => $leagueId,
                'user_id' => $playerId,
                'position' => 'player',
                'league_team_id' => null,
                'paid' => 0,
                'release' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => date('Y-m-d H:i:s'),
                'modified_by' => Zend_Auth::getInstance()->getIdentity(),
            ));
        } else {
            return 'duplicate';
        }
    }

    public function removePlayer($memberId)
    {
        $player = $this->find($memberId)->current();
        if($player) {
            $player->delete();
        }
    }

    public function isALeagueDirector($userId)
    {
        $select = $this->select()->where('user_id = ?', $userId)->where('position = ?', 'director');

        return (count($this->fetchAll($select)) > 0) ? true : false;
    }
}
