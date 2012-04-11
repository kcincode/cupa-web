<?php

class Model_DbTable_PageError extends Zend_Db_Table
{
    protected $_name = 'page_error';
    protected $_primary = 'id';

    public function log($url, $data, $code, $exception)
    {
        $this->insert(array(
            'url' => $url,
            'code' => $code,
            'data' => $data,
            'exception' => $exception,
            'when' => date('Y-m-d H:i:s'),
        ));
    }
}
