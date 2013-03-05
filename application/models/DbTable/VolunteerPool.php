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

            if(empty($volunteer)) {
                $volunteer = $this->fetchVolunteerFromId($user->id);
            }
		}

        if($user) {
            $userData['user_id'] = $user->id;
            unset($userData['name']);
            unset($userData['email']);
            unset($userData['phone']);
        }

        $interests = array();
        foreach($userData['primary_interest'] as $interest) {
            if($interest != 'Other') {
                $interests[] = $interest;
            } else {
                $interests[] = $userData['other'];
            }
        }
        $userData['primary_interest'] = implode(',', $interests);
        unset($userData['other']);

        if(!empty($volunteer)) {
            foreach(array('experience', 'primary_interest', 'involvement') as $key) {
                $volunteer->$key = (empty($volunteer->$key)) ? $userData[$key] : $volunteer->$key;
            }
            $volunteer->save();

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

    public function fetchMember($data)
    {
        $userTable = new Model_DbTable_User();
        $user = $userTable->find(Zend_Auth::getInstance()->getIdentity())->current();
        if($user->email != $data['email']) {
            // if logged in email equals email find by email
            $member = $this->fetchVolunteerFromEmail($data['email']);
        } else {
            $member = $this->fetchVolunteerFromId($user->id);
        }

        if($member) {
            return $member;
        }

        if($user->email != $data['email']) {
            // modify the data
            $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
            unset($data['first_name']);
            unset($data['last_name']);
            unset($data['comment']);

            $data['involvement'] = (empty($data['involvement'])) ? 'null' : $data['involvement'];
            $data['primary_interest'] = (empty($data['primary_interest'])) ? 'null' : $data['primary_interest'];
            $data['experience'] = (empty($data['experience'])) ? 'null' : $data['experience'];

            $id = $this->insert($data);

            return $this->find($id)->current();
        } else {
            $row = $this->createRow();
            $row->user_id = $user->id;
            $row->save();

            return $row;
        }
    }
}
