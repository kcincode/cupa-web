<?php

class My_View_Helper_DisplayAge extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function displayAge($birthday)
    {
        if(empty($birthday)) {
            return 'Unknown';
        }

        list($year, $month, $day) = explode("-", $birthday);
        return( date("md") < $month.$day ? date("Y") - $year - 1 : date("Y") - $year );
    }
}
