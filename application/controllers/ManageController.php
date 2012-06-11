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

        $this->view->edit = 0;

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->view->edit = 1;


            $userTable = new Model_DbTable_User();
            $this->view->userEdit = $userTable->find($post['user_id'])->current();
            $form = new Form_UserManage($this->view->userEdit);
            if(isset($post['edit'])) {
                if($form->isValid($post)) {
                    $user = $userTable->fetchUserBy('email', $post['email']);

                    if($user and ($user->id == $this->view->userEdit->id)) {
                        $this->view->userEdit->username = $post['username'];
                        $this->view->userEdit->email = $post['email'];
                        $this->view->userEdit->first_name = $post['first_name'];
                        $this->view->userEdit->last_name = $post['last_name'];
                        $this->view->userEdit->is_active = $post['is_active'];
                        $this->view->userEdit->updated_at = date('Y-m-d H:i:s');
                        $this->view->userEdit->save();

                        $this->view->message('User ' . $this->view->userEdit->email . ' modified.', 'success');
                        $this->_redirect('manage/user');
                    } else {
                        $this->view->message('The email you entered is already taken.', 'error');
                    }
                }
            }

            $this->view->form = $form;
        }


        $userTable = new Model_DbTable_User();
        $this->view->users = $userTable->fetchAllUsers(true, false);
    }
    
    public function pageAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/manage/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/manage/page.js');        
        
        $pageTable = new Model_DbTable_Page();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            $page = $pageTable->fetchBy('name', $post['page']);
            if($page) {
                $this->view->message('A page already exists with that name.', 'error');
            } else {
                $pageTable->createPage($post['page']);
                $this->_redirect('/' . $post['page'] . '/edit');
            }
        }
        
        $this->view->pages = $pageTable->fetchAllpages();
    }
}
