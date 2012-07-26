<?php

class VolunteerController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/volunteer/index.css');
    }

    public function registerAction()
    {

    }

    public function listAction()
    {

    }
}
