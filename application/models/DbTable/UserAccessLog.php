<?php

class Cupa_Model_DbTable_UserAccessLog extends Zend_Db_Table
{
    protected $_name = 'user_access_log';
    protected $_primary = 'id';
    
    public function log($username, $type, $comment = null)
    {
        $this->insert(array(
            'user' => $username,
            'time' => date('Y-m-d H:i:s'),
            'type' => $type,
            'session' => Zend_Session::getId(),
            'comment' => $comment,
            'client' => $_SERVER['HTTP_USER_AGENT'],
        ));
    }
}