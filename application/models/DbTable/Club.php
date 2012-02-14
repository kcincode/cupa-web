<?php

class Model_DbTable_Club extends Zend_Db_Table
{
    protected $_name = 'club';
    protected $_primary = 'id';
    
    public function fetchAllByType($type, $order = 'name')
    {
        $select = $this->select()
                       ->order($order);
        
        if($type == 'past') {
            $select->where('end IS NOT NULL');
        }
        
        if($type == 'current') {
            $select->where('end IS NULL');
        }
        
        return $this->fetchAll($select);
    }
    
    public function fetchByName($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name);
        
        return $this->fetchRow($select);
    }
    
    public function isUnique($name)
    {
        $result = $this->fetchByName($name);
        
        if(isset($result->id)) {
            return false;
        }
        
        return true;
    }
}
