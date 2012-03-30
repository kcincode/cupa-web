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

}
