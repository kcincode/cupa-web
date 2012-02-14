<?php

class Model_DbTable_ClubCaptain extends Zend_Db_Table
{
    protected $_name = 'club_captain';
    protected $_primary = 'id';
 
    public function fetchAllByClub($clubId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('cc' => 'club_captain'), array())
                       ->joinLeft(array('u' => 'user'), 'u.id = cc.user_id', array("CONCAT(u.first_name, ' ', u.last_name) as name"))
                       ->where('club_id = ?', $clubId);
        
        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll();
    }
}
