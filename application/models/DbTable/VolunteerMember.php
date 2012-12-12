<?php

class Model_DbTable_VolunteerMember extends Zend_Db_Table
{
    protected $_name = 'volunteer_member';
    protected $_primary = 'id';

    public function fetchVolunteers($volunteerId)
    {
        $select = $this->select()
                       ->where('volunteer_id = ?', $volunteerId);

        return $this->fetchAll($select);
    }
}
