<?php

class My_View_Helper_Filesize extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function filesize($bytes, $format = 'MB', $decimals = 2)
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $convention=1024; //[1000>10^x|1024>2^x]
        $s = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
        $e = floor(log($bytes, $convention));
        return round($bytes / pow($convention,$e), $decimals) . ' ' . $s[$e];
    }
}