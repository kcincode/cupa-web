<?php

class Cupa_Model_DbTable_Officer extends Zend_Db_Table
{
    protected $_name = 'officer';
    protected $_primary = 'id';
    
    public function fetchAllOfficers($order = 'weight ASC')
    {
        $select = $this->select()
                       ->order($order);
        
        $officers = array();
        foreach($this->fetchAll($select) as $officer) {
            if($officer->position != 'At Large Board Member') {
                $type = 'current';
            } else {
                $type = 'atLarge';
            }
            
            if($officer->to !== null) {
                $type = 'past';
            }
            
            $officers[$type][] = $officer->toArray();
        }
        
        
        return $officers;
    }
}
