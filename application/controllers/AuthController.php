<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
    }

    /**
     * Handles the login action where a user can login to the system
     * It will redirect after successful login depeding on the saved 
     * request ojbect.
     * 
     */
    public function loginAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();
        
        // load the css for the login page
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/login.css');
        
        // initialize the Login form
        $form = new Cupa_Form_UserLogin();

        // initialize the log file
        $logger = new Zend_Log(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/logs/login_errors.log'));

        // if the form is submitted
        if($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNoRender(true);
            
            // get the post data
            $data = $this->getRequest()->getPost();
            if(!empty($data['username']) and !empty($data['password'])) {
                // get the user object
                $userTable = new Cupa_Model_DbTable_User();
                
                // try to find the user by email
                $user = $userTable->fetchUserBy('email', $data['username']);
                
                // if they don't exist try by username
                if(empty($user)) {
                    $user = $userTable->fetchUserBy('username', $data['username']);
                }
                
                // check to see if the user exists
                if($user) {
                    $authentication = new Cupa_Model_Authenticate($user);
                    
                    // try the password for authentication
                    if($authentication->authenticate($data['password'])) {
                        // successfull login
                        
                        // update the salt if doesn't exist
                        if(empty($user->salt)) {
                            $user->salt = $userTable->generateUniqueCodeFor('salt');
                            $user->password = sha1($user->salt . $data['password']);
                            $user->save();
                        }
                        
                        // set the user id in the session storage
                        Zend_Auth::getInstance()->getStorage()->write($user->id);
                        
                        // build the data to be sent
                        $data = array('result' => 'Success');
                        echo Zend_Json::encode($data);
                    } else {
                        // failed login
                        
                        // log the message
                        $logger->log("User login failed for `$user->first_name $user->last_name ($user->username)`", Zend_Log::ERR);
                        
                        // increment the login errors count
                        $user->login_errors++;
                        $user->save();
                        
                        // build the data to be sent
                        $data = array('result' => 'Error', 'msg' => 'Invalid Credentials');
                        echo Zend_Json::encode($data);
                    }
                    
                } else {
                    // log the message
                    $logger->log("User login failed for `{$data['username']}` which doesn't exist.", Zend_Log::ERR);

                    // build the data to be sent
                    $data = array('result' => 'Error', 'msg' => 'Invalid Credentials');
                    echo Zend_Json::encode($data);
                }
                
            } else {
                // build the data to be sent
                $data = array('result' => 'Error', 'msg' => 'Please enter all information');
                echo Zend_Json::encode($data);
            }
            
        }
        
        // set the form to the view
        $this->view->form = $form;
    }

    public function logoutAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // clear the authentication identity
        $auth = Zend_Auth::getInstance()->clearIdentity();
        
        $session = new Zend_Session_Namespace('request');
        
        // destroy session data
        Zend_Session::destroy();
    }

    public function registerAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/register.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/auth/register.js');
        
        $form = new Cupa_Form_UserRegister();
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $userTable = new Cupa_Model_DbTable_User();
            $userId = $userTable->createNewUser($post['first_name'], $post['last_name'], $post['email']);
            
            if(is_numeric($userId)) {
                Cupa_Model_Email::sendActivationEmail($userTable->find($userId)->current());
                $this->view->message('Created user in the system, please check your email for activation email.', 'success');
            } else {
                $this->view->message('Could not create user in the system.', 'error');
            }
        }
         
        $this->view->form = $form;
    }
    
    public function checkemailAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $email = $this->getRequest()->getUserParam('email');
        if(isset($email)) {
            $validateEmail = new Zend_Validate_EmailAddress();
            if(!$validateEmail->isValid($email)) {
                echo "invalid";
                return;
            }
            
            $userTable = new Cupa_Model_DbTable_User();
            $user = $userTable->fetchUserBy('email', $email);
            if($user) {
                echo "error";
                return;
            } else {
                echo "ok";
                return;
            }
            
            echo "unknown";
        }
    }

    public function activateAction()
    {
        // action body
    }

    public function resetAction()
    {
        // action body
    }

    public function impersonateAction()
    {
        // action body
    }


}
