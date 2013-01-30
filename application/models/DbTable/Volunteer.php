<?php

class Model_DbTable_Volunteer extends Zend_Db_Table
{
    protected $_name = 'volunteer';
    protected $_primary = 'id';

    public function fetchUpcomingVolunteers()
    {
        $select = $this->getAdapter()
                       ->select()
                       ->from(array('v' => $this->_name), array('*'))
                       ->joinLeft(array('vl' => 'volunteer_location'), 'vl.volunteer_id = v.id', array('name AS location', 'street', 'city', 'state', 'zip'))
                       ->where('start > ?', date('Y-m-d H:i:s'))
                       ->order('start');

        $results = array();
        $volunteerMemberTable = new Model_DbTable_VolunteerMember();
        foreach($this->getAdapter()->fetchAll($select) as $volunteer) {
            $volunteer['members'] = $volunteerMemberTable->fetchVolunteers($volunteer['id']);
            $results[] = $volunteer;
        }

        return $results;
    }
}