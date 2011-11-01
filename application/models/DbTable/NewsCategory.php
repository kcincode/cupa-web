<?php

class Cupa_Model_DbTable_NewsCategory extends Zend_Db_Table
{
    protected $_name = 'news_category';
    protected $_primary = 'id';

    public function fetchCategoryIdFromName($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name);
        
        $result = $this->fetchRow($select);
        if(isset($result->id)) {
            return $result->id;
        }
        
        return false;
    }
}
