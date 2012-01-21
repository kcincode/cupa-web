<?php

class My_View_Helper_CompleteStatus extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function completeStatus($data)
    {
        if($data['paid'] == 0) {
            return false;
        }
        
        if(!$this->view->hasUserSignedWaiver($data['user_id'], $this->view->league->year)) {
            return false;
        }
        
        if($data['release'] == 0) {
            return false;
        }
        
        return true;
    }
}
