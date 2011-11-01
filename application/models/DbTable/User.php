<?php

class Cupa_Model_DbTable_User extends Zend_Db_Table
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
            $userProfileTable = new Cupa_Model_DbTable_UserProfile();
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
}
