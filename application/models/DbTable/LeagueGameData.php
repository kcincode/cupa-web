<?php

class Cupa_Model_DbTable_LeagueGameData extends Zend_Db_Table
{
    protected $_name = 'league_game_data';
    protected $_primary = 'id';

    public function fetchOutcome($gameId, $teamId)
    {
        $select = $this->getAdapter()->select()
            ->from(array('lg' => $this->_name), array())
            ->join(array('lgdteam' => 'league_game_data'), 'lgdteam.league_game_id = lg.id', array('score AS team'))
            ->join(array('lgdopponent' => 'league_game_data'), 'lgdopponent.league_game_id = lg.id', array('score AS opponent'))
            ->where('lg.id = ?', $gameId)
            ->where('lgdteam.league_team_id = ?', $teamId)
            ->where('lgdopponent.league_team_id <> ?', $teamId)
            ->where('lgdteam.score > 0 OR lgdopponent.score > 0');

        $result = $this->getAdapter()->fetchRow($select);

        if($result['team'] == $result['opponent']) {
            return 'tie';
        }

        return ($result['team'] > $result['opponent']) ? 'win' : 'loss';
    }

    public function fetchGameData($gameId, $type = null)
    {
        $select = $this->select()
                       ->where('league_game_id = ?', $gameId)
                       ->order('type');

        if(!empty($type)) {
            $select->where('type = ?', $type);
            return $this->fetchRow($select);
        }

        return $this->fetchAll($select);
    }



}
