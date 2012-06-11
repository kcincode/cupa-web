<?php

class Model_DbTable_Page extends Zend_Db_Table
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
    
    public function fetchAllPages()
    {
        $select = $this->select()
                       ->order('name');
        
        return $this->fetchAll($select);
    }
    
    public function createPage($name)
    {
        $this->insert(array(
            'parent' => null,
            'name' => $name,
            'title' => $name,
            'content' => '',
            'url' => null,
            'target' => 'self',
            'weight' => 0,
            'is_visible' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Zend_Auth::getInstance()->getIdentity(),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_updated_by' => Zend_Auth::getInstance()->getIdentity(),
        ));
    }
}
