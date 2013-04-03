<?php

class Model_DbTable_Paypal extends Zend_Db_Table
{
    protected $_name = 'paypal';
    protected $_primary = 'id';

    public function log($id, $type, $data)
    {
        return $this->insert(array(
            'type' => $type,
            'type_id' => $id,
            'data' => $data,
        ));
    }
}
