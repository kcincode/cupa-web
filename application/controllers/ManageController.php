<?php

class ManageController extends Zend_Controller_Action
{
    public function init()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('auth');
        }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/manage/common.css');
    }

    public function indexAction()
    {
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }
    }

    public function authAction()
    {
    }

    public function unpaidAction()
    {
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/manage/unpaid.css');

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
    
    public function userAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/manage/user.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/manage/user.js');
        
        $userTable = new Model_DbTable_User();
        $this->view->users = $userTable->fetchAllUsers(true);
        
    }
}
