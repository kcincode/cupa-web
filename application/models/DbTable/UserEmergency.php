<?php

class Cupa_Model_DbTable_UserEmergency extends Zend_Db_Table
{
    protected $_name = 'user_emergency';
    protected $_primary = 'id';

    public function fetchContact($userId, $phone)
    {
    	$select = $this->select()
                       ->where('user_id = ?', $userId)
    	               ->where('phone = ?', $phone);

    	return $this->fetchRow($select);
    }
}
