<?php

class Model_DbTable_Minute extends Zend_Db_Table
{
    protected $_name = 'minute';
    protected $_primary = 'id';
    
    public function fetchAllMinutes()
    {
        $select = $this->select()
                       ->order('when');

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
    
}
