<?php

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('auth');
        } else {
            if(!$this->view->hasRole('admin') and !$this->view->hasRole('manager') and !$this->view->hasRole('volunteer') and !$this->view->isLeagueDirector()) {
                $this->view->message('You do not have access to the management features.');
                $this->_redirect('/');
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
    }

    public function indexAction()
    {
    }

    public function browserAction()
    {
        if(!$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

        $userAccessLogTable = new Model_DbTable_UserAccessLog();
        $this->view->overall = $userAccessLogTable->fetchReportData('all');
        $this->view->month = $userAccessLogTable->fetchReportData('month');

    }

    public function authAction()
    {
    }

    public function duplicatesAction()
    {
        if(!$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

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
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin') and !$this->view->isLeagueDirector()) {
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
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }
        
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
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

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
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }
        
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
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

        $form = new Form_Page();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            if(isset($post['cancel'])) {
                $this->_redirect('admin/page');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $page = $pageTable->createRow();
                $page->name = $data['name'];
                $page->title = $data['title'];
                $page->name = $data['name'];
                $page->save();

                $this->view->message('User ' . $user->email . ' modified.', 'success');
                $this->_redirect('admin/user/filter/' .  $post['filter'] . '/page/' . $post['page']);
            }
            
        }
        
        $this->view->form = $form;
    }

    public function volunteerAction()
    {
        if(!$this->view->hasRole('manager') and !$this->view->hasRole('admin')) {
            $this->_forward('auth');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/manage/volunteer.css');

        $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $this->view->volunteers = $leagueAnswerTable->fetchAllVolunteers();

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            ////apache_setenv('no-gzip', '1');
            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="CUPA-Volunteers.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "first_name,last_name,email\n";
            foreach($this->view->volunteers as $volunteer) {
                $email = (empty($volunteer['email'])) ? $volunteer['parent_email'] : $volunteer['email'];
                echo "{$volunteer['first_name']},{$volunteer['last_name']},{$email}\n";
            }

            flush();
        }
    }
}
