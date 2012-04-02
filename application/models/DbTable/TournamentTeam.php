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
}
