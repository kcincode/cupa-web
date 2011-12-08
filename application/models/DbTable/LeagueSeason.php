<?php

class Cupa_Model_DbTable_LeagueSeason extends Zend_Db_Table
{
    protected $_name = 'league_season';
    protected $_primary = 'id';
    
    public function fetchAllSeasons()
    {
        $select = $this->select()
                       ->order('weight ASC');
        
        return $this->fetchAll($select);
    }
    
    public function generateLinks()
    {
        $data = array();
        foreach($this->fetchAllSeasons() as $row) {
            $data[] = array(
                'name' => $row['name'],
                'title' => ucwords($row['name']) . ' Leagues',
                'link' => '/leagues/' . $row['name'],
                );
        }
        
        return $data;
    }
    
}