<?php

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
    }

    public function indexAction()
    {
    }

    public function browserAction()
    {
        $userAccessLogTable = new Model_DbTable_UserAccessLog();
        $this->view->overall = $userAccessLogTable->fetchReportData('all');
        $this->view->month = $userAccessLogTable->fetchReportData('month');

    }

    public function duplicatesAction()
    {
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

    public function duplicateemailAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $userTable = new Model_DbTable_User();
        foreach($userTable->fetchAllDuplicates(null, true, true) as $user) {
            $mail = new Zend_Mail();
            $mail->setFrom('webmaster@cincyultimate.org');
            $mail->setSubject('[CUPA] Duplicate user accounts');
            $string = "    According to the web database you have more than one account in the system.  Below is a list of the usernames and emails.  You have receieve an email at each of the accounts.  You can reply from any one letting us know which one you would like to use.  If you do not let us know within 7 days all accounts will be deactivated and possible removed depending on the how old the login is.  If you have any questions just reply to this email.\r\n\r\n";
            $string .= "   Once you choose which account to make your main account an administrator will consolidate all your data into that account and delete the others that are not going to be used.  None of your data will be lost, it will just be associated with the new account now.\r\n\r\n";
            $string .= "    Your accounts are as follows: (username: email)\r\n\r\n";

            $mail->addBcc('kcin1018@gmail.com');
            foreach($user as $id => $account) {
                if(APPLICATION_ENV == 'production') {
                    $mail->addTo($account['email']);
                }

                if($id == 0) {
                    $string = "Dear {$account['first_name']} {$account['last_name']}\r\n" . $string;
                }
                $string .= "    " . str_pad($account['username'], 20, ' ', STR_PAD_LEFT) . ": {$account['email']}\r\n";
            }

            $string .= "\r\n    All you need to do is hit reply and tell us which EMAIL ADDRESS you would like to use and we will do the rest.  We are actively trying to clean up the duplicate usernames in our system and appriciate your help.\r\n\r\nFor future reference you can always reset your password if you forget at the login window or just email webmaster@cincyultimate.org with any questions.\r\n\r\n";
            $string .= "Thanks,\r\nCUPA Website\r\n";
            $mail->setBodyText($string);

            if(APPLICATION_ENV == 'production') {
                $mail->send();
            }
        }

        $this->view->message('Emails sent to all users with duplicate accounts.', 'success');
        $this->_redirect('admin/duplicates');
    }

    public function unpaidAction()
    {
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
        $filter = $this->getRequest()->getPost('filter');
        if(empty($filter)) {
            $filter = $this->getRequest()->getParam('filter');
        }
        $page = $this->getRequest()->getUserParam('page');
        $form = new Form_Filter($filter);
        $this->view->filter = $filter;

        $reset = $this->getRequest()->getPost('reset');
        if($reset) {
            $this->_redirect('admin/user');
        }

        $userTable = new Model_DbTable_User();
        $this->view->users = Zend_Paginator::factory($userTable->fetchAllFilteredUsers($filter));
        $this->view->users->setCurrentPageNumber($page);
        $this->view->users->setItemCountPerPage(15);

        $this->view->form = $form;
    }

    public function usereditAction()
    {
        $filter = $this->getRequest()->getParam('filter');
        $page = $this->getRequest()->getParam('page');

        $userTable = new Model_DbTable_User();
        $user = $userTable->find($this->getRequest()->getUserParam('user_id'))->current();
        $form = new Form_UserManage($user, $page, $filter);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('admin/user/filter/' .  $post['filter'] . '/page/' . $post['page']);
            }

            if($form->isValid($post)) {
                $user->username = $post['username'];
                $user->email = $post['email'];
                $user->first_name = $post['first_name'];
                $user->last_name = $post['last_name'];
                $user->is_active = $post['is_active'];
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();

                $this->view->message('User ' . $user->email . ' modified.', 'success');
                $this->_redirect('admin/user/filter/' .  $post['filter'] . '/page/' . $post['page']);
            }

        }

        $this->view->form = $form;
    }
    public function pageAction()
    {
        $filter = $this->getRequest()->getPost('filter');
        if(empty($filter)) {
            $filter = $this->getRequest()->getParam('filter');
        }
        $page = $this->getRequest()->getUserParam('page');
        $form = new Form_Filter($filter);
        $this->view->filter = $filter;

        $reset = $this->getRequest()->getPost('reset');
        if($reset) {
            $this->_redirect('admin/page');
        }

        $pageTable = new Model_DbTable_Page();
        $this->view->pages = Zend_Paginator::factory($pageTable->fetchAllPages($filter));
        $this->view->pages->setCurrentPageNumber($page);
        $this->view->pages->setItemCountPerPage(15);

        $this->view->form = $form;
    }

    public function pageaddAction()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $form = new Form_Page();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('admin/page');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $pageTable = new Model_DbTable_Page();
                $page = $pageTable->createRow();
                $page->parent = (empty($data['parent'])) ? null : $data['parent'];
                $page->name = $data['name'];
                $page->title = $data['title'];
                $page->url = (empty($data['url'])) ? null : $data['url'];
                $page->target = (empty($data['target'])) ? null : $data['target'];
                $page->weight = (empty($data['weight'])) ? 0 : $data['weight'];
                $page->content = (empty($data['content'])) ? null : $data['content'];
                $page->is_visible = (empty($data['is_visible'])) ? null : $data['is_visible'];
                $page->save();

                $this->view->message('Page created', 'success');
                $this->_redirect('/' . $page->name);
            }
        }

        $this->view->form = $form;
    }

    public function leagueplayersAction()
    {
        $session = new Zend_Session_Namespace('admin_league_move');
        $state = $this->getRequest()->getUserParam('state');

        unset($session->$state);
        $prevState = $this->getPrev($state);
        if(empty($session->$prevState) && $prevState != $state) {
            $this->_redirect('admin/league_players/' . $prevState);
        }

        $form = new Form_LeagueMove($state);

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['back'])) {
                $this->_redirect('admin/league_players/' . $prevState);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                if($state == 'done') {
                    $leagueMemberTable = new Model_DbTable_LeagueMember();
                    $member = $leagueMemberTable->find($session->src_player['league_member_id'])->current();
                    if($member) {
                        $member->league_id = $session->target_league['league_id'];
                        $member->league_team_id = (empty($session->target_team['league_team_id'])) ? null : $session->target_team['league_team_id'];
                        $member->save();
                    }

                    $session->unsetAll();
                    $this->view->message('Player moved', 'success');
                    $this->_redirect('admin/league_players/src_league');

                } else {
                    $session->$state = $data;
                }

                $this->_redirect('admin/league_players/' . $this->getNext($state));
            }
        }

        $this->view->state = $state;
        $this->view->form = $form;
        $this->view->session = $session;
    }

    private function getPrev($state)
    {
        switch($state) {
            case 'src_player':
                return 'src_league';
            case 'target_league':
                return 'src_player';
            case 'target_team':
                return 'target_league';
            case 'done':
                return 'target_team';
            default:
                return 'src_league';
        }
    }

    private function getNext($state)
    {
        switch($state) {
            case 'src_league':
                return 'src_player';
            case 'src_player':
                return 'target_league';
            case 'target_league':
                return 'target_team';
            case 'target_team':
                return 'done';
            default:
                return 'done';
        }
    }
}
