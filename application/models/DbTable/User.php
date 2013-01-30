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

    public function fetchAllUsers($showDisabled = false, $showMinors = false)
    {
        $select = $this->select()
                       ->order('last_name')
                       ->order('first_name');

        if(!$showDisabled) {
            $select->where('is_active = ?', 1);
        }

        if(!$showMinors) {
            $select->where('parent IS NULL');
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
        $userLevelTable = new Model_DbTable_UserLevel();
        $userProfileTable = new Model_DbTable_UserProfile();
        $clubMemberTable = new Model_DbTable_ClubMember();
        $userProfile = $userProfileTable->find($user->id)->current();
        $level = $userLevelTable->find($userProfile->level)->current();
        $data['profile'] = array(
            'nickname' => $userProfile->nickname,
            'gender' => $userProfile->gender,
            'age' => $userProfile->birthday,
            'phone' => $userProfile->phone,
            'height' => $userProfile->height,
            'level' => (empty($level)) ? null : $level->name,
            'experience' => $userProfile->experience,
        );

        // get users league data
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $data['leagues'] = $leagueMemberTable->fetchUserLeagues($user->id, false);
        $data['clubs'] = $clubMemberTable->fetchUserClubs($user->id);

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

    public function fetchAllDuplicates($userId = null, $actives = true, $excludeMinors = false)
    {
        $duplicates = array();

        $select = $this->select();

        if(!$actives) {
            $select->where('is_active = ?', 1);
        }

        foreach($this->fetchAll($select) as $row) {
            if($excludeMinors == true and is_numeric($row['parent'])) {
                continue;
            } else if(is_numeric($row['parent']) and $row['last_name'] == 'Last' and $row['first_name'] == 'First') {
                continue;
            } else {
                if($row['last_name'] != 'Southard' and $row['first_name'] != 'Terence') {
                    $key = strtolower($row['last_name']) . '-' . strtolower($row['first_name']);
                    $duplicates[$key][] = $row->toArray();
                }
            }
        }

        foreach($duplicates as $name => $data) {
            if(count($data) == 1) {
                unset($duplicates[$name]);
            } else if($userId) {
                $flag = 0;
                foreach($data as $item) {
                    if($item['id'] == $userId) {
                        $flag = 1;
                        break;
                    }
                }
                if($flag == 0) {
                    unset($duplicates[$name]);
                }
            }
        }

        return $duplicates;
    }

    public function toggle($userId)
    {
        $user = $this->find($userId)->current();
        if($user) {
            $user->is_active = ($user->is_active == 1) ? 0 : 1;
            $user->save();
        }
    }

    public function mergeAccounts($userId)
    {
        $users = $this->fetchAllDuplicates($userId);

        $ids = array();
        foreach($users as $data) {
            foreach($data as $user) {
                if($user['id'] != $userId) {
                    $ids[] = $user['id'];
                }
            }
        }

        // backup the database
        $this->backupDb($this->getAdapter(), $ids, $this->find($userId)->current());

        $ids = implode(',', $ids);
        $tables = array(
            'club_captain',
            'league_member',
            'officer',
            'tournament_member',
            'user_emergency',
            'user_password_reset',
            'user_role',
            'user_waiver',
        );

        foreach($tables as $table) {
            $sql = "UPDATE $table SET user_id = $userId WHERE user_id IN ($ids)";
            $this->getAdapter()->query($sql);
        }

        $userProfileTable = new Model_DbTable_UserProfile();
        $userProfileTable->mergeUsers($ids, $userId);

        foreach(explode(',', $ids) as $id) {
            $this->getAdapter()->query("UPDATE user SET parent = $userId WHERE parent = $id");
            $user = $this->find($id)->current();
            $user->delete();
        }
    }

    private function backupDb($db, $ids, $user)
    {
        $tables = array(
            'club_captain',
            'league_member',
            'officer',
            'tournament_member',
            'user_emergency',
            'user_password_reset',
            'user_role',
            'user_waiver',
            'user_profile',
            'user',
        );

        $fp = fopen(APPLICATION_PATH . '/data/user_merges/' . $user->last_name . '-' . $user->first_name . '.log', 'a');
        fwrite($fp, '=== BACKUP Starting ' . date('Y-m-d H:i:s') . " ===\n");
        foreach($tables as $table) {
            $stmt = $db->query('SELECT * FROM ' . $table);
            $data = $stmt->fetchAll();

            foreach($data as $row) {
                $sql = "INSERT INTO $table VALUES (";
                $i = 0;
                if((isset($row['user_id']) and in_array($row['user_id'], $ids)) or ($table == 'user' and in_array($row['id'], $ids))) {
                    foreach($row as $key => $value) {
                        if($i > 0) {
                            $sql .= ", ";
                        }
                        $sql .= "'" . addslashes($value) . "'";
                        $i++;
                    }
                }
                if($i > 0) {
                    $sql .= ");\n";
                    fwrite($fp, $sql);
                }
            }
        }
        fwrite($fp, '=== BACKUP Finished ' . date('Y-m-d H:i:s') . " ===\n\n");
        fclose($fp);
    }

    public function fetchByFullname($name)
    {
        $data = explode(' ', $name);
        if(count($data) == 2) {
            $select = $this->select()
                           ->where('first_name = ?', $data[0])
                           ->where('last_name = ?', $data[1]);

            return $this->fetchRow($select);
        }

        return null;
    }
    
    public function fetchAllFilteredUsers($filter = null)
    {
        $select = $this->select()
                       ->where('parent IS NULL')
                       ->order('last_name')
                       ->order('first_name');
        
        if(!is_null($filter)) {
            $select = $select->where('first_name LIKE ?', "%$filter%")
                             ->orWhere('last_name LIKE ?', "%$filter%")
                             ->orWhere('email LIKE ?', "%$filter%");
        }

        return $this->fetchAll($select);
    }
}
