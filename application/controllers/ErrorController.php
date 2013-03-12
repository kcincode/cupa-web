<?php

class ErrorController extends Zend_Controller_Action
{
    protected $_url;
    protected $_params;

    public function init()
    {
        // change the layout file for all pages.
        $this->_helper->_layout->setLayout('error');

        $this->_url = $_SERVER['REQUEST_URI'];

        $this->_params = array(
            'get' => $this->getRequest()->getParams(),
            'post' => $this->getRequest()->getPost(),
        );
        $this->_params = Zend_Json::encode($this->_params, true);
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        $log = $this->getLog();
        if ($log) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $pageErrorTable = new Model_DbTable_PageError();
        $pageErrorTable->log($this->_url, $this->_params, '404', $errors->exception);

        $this->view->request   = $errors->request;
    }

    public function authAction()
    {
        // change the layout file for all pages.
        $this->_helper->_layout->setLayout('layout');
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}
