<?php

class Cupa_Model_DbTable_Pickup extends Zend_Db_Table
{
    protected $_name = 'pickup';
    protected $_primary = 'id';
    
    
    public function fetchAllPickups()
    {
        $select = $this->select()
                       ->order('title');
        
        $data = array();
        foreach($this->fetchAll($select) as $row) {
            if($row->is_visible) {
                $data['visible'][] = $row->toArray();
            } else {
                $data['hidden'][] = $row->toArray();
            }
        }
                
        return $data;
    }
    
    public function isUnique($title)
    {
        if(empty($title)) {
            return false;
        }
        
        $select = $this->select()
                       ->where('title = ?', $title);
        
        $result = $this->fetchRow($select);
        if(isset($result->id)) {
            return false;
        }
        
        return true;
    }
}