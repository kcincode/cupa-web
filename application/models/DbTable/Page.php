<?php

class Cupa_Model_DbTable_Page extends Zend_Db_Table
{
    protected $_name = 'page';
    protected $_primary = 'id';

    public function fetchBy($column, $value)
    {
        if($column == 'id') {
            return $this->find($value)->current();
        } else {
            $select = $this->select()
                           ->where($column . ' = ?', $value);
            
            return $this->fetchRow($select);
        }
    }
    
    public function fetchChildren($page)
    {
        if(empty($page->parent)) {
            $select = $this->select()
                           ->where('parent = ?', $page->id)
                           ->where('is_visible = ?', 1)
                           ->order('weight ASC');
            return $this->fetchAll($select);
        } else {
            return $this->fetchChildren($this->find($page->parent)->current());
        }
    }
    
    public function fetchAllParentPages($showHidden = false)
    {
        $select = $this->select()
                       ->where('parent IS NULL')
                       ->order('name');
        
        if(!$showHidden) {
            $select->where('is_visible = ?', 1);
        }
        
        return $this->fetchAll($select);
    }
}