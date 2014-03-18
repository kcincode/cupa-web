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

    public function addMember($tournamentId, $userId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->where('user_id = ?', $userId);

        $result = $this->fetchRow($select);
        if(!$result) {
            $member = $this->createRow();
            $member->tournament_id = $tournamentId;
            $member->user_id = $userId;
            $member->type = 'member';
            $member->weight= $this->getHighestWeight($tournamentId);
            $member->save();

            return $member->id;
        } else {
            return $result->id;
        }
    }

    public function getHighestWeight($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('weight DESC');

        $result = $this->fetchRow($select);
        if(!$result) {
            return 1;
        } else {
            return $result->weight + 1;
        }
    }

    public function fetchAllDirectors($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       //->where('type = ?', 'director')
                       ->order('weight ASC');

        return $this->fetchAll($select);
    }
}
