<?php

class Cupa_Model_DbTable_LeagueInformation extends Zend_Db_Table
{
    protected $_name = 'league_information';
    protected $_primary = 'id';
    
    public function fetchInformation($leagueId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId);
        
        return $this->fetchRow($select);
    }
}