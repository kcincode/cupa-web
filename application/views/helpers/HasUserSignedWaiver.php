<?php

class My_View_Helper_HasUserSignedWaiver extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function hasUserSignedWaiver($userId, $year)
    {
        $userWaiverTable = new Model_DbTable_UserWaiver();
        return $userWaiverTable->hasWaiver($userId, $year);
    }
}
