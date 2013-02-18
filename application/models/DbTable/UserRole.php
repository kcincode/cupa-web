<?php

class Model_DbTable_UserRole extends Zend_Db_Table
{
    protected $_name = 'user_role';
    protected $_primary = 'id';

    public function hasRole($userId, $role, $pageId = null)
    {
        $select = $this->select()
                       ->where('user_id = ?', $userId)
                       ->where('role = ?', $role);

        if($pageId) {
            $select->where('page_id = ? OR page_id IS NULL', $pageId);
        } else {
            $select->where('page_id IS NULL');
        }

        $result = $this->fetchRow($select);

        if(isset($result->role)) {
            return true;
        }

        return false;

    }

    public function addRole($userId, $role, $pageId = null)
    {
        if($this->hasRole($userId, $role, $pageId)) {
            return;
        }

        $row = $this->createRow();
        $row->user_id = $userId;
        $row->role = $role;
        $row->page_id = $pageId;
        $row->save();
    }

    public function fetchRoles($userId)
    {
        $select = $this->select()
                       ->where('user_id = ?', $userId);

        $data = array();
        foreach($this->fetchAll($select) as $row) {
            if(!isset($data[$row->role])) {
                $data[$row->role] = $row->role;
            }
        }

        return $data;
    }

}
