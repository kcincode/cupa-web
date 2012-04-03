<?php

class Model_DbTable_TournamentMember extends Zend_Db_Table
{
    protected $_name = 'tournament_member';
    protected $_primary = 'id';

    public function fetchAllMembers($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('weight ASC');

        return $this->fetchAll($select);
    }

}
