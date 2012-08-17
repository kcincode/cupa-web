<?php

class Model_DbTable_VolunteerPool extends Zend_Db_Table
{
    protected $_name = 'volunteer_pool';
    protected $_primary = 'id';

    public function addVolunteer($userData)
    {
    	if(!isset($userData['experience']) or !isset($userData['primary_interest']) or !isset($userData['involvement'])) {
    		return null;
    	}

    	if(!empty($userData['user_id'])) {
	    	$volunteer = $this->fetchVolunteerFromId($userData['user_id']);
		} else {
			$volunteer = $this->fetchVolunteerFromEmail($userData['email']);
		}

		if(!empty($volunteer)) {
			return $volunteer;
		}

		$id = $this->insert($userData);

		return $this->find($id)->current();
    }

    public function fetchVolunteerFromId($userId)
    {
    	$select = $this->select()
    	               ->where('user_id = ?', $userId);

    	return $this->fetchRow($select);
    }

    public function fetchVolunteerFromEmail($email)
    {
    	$select = $this->select()
    	               ->where('email = ?', $email);

    	return $this->fetchRow($select);
    }
}
