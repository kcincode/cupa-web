<?php

class Cupa_Model_DbTable_LeagueQuestion extends Zend_Db_Table
{
    protected $_name = 'league_question';
    protected $_primary = 'id';
    
    public function fetchQuestion($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name);
        
        return $this->fetchRow($select);
    }
}