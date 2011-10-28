<?php

class AuthController extends Zend_Controller_Action
{

    /**
     * This will redirect and set the request variable depending on what
     * action is requested.
     * 
     */
    public function init()
    {
        // get the requested action
        $action = $this->getRequest()->getActionName();
        
        if(in_array($action, array('login', 'register', 'activate', 'reset'))) {
            // if user is already logged in redirect to home
            if(Zend_Auth::getInstance()->hasIdentity()) {
                $this->_redirect('/');
            }
        } else if(in_array($action, array('logout', 'impersonate'))) {
            // if user is not logged in redirect to login page
            if(!Zend_Auth::getInstance()->hasIdentity()) {
                // get the request data and save it
                $session = new Zend_Session_Namespace('request');
                $session->request = $this->getRequest();
                $this->_redirect('/login');
            }
        }
    }

    /**
     * Handles the login action where a user can login to the system
     * It will redirect after successful login depeding on the saved 
     * request ojbect.
     * 
     */
    public function loginAction()
    {
        // load the css for the login page
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/login.css');
        
        // initialize the Login form
        $form = new Cupa_Form_UserLogin();

        // initialize the log file
        $logger = new Zend_Log(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/logs/login_errors.log'));

        // if the form is submitted
        if($this->getRequest()->isPost()) {
            // get the post data
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                // if the data is valid get the values
                $data = $form->getValues();
                
                // get the user object
                $user = $userTable->fetchUserBy('email', $data['username']);
                
                // check to see if the user exists
                if($user) {
                    $authentication = new Cupa_Model_Authenticate($user);
                    
                    // try the password for authentication
                    if($authentication->authenticate($data['password'])) {
                        // successfull login
                        
                        // update the salt if doesn't exist
                        if(empty($user->salt)) {
                            $user->salt = $userTable->generateUniqueCodeFor('salt');
                            $user->save();
                        }
                        
                        // set the user id in the session storage
                        Zend_Auth::getInstance()->getStorage()->write($user->id);
                        
                        // get the redirect request if there is one
                        $session = new Zend_Session_Namespace('request');
                        if(!empty($session->request)) {
                            // redirect to the give request path
                            $this->_redirect($session->request->getPathInfo());
                            $session->unsetAll();
                        } else {
                            // redirect to home page if request doesn't exist
                            $this->_redirect('/');
                        }
                    } else {
                        // failed login
                        
                        // display message
                        $this->view->message('Invalid credentials', 'error');

                        // log the message
                        $logger->log("User login failed for `$user->first_name $user->last_name ($user->username)`", Zend_Log::ERR);
                        
                        // increment the login errors count
                        $user->login_errors++;
                        $user->save();
                    }
                    
                } else {
                    // display message
                    $this->view->message('Invalid credentials', 'error');
                    
                    // log the message
                    $logger->log("User login failed for `{$data['usernmae']}` which doesn't exist.", Zend_Log::ERR);
                }
                
            } else {
                // display form errors
                $form->populate($post);
            }
            
        }
        
        // set the form to the view
        $this->view->form = $form;
    }

    public function logoutAction()
    {
        // action body
    }

    public function registerAction()
    {
        // action body
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
