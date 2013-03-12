<?php

class Model_DbTable_Role extends Zend_Db_Table
{
    protected $_name = 'role';
    protected $_primary = 'id';

    public function fetchUserRole($id)
    {
        $select = $this->getAdapter()
                       ->select()
                       ->from(array('r' => $this->_name), array('name'))
                       ->joinLeft(array('ur' => 'user_role'), 'ur.role = r.name', array())
                       ->order('r.weight DESC')
                       ->where('ur.user_id = ?', $id);

       $result = $this->getAdapter()->fetchRow($select);

       if(count($result) < 1) {
          return null;
       }

       return $result['name'];
    }

    public function fetchAllRoles($order = 'name')
    {
        $select = $this->select()
                       ->order($order);

        return $this->fetchAll($select);
    }

    public function fetchRoleName($roleId)
    {
        $select = $this->fetchRow($this->select()->where('id = ?', $roleId));

        if(isset($select->name)) {
            return $select->name;
        }

        return false;
    }
}
