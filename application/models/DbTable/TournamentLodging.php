<?php

class Model_DbTable_TournamentLodging extends Zend_Db_Table
{
    protected $_name = 'tournament_lodging';
    protected $_primary = 'id';

    public function fetchAllLodgings($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->order('title');

        return $this->fetchAll($select);
    }

    public function addNewLodging($tournamentId)
    {
        $select = $this->select()
                       ->where('tournament_id = ?', $tournamentId)
                       ->where('title = ?', 'New Lodging');

        $result = $this->fetchRow($select);

        if(!$result) {
            $result = $this->createRow(array(
                'tournament_id' => $tournamentId,
                'title' => 'New Lodging',
                'street' => '1234 Street',
                'city' => 'Cincinnati',
                'state' => 'OH',
                'zip' => '45209',
            ));

            $result->save();
        }

        return $result->id;
    }
}
