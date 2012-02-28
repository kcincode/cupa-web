<?php

class Model_DbTable_User extends Zend_Db_Table
{
    protected $_name = 'user';
    protected $_primary = 'id';

    public function isUniqueCode($column, $code)
    {
        if(empty($code)) {
            return false;
        }

        $select = $this->select()
                       ->where($column . ' = ?', $code);

        $result = $this->fetchRow($select);
        if(isset($result->$column)) {
            return false;
        }

        return true;
    }

    public function generateUniqueCodeFor($column, $length = 15)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = '';
        while(!$this->isUniqueCode($column, $code)) {
            $code = '';
            for ($p = 0; $p < $length; $p++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
        }

        return $code;
    }

    public function fetchUserBy($column, $value)
    {
        if($column == 'id') {
            return $this->find($value)->current();
        } else {
            $select = $this->select()
                           ->where($column . ' = ?', $value);

            return $this->fetchRow($select);
        }
    }

    public function createNewUser($firstName, $lastName, $email)
    {
        $expire = date('Y-m-d H:i:s', time() + 604800);
        $date = date('Y-m-d H:i:s');

        $username = substr($email, 0, strpos($email, '@'));

        $data = array(
            'username' => $username,
            'salt' => null,
            'password' => md5('sdfughaiudgbsfdgsdfgwrthwrhyterHethns'),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'activation_code' => $this->generateUniqueCodeFor('activation_code'),
            'requested_at' => $date,
            'activated_at' => null,
            'expires_at' => $expire,
            'updated_at' => $date,
            'last_login' => null,
            'login_errors' => 0,
            'is_active' => 0,
        );

        $userId = $this->insert($data);

        if(is_numeric($userId)) {
            $userProfileTable = new Model_DbTable_UserProfile();
            $userProfile = $userProfileTable->createRow();
            $userProfile->user_id = $userId;
            $userProfile->save();
        }

        return $userId;
    }

    public function updateUserPasswordFromCode($code, $password)
    {
        $user = $this->fetchUserBy('activation_code', $code);
        if($user) {
            if(empty($user->salt)) {
                $user->salt = $this->generateUniqueCodeFor('salt');
                $user->password = sha1($user->salt . $password);
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
                return $user->id;
            }
        }

        return false;
    }

    public function updateUserPasswordFromId($id, $password)
    {
        $user = $this->find($id)->current();
        if($user) {
            if(empty($user->salt)) {
                $user->salt = $this->generateUniqueCodeFor('salt');
                $user->password = sha1($user->salt . $password);
            } else {
                $user->password = sha1($user->salt . $password);
            }

            $user->updated_at = date('Y-m-d H:i:s');
            $user->save();
            return $user->id;
        }

        return false;
    }

    public function fetchAllUsers($showDisabled = false)
    {
        $select = $this->select()
                       ->order('last_name')
                       ->order('first_name');

        if(!$showDisabled) {
            $select->where('is_active = ?', 1);
        }

        return $this->fetchAll($select);
    }

    public function fetchMinor($parentId, $first, $last)
    {
        $select = $this->select()
                       ->where('parent = ?', $parentId)
                       ->where('first_name = ?', $first)
                       ->where('last_name = ?', $last);

        return $this->fetchRow($select);
    }

    public function fetchProfile($user)
    {
        $data = array();
        $exclude = array('salt', 'password', 'activation_code', 'activated_at', 'expires_at', 'login_errors');
        foreach($user as $key => $value) {
            if(!in_array($key, $exclude)) {
                $data[$key] = $value;
            }
        }

        // get the public user profile data
        $userProfileTable = new Model_DbTable_UserProfile();
        $userLevelTable = new Model_DbTable_UserLevel();
        $userProfile = $userProfileTable->find($user->id)->current();
        $data['profile'] = array(
            'nickname' => $userProfile->nickname,
            'gender' => $userProfile->gender,
            'age' => $userProfile->birthday,
            'phone' => $userProfile->phone,
            'height' => $userProfile->height,
            'level' => (empty($userProfile->level)) ? null : $userProfile->level,
            'experience' => $userProfile->experience,
        );

        // get users league data
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $data['leagues'] = $leagueMemberTable->fetchUserLeagues($user->id);

        $data['minors'] = $this->fetchAllMinors($user->id, true);

        $userEmergencyTable = new Model_DbTable_UserEmergency();
        $data['contacts'] = $userEmergencyTable->fetchAllContacts($user->id);
        if(count($data['contacts'])) {
            $data['contacts'] = $data['contacts']->toArray();
        }

        return $data;

    }

    public function hasMinors($userId)
    {
        $select = $this->select()
                       ->where('parent = ?', $userId);

        $results = $this->fetchAll($select);

        return (count($results) == 0) ? false : true;
    }

    public function fetchAllMinors($userId, $all = false)
    {
        if($all) {
            $select = $this->getAdapter()->select()
                           ->from(array('u' => $this->_name), array('id', 'first_name', 'last_name'))
                           ->joinLeft(array('up' => 'user_profile'), 'up.user_id = u.id', array('gender', 'birthday', 'nickname', 'height', 'experience'))
                           ->joinLeft(array('ul' => 'user_level'), 'ul.id = up.level', array('name AS level'))
                           ->where('parent = ?', $userId)
                           ->order('last_name')
                           ->order('first_name');

            $result = $this->getAdapter()->fetchAll($select);
            return $result;
        }

        $select = $this->select()
                       ->where('parent = ?', $userId)
                       ->order('last_name')
                       ->order('first_name');

        $results = $this->fetchAll($select);

        $data = array();
        if($results) {
            foreach($results as $row) {
                $data[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
            }
        }

        return $data;
    }

    public function createBlankMinor($parentId)
    {
        $select = $this->select()
                       ->where('parent = ?', $parentId)
                       ->where('first_name = ?', 'First')
                       ->where('last_name = ?', 'Last');

        $result = $this->fetchRow($select);
        if(!$result) {
            $id = $this->insert(array(
                'parent' => $parentId,
                'first_name' => 'First',
                'last_name' => 'Last',
                'is_active' => 1,
            ));

            if(is_numeric($id)) {
                $userProfileTable = new Model_DbTable_UserProfile();
                $userProfileTable->insert(array(
                    'user_id' => $id,
                ));

                return $this->find($id)->current();
            }
        }

    }
}
