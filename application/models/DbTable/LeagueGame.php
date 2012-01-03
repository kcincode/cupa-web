<?php

class Cupa_Model_DbTable_LeagueGame extends Zend_Db_Table
{
    protected $_name = 'league_game';
    protected $_primary = 'id';
    
    public function fetchGame($date, $week, $field)
    {
        $select = $this->select()
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
}