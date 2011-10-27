<?php

class Cupa_Model_DbTable_UserLevel extends Zend_Db_Table
{
    protected $_name = 'user_level';
    protected $_primary = 'id';

    public function fetchAllByWeight()
    {
        $select = $this->select()
                       ->order('weight ASC');
        
        return $this->fetchAll($select);
    }
}