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
        if(file_exists(APPLICATION_WEBROOT . '/images/tournaments/' . $this->view->tournament->name . '.jpg')) {
            return $this->view->baseUrl() . '/images/tournaments/' . $this->view->tournament->name . '.jpg';
        }

        return $this->view->baseUrl() . '/images/tournaments/default.jpg';
    }
}
