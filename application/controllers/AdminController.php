<?php

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('auth');
        } else {
            if(!$this->view->hasRole('admin')) {
                $this->view->message('You do not have access to the admin featuers.');
                $this->_redirect('/');
            }
        }
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin/common.css');
    }

    public function indexAction()
    {

    }

    public function reportsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin/reports.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/highcharts/highcharts.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/admin/reports.js');
    }

    public function loadbrowserdataAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $userAccessLogTable = new Model_DbTable_UserAccessLog();
        echo Zend_Json::encode($userAccessLogTable->fetchReportData(), true);
    }

    public function authAction()
    {


    }
}
