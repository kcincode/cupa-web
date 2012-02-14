<?php

class Model_DbTable_LeagueLimit extends Zend_Db_Table
{
    protected $_name = 'league_limit';
    protected $_primary = 'id';
    
    public function fetchLimits($leagueId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId);
        
        return $this->fetchRow($select);
    }
}
