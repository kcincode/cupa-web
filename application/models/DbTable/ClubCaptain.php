<?php

class Model_DbTable_ClubCaptain extends Zend_Db_Table
{
    protected $_name = 'club_captain';
    protected $_primary = 'id';
 
    public function fetchAllByClub($clubId)
    {
        $select = $this->getAdapter()->select()
                       ->from(array('cc' => 'club_captain'), array('user_id'))
                       ->joinLeft(array('u' => 'user'), 'u.id = cc.user_id', array("CONCAT(u.first_name, ' ', u.last_name) as name"))
                       ->where('cc.club_id = ?', $clubId)
                       ->order('u.last_name')
                       ->order('u.first_name');
        
        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll();
    }

    public function updateCaptains($captains, $clubId)
    {
        foreach($this->fetchAll() as $row) {
            if(!in_array($row->user_id, $captains)) {
                $row->delete();
            }
        }

        foreach($captains as $captain) {
            $select = $this->select()
                           ->where('club_id = ?', $clubId)
                           ->where('user_id = ?', $captain);

            $result = $this->fetchRow($select);
            if(!$result) {
                $this->insert(array(
                    'user_id' => $captain,
                    'club_id' => $clubId,
                ));
            }
        }
    }
}
