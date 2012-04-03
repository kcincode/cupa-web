<?php

class Model_DbTable_TournamentTeam extends Zend_Db_Table
{
    protected $_name = 'tournament_team';
    protected $_primary = 'id';

    public function isUnique($tournamentId, $teamName, $division)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->where('name = ?', $teamName)
                       ->where('division = ?', $division);

        $result = $this->fetchRow($select);
        if(!$result) {
            return true;
        }

        return false;
    }

    public function fetchAllTeams($tournamentId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('tt' => $this->_name), array('*'))
                       ->join(array('td' => 'tournament_division'), 'td.id = tt.division', array('name AS divisionName'))
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('division ASC')
                       ->order('name');

        $teams = array();
        foreach($this->getAdapter()->fetchAll($select) as $team) {
            $teams[$team['divisionName']][] = $team;
        }

        return $teams;
    }

    public function createTeam($tournamentId, $name, $division)
    {
        $team = $this->createRow();
        $team->tournament_id = $tournamentId;
        $team->name = $name;
        $team->city = 'Unknown';
        $team->state = 'NA';
        $team->contact_name = 'Unknown';
        $team->contact_email = str_replace(' ', '_', $name) . '@email.com';
        $team->contact_phone = '555-555-5555';
        $team->division = $division;
        $team->accepted = 0;
        $team->paid = 0;
        $team->save();

        return $team->id;
    }
}
