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
    
    public function isUnique($tournamentId, $title)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->where('title = ?', $title);
        
        $result = $this->fetchRow($select);
        
        if(!$result) {
            return true;
        }
        
        return false;
    }
}
