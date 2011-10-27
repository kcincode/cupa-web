<?php

class Cupa_Model_DbTable_User extends Zend_Db_Table
{
    protected $_name = 'user';
    protected $_primary = 'id';

    public function isUniqueActivationCode($code)
    {
        if(empty($code)) {
            return false;
        }
        
        $select = $this->select()
                       ->where('activation_code = ?', $code);
        
        $result = $this->fetchRow($select);
        if(isset($result->activation_code)) {
            return false;
        }
        
        return true;
    }
    
    public function generateUniqueActivationCode($length = 15)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = '';
        while(!$this->isUniqueActivationCode($code)) {
            $code = '';
            for ($p = 0; $p < $length; $p++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
        }
        
        return $code;
    }
}
