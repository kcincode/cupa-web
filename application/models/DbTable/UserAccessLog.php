<?php

class Model_DbTable_UserAccessLog extends Zend_Db_Table
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

    public function fetchReportData($type)
    {
        $select = $this->getAdapter()->select()
                       ->distinct()
                       ->from(array('ual' => $this->_name), array('client'))
                       ->order('client');

        if($type == 'month') {
            $monthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));
            $select = $select->where('time > ?', $monthAgo);
        }

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            switch(true) {
                case (preg_match('/^blackberry/i', $row['client'])):
                case (preg_match('/iphone/i', $row['client'])):
                case (preg_match('/ipad/i', $row['client'])):
                case (preg_match('/ipod/i', $row['client'])):
                case (preg_match('/android/i', $row['client'])):
                case (preg_match('/kindle/i', $row['client'])):
                case (preg_match('/danger hiptop/i', $row['client'])):
                    $key = 'Mobile';
                    break;
                case (preg_match('/msie 5.5/i', $row['client'])):
                    //$key = 'IE5.5';
                    //break;
                case (preg_match('/msie 6.0/i', $row['client'])):
                    //$key = 'IE6';
                    //break;
                case (preg_match('/msie 7.0/i', $row['client'])):
                    //$key = 'IE7';
                    //break;
                case (preg_match('/msie 8.0/i', $row['client'])):
                    //$key = 'IE8';
                    //break;
                case (preg_match('/msie 9.0/i', $row['client'])):
                    //$key = 'IE9';
                    //break;
                case (preg_match('/msie 10.0/i', $row['client'])):
                    //$key = 'IE10';
                    $key = 'IE';
                    break;
                case (preg_match('/firefox/i', $row['client'])):
                    //$matches = array();
                    //preg_match('/.*(x11|macintosh|windows).*Firefox\/(\d*\.\d*).*/i', $row['client'], $matches);
                    $key = 'Firefox';
                    /*
                    $key .= $matches[2];
                    switch($matches[1]) {
                        case 'Macintosh':
                            $key .= 'Mac';
                            break;
                        case 'Windows':
                            $key .= 'Windows';
                            break;
                        case 'X11':
                            $key .= 'Linux';
                            break;
                    } */
                    break;
                case (preg_match('/chrome/i', $row['client'])):
                    //$matches = array();
                    //preg_match('/.*(x11|macintosh|windows).*chrome\/(\d*\.\d*).*/i', $row['client'], $matches);
                    $key = 'Chrome';
                    /*
                    $key .= $matches[2];
                    switch($matches[1]) {
                        case 'Macintosh':
                            $key .= 'Mac';
                            break;
                        case 'Windows':
                            $key .= 'Windows';
                            break;
                        case 'webOS':
                            $key .= 'Palm';
                            break;
                    } */
                    break;
                case (preg_match('/safari/i', $row['client'])):
                    //$matches = array();
                    //preg_match('/.*(webos|macintosh|windows).*Version\/(\d*\.\d*).*/i', $row['client'], $matches);
                    $key = 'Safari';
                    //$key .= $matches[2];
                    /*
                    switch($matches[1]) {
                        case 'Macintosh':
                            $key .= 'Mac';
                            break;
                        case 'Windows':
                            $key .= 'Windows';
                            break;
                        case 'webOS':
                            $key .= 'Palm';
                            break;
                    } */
                    break;
                default:
                    $key = 'Unknown';
            }

            $data[$key] = (empty($data[$key])) ? 1 : $data[$key] + 1;
        }
        $result = array();
        $total = 0;
        foreach($data as $key => $value) {
            $result[] = array($key, $value);
            $total += $value;
        }
        $result[] = array('total', $total);

        usort($result, function($a, $b) {
            return($a[1] > $b[1]) ? -1 : 1;
        });


        return $result;
    }
}
