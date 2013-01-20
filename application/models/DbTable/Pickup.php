<?php

class Model_DbTable_Pickup extends Zend_Db_Table
{
    protected $_name = 'pickup';
    protected $_primary = 'id';


    public function fetchAllPickups()
    {
        $select = $this->select()
                       ->order('weight ASC')
                       ->order('title');

        return $this->fetchAll($select);
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

    public function fetchHighestWeight()
    {
        $select = $this->select()
                       ->order('weight DESC');

        $result = $this->fetchRow($select);

        if(isset($result->weight)) {
            return $result->weight + 1;
        } else {
            return 0;
        }
    }
}
