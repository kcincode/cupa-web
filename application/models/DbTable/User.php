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
}
