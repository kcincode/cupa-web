<?php

class Model_DbTable_VolunteerMember extends Zend_Db_Table
{
    protected $_name = 'volunteer_member';
    protected $_primary = 'id';

    public function fetchVolunteers($volunteerId)
    {
        $select = $this->getAdapter()
                       ->select()
                       ->from(array('vm' => $this->_name), array('comment', 'answers'))
                       ->joinLeft(array('vp' => 'volunteer_pool'), 'vp.id = vm.volunteer_pool_id', array('name AS vname', 'email AS vemail', 'phone AS vphone', 'involvement', 'primary_interest', 'experience'))
                       ->joinLeft(array('u' => 'user'), 'u.id = vp.user_id', array('first_name', 'last_name', 'email'))
                       ->joinLeft(array('up' => 'user_profile'), 'up.user_id = u.id', array('phone'))
                       ->where('volunteer_id = ?', $volunteerId);

        return $this->getAdapter()->fetchAll($select);
    }

    public function addVolunteer($volunteerId, $memberId, $data)
    {
        $excludedData = array('email', 'first_name', 'last_name', 'phone', 'comment', 'register');

        $select = $this->select()
                       ->where('volunteer_pool_id = ?', $memberId);

        $result = $this->fetchRow($select);

        if(!$result) {
            foreach($data as $key => $value) {
                if(!in_array($key, $excludedData)) {
                    $answers[$key] = $value;
                }
            }

            $result = $this->createRow();
            $result->volunteer_id = $volunteerId;
            $result->volunteer_pool_id = $memberId;
            $result->enrolled_at = date('Y-m-d H:i:s');
            $result->comment = $data['comment'];
            $result->answers = Zend_Json::encode($answers);
            $result->save();

            return $result;
        }

        return null;
    }

}
