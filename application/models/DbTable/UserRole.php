<?php

class Cupa_Model_DbTable_UserRole extends Zend_Db_Table
{
    protected $_name = 'user_role';
    protected $_primary = 'id';
    
    public function hasRole($userId, $role, $pageId = null)
    {
        $select = $this->select()
                       ->where('user_id = ?', $userId)
                       ->where('role = ?', $role);
        
        if($pageId) {
            $select->where('page_id = ?', $pageId);
        }
        
        $result = $this->fetchRow($select);
        
        if(isset($result->role)) {
            return true;
        }
        
        return false;
        
    }

}