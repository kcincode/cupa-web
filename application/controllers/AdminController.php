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
                $mail->addTo($account['email']);
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
}
