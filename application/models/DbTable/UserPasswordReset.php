<?php

class Model_DbTable_UserPasswordReset extends Zend_Db_Table
{
    protected $_name = 'user_password_reset';
    protected $_primary = 'id';

    public function fetchByCode($code)
    {
        $select = $this->select()
                       ->where('code = ?', $code);

        return $this->fetchRow($select);
    }

    public function isUniqueCode($code)
    {
        if(empty($code)) {
            return false;
        }

        $select = $this->select()
                       ->where('code = ?', $code);

        $result = $this->fetchRow($select);
        if(isset($result->code)) {
            return false;
        }

        return true;
    }

    public function generateUniqueCode($length = 15)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = '';
        while(!$this->isUniqueCode($code)) {
            $code = '';
            for ($p = 0; $p < $length; $p++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
        }

        return $code;
    }
}
