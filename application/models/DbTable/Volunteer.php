<?php

class Model_DbTable_Volunteer extends Zend_Db_Table
{
    protected $_name = 'volunteer';
    protected $_primary = 'id';

    public function fetchUpcomingVolunteers()
    {
        $select = $this->select()
                       ->where('start > ?', date('Y-m-d H:i:s'))
                       ->order('start');

        $results = array();
        $volunteerMemberTable = new Model_DbTable_VolunteerMember();
        foreach($this->fetchAll($select) as $volunteer) {
            $tmp = $volunteer->toArray();
            $tmp['members'] = $volunteerMemberTable->fetchVolunteers($volunteer->id);
            $results[] = $tmp;
        }

        return $results;
    }
}
