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

    public function createTeam($tournamentId, $data)
    {
        $team = $this->createRow();
        $team->tournament_id = $tournamentId;
        $team->name = $data['name'];
        $team->city = $data['city'];
        $team->state = $data['state'];
        $team->contact_name = $data['contact_name'];
        $team->contact_email = $data['contact_email'];
        $team->contact_phone = $data['contact_phone'];
        $team->division = $data['division'];
        $team->accepted = $data['accepted'];
        $team->paid = $data['paid'];
        $team->save();

        return $team->id;
    }

    public function updateValues($teamId, $data)
    {
        $team = $this->find($teamId)->current();
        foreach($data as $key => $value) {
            $team->$key = $value;
        }
        $team->save();
    }
}
