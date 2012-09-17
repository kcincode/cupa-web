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

        $userTable = new Model_DbTable_User();
    	if(!empty($userData['user_id'])) {
	    	$volunteer = $this->fetchVolunteerFromId($userData['user_id']);
            $user = $userTable->find($userData['user_id'])->current();
		} else {
			$volunteer = $this->fetchVolunteerFromEmail($userData['email']);
            $user = $userTable->fetchUserBy('email', $userData['email']);
		}

		if(!empty($volunteer)) {
			return $volunteer;
		}

        if($user) {
            $userData['user_id'] = $user->id;
            unset($userData['name']);
            unset($userData['email']);
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
