<?php

class Cupa_Model_DbTable_LeagueGame extends Zend_Db_Table
{
    protected $_name = 'league_game';
    protected $_primary = 'id';
    
    public function fetchGame($leagueId, $date, $week, $field)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('day = ?', $date)
                       ->where('week = ?', $week)
                       ->where('field = ?', $field);
        
        return $this->fetchRow($select);
    }

    public function fetchRecord($leagueId, $teamId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lg' => $this->_name), array())
                       ->join(array('lgdteam' => 'league_game_data'), 'lgdteam.league_game_id = lg.id', array('score AS team'))
                       ->join(array('lgdopponent' => 'league_game_data'), 'lgdopponent.league_game_id = lg.id', array('score AS opponent'))
                       ->where('lg.league_id = ?', $leagueId)
                       ->where('lgdteam.league_team_id = ?', $teamId)
                       ->where('lgdopponent.league_team_id <> ?', $teamId)
                       ->where('lgdteam.score > 0 OR lgdopponent.score > 0');

        $win = $loss = $tie = 0;
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            if($row['team'] == $row['opponent']) {
                $tie++;
            } else {
                ($row['team'] > $row['opponent']) ? $win++ : $loss++;
            }
        }

        return $win . '-' . $loss . '-' . $tie;
    }

    public function fetchSchedule($leagueId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('lg' => $this->_name), array('*'))
                       ->join(array('lgdaway' => 'league_game_data'), 'lgdaway.league_game_id = lg.id', array('league_team_id AS away_team_id', 'score AS away_score'))
                       ->join(array('lgdhome' => 'league_game_data'), 'lgdhome.league_game_id = lg.id', array('league_team_id AS home_team_id', 'score AS home_score'))
                       ->join(array('ltaway' => 'league_team'), 'ltaway.id = lgdaway.league_team_id', array('name AS away_team'))
                       ->join(array('lthome' => 'league_team'), 'lthome.id = lgdhome.league_team_id', array('name AS home_team'))
                       ->where('lg.league_id = ?', $leagueId)
                       ->where('lgdaway.type = ?', 'away')
                       ->where('lgdhome.type = ?', 'home')
                       ->order('lg.week ASC')
                       ->order('lg.day ASC')
                       ->order('lg.field ASC');

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $data[$row['week']][$row['field']] = $row;
        }

        return $data;
    }
    
    public function createGame($leagueId, $date, $week, $field)
    {
        $result = $this->fetchGame($leagueId, $date, $week, $field);
        
        if(!$result) {
            return $this->insert(array(
                'league_id' => $leagueId,
                'day' => $date,
                'week' => $week,
                'field' => $field,
            ));
        } else {
            return $result->id;
        }
    }

}
