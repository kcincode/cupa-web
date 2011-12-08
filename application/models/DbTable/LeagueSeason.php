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
   
}