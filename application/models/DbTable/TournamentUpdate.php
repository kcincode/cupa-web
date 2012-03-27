<?php

class Model_DbTable_TournamentUpdate extends Zend_Db_Table
{
    protected $_name = 'tournament_update';
    protected $_primary = 'id';
 
    public function fetchUpdates($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('posted DESC');
        
        return $this->fetchAll($select);
    }
}
