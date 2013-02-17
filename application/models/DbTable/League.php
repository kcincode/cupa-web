<?php

class Model_DbTable_League extends Zend_Db_Table
{
    protected $_name = 'league';
    protected $_primary = 'id';


    public function fetchCurrentLeaguesBySeason($season, $all = false, $showArchived = false)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('l' => $this->_name), array('*'))
                       ->joinLeft(array('ls' => 'league_season'), 'ls.id = l.season', array('name AS season'))
                       ->joinLeft(array('li' => 'league_information'), 'li.league_id = l.id', array())
                       ->where('ls.name = ?', $season)
                       ->where('li.is_youth = ?', 0)
                       ->order('l.is_archived ASC')
                       ->order('l.year DESC')
                       ->order('l.registration_end');

        if(!$all) {
            $select->where('l.visible_from <= ?', date('Y-m-d H:i:s'));
        }

        if(!$showArchived) {
            $select = $select->where('is_archived = ?', 0);
        }

        $results = $this->getAdapter()->fetchAll($select);

        $data = array();
        foreach($results as $row) {
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

    public function createLeague($data)
    {
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $leagueId = $this->insert(array(
            'year' => $data['year'],
            'season' => $data['season'],
            'day' => $data['day'],
            'name' => (empty($data['name'])) ? null : $data['name'],
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
            foreach($data['directors'] as $director) {
                $leagueMemberTable->insert(array(
                    'league_id' => $leagueId,
                    'user_id' => $director,
                    'position' => 'director',
                    'league_team_id' => null,
                    'paid' => 0,
                    'release' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_at' => date('Y-m-d H:i:s'),
                    'modified_by' => Zend_Auth::getInstance()->getIdentity()->id,
                ));
            }

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

    public function fetchAllCurrentLeagues()
    {
        $select = $this->select()
                       ->where('is_archived = ?', 0)
                       ->where('season IS NOT NULL')
                       ->order('year DESC')
                       ->order('season ASC')
                       ->order('name');

        return $this->fetchAll($select);
    }

    public function fetchMostCurrentYear($seasonId)
    {
        if(empty($seasonId)) {
            return null;
        }

        $select = $this->select()
                       ->where('season = ?', $seasonId)
                       ->order('year DESC');

        $result = $this->fetchRow($select);
        if($result) {
            return $result->year;
        }

        return null;
    }

    public function fetchAllLeaguesWithDirectors()
    {
        $select = $this->getAdapter()->select()
                       ->from(array('l' => $this->_name), array('day', 'l.name'))
                       ->joinLeft(array('ls' => 'league_season'), 'l.season = ls.id', array('ls.name AS season'))
                       ->joinLeft(array('lm' => 'league_member'), 'lm.league_id = l.id', array())
                       ->joinLeft(array('u' => 'user'), 'u.id = lm.user_id', array("CONCAT(u.first_name, ' ', u.last_name) AS director", 'email'))
                       ->where('is_archived = ?', 0)
                       ->where('lm.position = ?', 'director')
                       ->where('l.year >= ?', date('Y') - 1)
                       ->order('l.year DESC');

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            if(!$row['season']) {
                continue;
            }

            $key = str_replace('  ', ' ', $row['day'] . ' ' . $row['name'] . ' ' . ucfirst($row['season']));
            if(empty($data[$key])) {
                $data[$key] = array(
                    'name' => $key,
                    'directors' => array(
                        $row['director'] => $row['email'],
                    ),
                );
            } else {
                $data[$key]['directors'][$row['director']] = $row['email'];
            }
        }

        return $data;
    }
}
