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
    
}