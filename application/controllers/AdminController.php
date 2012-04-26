<?php

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        // TODO: check permissions per action

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

    public function browserAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin/browser.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/highcharts/highcharts.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/admin/browser.js');

        if($this->getRequest()->isPost()) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $userAccessLogTable = new Model_DbTable_UserAccessLog();
            echo Zend_Json::encode($userAccessLogTable->fetchReportData(), true);
        }
    }

    public function authAction()
    {
    }

    public function unpaidAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin/unpaid.css');

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $data = array();
        foreach($leagueMemberTable->fetchUnpaidPlayers() as $row) {
            if(!isset($data[$row['user_id']])) {
                $data[$row['user_id']] = array(
                    'leagues' => array($row['league']),
                    'owed' => $row['cost'],
                );
            } else {
                if(!in_array($row['league'], $data[$row['user_id']]['leagues'])) {
                    $data[$row['user_id']]['leagues'][] = $row['league'];
                    $data[$row['user_id']]['owed'] += $row['cost'];
                }
            }
        }
        $this->view->players = $data;
    }

    public function duplicatesAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin/duplicates.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/admin/duplicates.js');

        $user = $this->getRequest()->getParam('user');

        $userTable = new Model_DbTable_User();
        if($user) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $userTable->mergeAccounts($user);
            $this->view->message("User data merged to #$user.", 'success');
            $this->_redirect('admin/duplicates');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = '/admin/duplicates';

        $this->view->users = $userTable->fetchAllDuplicates();
    }
}
