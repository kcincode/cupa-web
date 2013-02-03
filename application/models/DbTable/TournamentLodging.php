<?php

class Model_DbTable_TournamentLodging extends Zend_Db_Table
{
    protected $_name = 'tournament_lodging';
    protected $_primary = 'id';

    public function fetchAllLodgings($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('title');

        return $this->fetchAll($select);
    }

}
