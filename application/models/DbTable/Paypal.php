<?php

class Model_DbTable_Paypal extends Zend_Db_Table
{
    protected $_name = 'paypal';
    protected $_primary = 'id';

    public function log($userId, $id, $type, $data)
    {
        return $this->insert(array(
            'user_id' => $userId,
            'type' => $type,
            'type_id' => $id,
            'data' => $data,
        ));
    }
}
