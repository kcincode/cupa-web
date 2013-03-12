<?php

class Model_DbTable_Privilege extends Zend_Db_Table
{
    protected $_name = 'privilege';
    protected $_primary = 'id';


    public function fetchAllPrivileges($role = null)
    {
        $db = $this->getAdapter();
        if(!isset($role)) {
            // if a role was not passed in return all privileges
            $select = $db->select()->from(array('p' => $this->_name), array('id', 'type', 'resource', 'action'))
                                   ->joinLeft(array('r' => 'role'), 'r.id = p.role_id', array('r.name AS role'));
        } else {
            if($role != 0) {
                // if a role was passed in and not 0 return privileges for that role
                $select = $db->select()->from(array('p' => $this->_name), array('id', 'type', 'resource', 'action'))
                                       ->joinRight(array('r' => 'role'), 'r.id = p.role_id', array('r.name AS role'));

                // this handles if the a role id or role name is passed into the function
                if(is_numeric($role)) {
                    $select->where('role_id = ?', $role);
                } else {
                    $select->where('r.name = ?', $role);
                }
            } else {
                // if 0 is passed as the role (means that you wan just the null roles)
                $select = $db->select()->from(array('p' => $this->_name), array('id', 'type', 'resource', 'action'))
                                       ->joinLeft(array('r' => 'role'), 'r.id = p.role_id', array('r.name AS role'))
                                       ->where('role_id IS NULL');
            }
        }
        // return the results
        return $db->fetchAll($select);
    }
}
