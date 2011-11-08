<?php

class Cupa_Model_DbTable_News extends Zend_Db_Table
{
    protected $_name = 'news';
    protected $_primary = 'id';

    public function slugifyTitle($title)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($title, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
    
    public function getNewsType($news)
    {
        if(!empty($news['content'])) {
            return 'news';
        } else if(strpos($news['url'], 'http') !== false) {
            return 'external';
        } else if(strpos($news['url'], 'http') === false and !empty($news['url'])) {
            return 'internal';
        } else {
            return 'text';
        }
    }
    
    public function fetchAllNews($ignoreVisibility = false)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('n' => 'news'), array('*'))
                       ->joinLeft(array('nc' => 'news_category'), 'nc.id = n.category_id', array('name AS category'));
        
        if(!$ignoreVisibility) {
            $select->where('n.is_visible = ?', 1);
        }
        
        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll();
    }
    
    public function fetchNewsByCategory($category)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('n' => 'news'), array('*'))
                       ->joinLeft(array('nc' => 'news_category'), 'nc.id = n.category_id', array('name AS category'));
                       
        
        if($category != 'all') {
            $select->where('nc.name = ?', $category);
        }
        
        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll();
    }
    
    public function fetchNewsBySlug($slug)
    {
        $select = $this->select() 
                       ->where('slug = ?', $slug);
        
        return $this->fetchRow($select);
    }

    public function isUnique($title)
    {
        $slug = $this->slugifyTitle($title);
        $result = $this->fetchNewsBySlug($slug);
        
        if(isset($result->id)) {
            return false;
        }
        
        return true;
    }
}
