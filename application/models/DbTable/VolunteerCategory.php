<?php

class Model_DbTable_VolunteerCategory extends Zend_Db_Table
{
    protected $_name = 'volunteer_category';
    protected $_primary = 'id';

    public function fetchAllCategories()
    {
        $select = $this->select()
                       ->order('category');

        return $this->fetchAll($select);
    }
}
