<?php
class Cupa_Model_Authenticate {
    private $_user;
    
    public function __construct($user)
    {
        $this->_user = $user;
    }

    public function authenticate($password)
    {
        return (empty($this->_user->salt)) ? $this->checkPassword($password, 'md5') : $this->checkPassword($password, 'sha1');
    }
    
    private function checkPassword($password, $algorithm)
    {
        if($algorithm == 'md5') {
            // generate md5 hash of password
            $passwordHash = md5($password);
        } else if($algorithm == 'sha1') {
            // generate sha1 hash of the salt + password
            $passwordHash = sha1($user->salt . $password);
        }

        // check to see if the hashes match
        if($passwordHash == $user->password) {
            return true;
        }

        // return false if not
        return false;
    }

}