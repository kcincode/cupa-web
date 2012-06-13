<?php

class My_View_Helper_OfficerImageUrl extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function officerImageUrl($userId)
    {
        if(file_exists(APPLICATION_WEBROOT . '/images/officers/' . $userId . '.jpg')) {
            $url = $this->view->baseUrl() . '/images/officers/' . $this->view->escape($userId) . '.jpg';
        } else {
            $url = $this->view->baseUrl() . '/images/officers/default-profile.png';
        }

        return $url;
    }
}
