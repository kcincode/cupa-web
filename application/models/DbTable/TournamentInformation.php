<?php

class Model_DbTable_TournamentInformation extends Zend_Db_Table
{
    protected $_name = 'tournament_information';
    protected $_primary = 'tournament_id';
 
    public function fetchInfo($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId);
        
        return $this->fetchRow($select);
    }
}
