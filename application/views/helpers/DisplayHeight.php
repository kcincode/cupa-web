<?php

class My_View_Helper_DisplayHeight extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function displayHeight($height)
    {
        if(is_numeric($height)) {
            $feet = (int)($height / 12);
            $inches = $height % 12;

            return $feet . "'" . $inches . '"';
        } else {
            return 'Unknown';
        }
    }
}
