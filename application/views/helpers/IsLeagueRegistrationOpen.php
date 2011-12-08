<?php

class My_View_Helper_IsLeagueRegistrationOpen extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function isLeagueRegistrationOpen($start, $end)
    {
        $date = date('Y-m-d H:i:s');
        if($date >= $start and $date < $end) {
            return true;
        }
        
        return false;
    }
}