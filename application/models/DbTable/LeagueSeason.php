<?php

class Cupa_Model_DbTable_LeagueSeason extends Zend_Db_Table
{
    protected $_name = 'league_season';
    protected $_primary = 'id';
    
    public function fetchAllSeasons($order = 'weight ASC')
    {
        $select = $this->select()
                       ->order($order);
        
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
    
    public function moveSeason($seasonId, $weight)
    {
        $activeSeason = $this->find($seasonId)->current();
        $prevWeight = $activeSeason->weight;
        $activeSeason->weight = $weight;
        $activeSeason->save();
        
        foreach($this->fetchAllSeasons() as $row) {
            if($row->id != $seasonId and $row->weight == $weight) {
                $row->weight = $prevWeight;
                $row->save();
            }
        }      
    }
    
    public function fetchNextWeight()
    {
        $select = $this->select()
                       ->order('weight DESC');
        
        $result = $this->fetchRow($select);
        
        return $result->weight + 1;
    }
    
    public function isUnique($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name);
        
        $result = $this->fetchRow($select);
        
        if(isset($result->id)) {
            return false;
        }
        
        return true;
    }

    public function fetchName($seasonId)
    {
        $result = $this->find($seasonId)->current();
        return (isset($result->name)) ? $result->name : null;
    }
    
    public function fetchId($seasonName)
    {
        $select = $this->select()
                       ->where('name = ?', $seasonName);
        
        $result = $this->fetchRow($select);
        return (isset($result->id)) ? $result->id : null;
    }
}