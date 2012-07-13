<?php

class Model_DbTable_ClubMember extends Zend_Db_Table
{
    protected $_name = 'club_member';
    protected $_primary = 'id';
    
    public function isUnique($clubId, $userId, $year, $position = 'player')
    {
        $select = $this->select()
                       ->where('club_id = ?', $clubId)
                       ->where('user_id = ?', $userId)
                       ->where('year = ?', $year)
                       ->where('position = ?', $position);

        $result = $this->fetchRow($select);
        return (empty($result)) ? true : false;
    }

    public function addMember($clubId, $userId, $year, $position = 'player')
    {
        if($this->isUnique($clubId, $userId, $year, $position)) {
            $this->insert(array(
                'club_id' => $clubId,
                'user_id' => $userId,
                'year' => $year,
                'position' => $position,
            ));
        }
    }

    public function fetchMembers($clubId, $year)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('cm' => $this->_name), array('year'))
                       ->join(array('c' => 'club'), 'c.id = cm.club_id', array('name'))
                       ->join(array('u' => 'user'), 'u.id = cm.user_id', array('id AS user_id', "CONCAT(first_name, ' ', last_name) AS member"))
                       ->where('club_id = ?', $clubId)
                       ->where('year = ?', $year);

        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchUserClubs($userId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('cm' => $this->_name), array('club_id', 'year'))
                       ->join(array('c' => 'club'), 'c.id = cm.club_id', array('name', 'type'))
                       ->join(array('u' => 'user'), 'u.id = cm.user_id', array())
                       ->where('user_id = ?', $userId);

        return $this->getAdapter()->fetchAll($select);        
    }
}
