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
        
        if($data['waiver'] != $this->view->league->year) {
            return false;
        }
        
        if($data['release'] == 0) {
            return false;
        }
        
        if(!empty($data['balance'])) {
            return false;
        }
        
        return true;
    }
}
