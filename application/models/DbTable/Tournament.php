<?php

class Model_DbTable_Tournament extends Zend_Db_Table
{
    protected $_name = 'tournament';
    protected $_primary = 'id';
 
    public function isUnique($year, $name)
    {
        $select = $this->select()
                       ->where('year = ?', $year)
                       ->where('name = ?', $name);
        
        $result = $this->fetchRow($select);
        if(!$result) {
            return true;
        }
        
        return false;
    }
    
    public function createBlankTournament($year, $name)
    {
        $tournament = $this->createRow();
        $tournament->name = $name;
        $tournament->year = $year;
        $tournament->display_name = $name;
        $tournament->save();
        
        $tournamentInfoTable = new Model_DbTable_TournamentInformation();
        $tournamentInfo = $tournamentInfoTable->createRow();
        $tournamentInfo->tournament_id = $tournament->id;
        $tournamentInfo->start = date('Y-m-d');
        $tournamentInfo->end = date('Y-m-d');
        $tournamentInfo->bid_due = date('Y-m-d');
        $tournamentInfo->cost = 100;
        $tournamentInfo->description = 'Enter the tournament description here.';
        $tournamentInfo->schedule_text = 'Enter any schedule information here.';
        $tournamentInfo->location = 'Enter the name of the field location';
        $tournamentInfo->location_map = 'Create a google map and copy the link here';
        $tournamentInfo->location_street = '9999 street name';
        $tournamentInfo->location_city = 'Cincinnati';
        $tournamentInfo->location_state = 'OH';
        $tournamentInfo->location_zip = '45209';
        $tournamentInfo->save();
        
        return $tournament;
        
    }
    
    public function fetchMostCurrentYear($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name)
                       ->order('year DESC');
        
        $result = $this->fetchRow($select);
        if($result) {
            return $result->year;
        }
        
        return null;
    }
    
    public function fetchTournament($year, $name)
    {
        if(empty($year)) {
            return null;
        }
        
        $select = $this->select()
                       ->where('year = ?', $year)
                       ->where('name = ?', $name);
        
        return $this->fetchRow($select);
    }
}
