<?php

class My_View_Helper_GetTournamentHeaderImage extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function getTournamentHeaderImage()
    {
        return $this->view->baseUrl() . '/images/banners/about.jpg';
        //return $this->view->baseUrl() . '/images/tournaments/default.jpg';
    }
}
