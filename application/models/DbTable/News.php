<?php

class Cupa_Model_DbTable_News extends Zend_Db_Table
{
    protected $_name = 'news';
    protected $_primary = 'id';

    public function slugifyTitle($title)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($title, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
    
    public function fetchAllNews()
    {
        $select = $this->getAdapter()->select()
                       ->from(array('n' => 'news'), array('*'))
                       ->joinLeft(array('nc' => 'news_category'), 'nc.id = n.category_id', array('name AS category'))
                       ->where('n.is_visible = ?', 1);
        
        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll();
    }
}
