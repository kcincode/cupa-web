<?php

class AuthController extends Zend_Controller_Action
{
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
        $form = new Form_UserLogin();

        // create link to user_access_logs table
        $userAccessLogTable = new Model_DbTable_UserAccessLog();

        // if the form is submitted
        if($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNoRender(true);

            // get the post data
            $data = $this->getRequest()->getPost();
            if(!empty($data['username']) and !empty($data['password'])) {
                // get the user object
                $userTable = new Model_DbTable_User();

                // try to find the user by email
                $user = $userTable->fetchUserBy('email', $data['username']);

                // if they don't exist try by username
                if(empty($user)) {
                    $user = $userTable->fetchUserBy('username', $data['username']);
                }

                // check to see if the user exists
                if($user) {
                    $authentication = new Model_Authenticate($user);

                    // try the password for authentication
                    if($authentication->authenticate($data['password'])) {
                        // successfull login

                        // check to see if the user is active
                        if(!$user->is_active) {
                            // log success to db
                            $userAccessLogTable->log($user->username, 'login-failed', "User login succeeded for `$user->first_name $user->last_name ($user->username)` but they are disabled.");

                            // build the data to be sent
                            $data = array('result' => 'Error', 'msg' => 'Your account is disabled.');
                            echo Zend_Json::encode($data);

                            return;
                        }

                        // update the salt if doesn't exist
                        if(empty($user->salt)) {
                            $user->salt = $userTable->generateUniqueCodeFor('salt');
                            $user->password = sha1($user->salt . $data['password']);
                            $user->save();
                        }

                        // set the user id in the session storage
                        Zend_Auth::getInstance()->getStorage()->write($user->id);

                        // log the success
                        $userAccessLogTable->log($user->username, 'login-success');

                        // build the data to be sent
                        $data = array('result' => 'Success');
                        echo Zend_Json::encode($data);
                    } else {
                        // failed login

                        // log the message
                        $userAccessLogTable->log($user->username, 'login-failed', "Invalid Credentials.");

                        // increment the login errors count
                        $user->login_errors++;
                        $user->save();

                        // build the data to be sent
                        $data = array('result' => 'Error', 'msg' => 'Invalid Credentials');
                        echo Zend_Json::encode($data);
                    }

                } else {
                    // log the message
                    $userAccessLogTable->log($user->username, 'login-failed', "Username does not exist.");

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

        $session = new Zend_Session_Namespace('adminuser');
        if($session->oldUser) {
            Zend_Auth::getInstance()->getStorage()->write($session->oldUser);
            $session->unsetAll();
            return;
        }

        // log the logout
        $userAccessLogTable = new Model_DbTable_UserAccessLog();
        $userAccessLogTable->log($this->view->user->username, 'logout');

        // clear the authentication identity
        $auth = Zend_Auth::getInstance()->clearIdentity();

        // destroy session data
        Zend_Session::destroy();
    }

    public function registerAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/register.css');

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/auth/register.js');

        $form = new Form_UserRegister();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $userTable = new Model_DbTable_User();
            $userId = $userTable->createNewUser($post['first_name'], $post['last_name'], $post['email']);

            if(is_numeric($userId)) {
                Model_Email::sendActivationEmail($userTable->find($userId)->current());
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

            $userTable = new Model_DbTable_User();
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/activate.css');

        $code = $this->getRequest()->getUserParam('code');
        $form = new Form_UserActivation();
        if(!empty($code)) {
            $userTable = new Model_DbTable_User();

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                if($form->isValid($post)) {
                    $user = $userTable->fetchUserBy('email', $post['email']);
                    if($user and $user->activation_code == $code) {
                        $userId = $userTable->updateUserPasswordFromCode($code, $post['password']);
                        if($userId) {
                            $user->activated_at = date('Y-m-d H:i:s');
                            $user->last_login = date('Y-m-d H:i:s');
                            $user->is_active = 1;
                            $user->save();
                            $this->view->message('Activated your account, you may now login with the password you entered.');
                            $this->_redirect('/');
                        } else {
                            $this->view->message('An error occured please try again, if it persists please contact the webmaster.', 'error');
                        }
                    } else {
                        $this->view->message('The email address entered does not match the expected email.', 'error');
                    }
                } else {
                    $form->populate($post);
                }
            }

            $user = $userTable->fetchUserBy('activation_code', $code);
            if(!$user) {
                $error = 'Invalid Code, please contact the webmaster if you think this is a problem.';
            } else {
                if(!empty($user->activated_at)) {
                    $error = 'User acount has already been activated, please contact the webmaster.';
                } else if($user->expires_at < date('Y-m-d H:i:s')) {
                    $error = 'Activation code has expired, please contact the webmaster.';
                } else {
                    $this->view->activateUser = $user;
                    $error = null;
                }
            }

            $this->view->form = $form;
        } else {
            $error = 'Invalid Code, please contact the webmaster if you think this is a problem.';

        }

        if(isset($error)) {
            $this->view->message($error, 'error');
            $this->_redirect('/contact');
        }

    }

    public function resetAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/reset.css');

        $code = $this->getRequest()->getUserParam('code');
        $form = new Form_UserActivation();

        $userPasswordResetTable = new Model_DbTable_UserPasswordReset();
        $userTable = new Model_DbTable_User();
        if(!empty($code)) {
            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                if($form->isValid($post)) {
                    $passwordReset = $userPasswordResetTable->fetchByCode($code);
                    if($passwordReset) {
                        $user = $userTable->find($passwordReset->user_id)->current();
                        if($post['email'] == $user->email) {
                            $userId = $userTable->updateUserPasswordFromId($user->id, $post['password']);
                            if($userId) {
                                $passwordReset->completed_at = date('Y-m-d H:i:s');
                                $passwordReset->save();
                                $this->view->message('Password reset, you may now login with that password.');
                                $this->_redirect('/');
                            } else {
                                $this->view->message('An error occured please try again, if it persists please contact the webmaster.', 'error');
                            }
                        } else {
                            $this->view->message('The email address entered does not match the expected email.', 'error');
                        }
                    } else {
                        $error = 'Invalid Code, please contact the webmaster if you think this is a problem.';
                    }
                } else {
                    $form->populate();
                }
            }

            $passwordReset = $userPasswordResetTable->fetchByCode($code);
            if(!$passwordReset) {
                $error = 'Invalid Code, please contact the webmaster if you think this is a problem.';
            } else {
                if(!empty($passwordReset->completed_at)) {
                    $error = 'This code has already been used, please try to request a reset again.';
                } else if($passwordReset->expires_at < date('Y-m-d H:i:s')) {
                    $error = 'Reset code has expired, please resquest another reset.';
                } else {
                    $this->view->resetUser = $passwordReset;
                    $error = null;
                }
            }

            $this->view->form = $form;

        } else {
            $error = 'Invalid Code, please contact the webmaster if you think this is a problem.';
        }

        if(isset($error)) {
            $this->view->message($error, 'error');
            $this->_redirect('/contact');
        }
    }

    public function forgotAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/auth/forgot.css');
        $form = new Form_UserForgotPassword();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $userTable = new Model_DbTable_User();
                $user = $userTable->fetchUserBy('email', $post['email']);
                if($user) {
                    $userPasswordResetTable = new Model_DbTable_UserPasswordReset();
                    $passwordReset = $userPasswordResetTable->createRow();
                    $passwordReset->code = $userPasswordResetTable->generateUniqueCode();
                    $passwordReset->user_id = $user->id;
                    $passwordReset->requested_at = date('Y-m-d H:i:s');
                    $passwordReset->expires_at = date('Y-m-d H:i:s', time() + 604800);
                    $passwordReset->completed_at = null;
                    $passwordReset->save();
                    Model_Email::sendPasswordResetEmail($user, $passwordReset);
                    $this->view->message("An email has been sent to `{$post['email']}` with the password reset link.", 'success');
                } else {
                    $this->view->message("The email `{$post['email']}` does not exist in the system.", 'error');
                }
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function impersonateAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $userRoleTable = new Model_DbTable_UserRole();
        if(Zend_Auth::getInstance()->hasIdentity() and $this->view->hasRole('admin')) {
            $user = $this->getRequest()->getUserParam('user');
            $oldUserId = $this->view->user->id;
            $session = new Zend_Session_Namespace('adminuser');

            $userTable = new Model_DbTable_User();
            if(is_numeric($user)) {
                $userObj = $userTable->find($user)->current();
            } else {
                $userObj = $userTable->fetchUserBy('username', $user);
            }

            if(isset($userObj->id)) {
                Zend_Debug::dump($userObj->toArray());
                Zend_Auth::getInstance()->getStorage()->write($userObj->id);
                $this->view->message("Impersonating user `{$userObj->first_name} {$userObj->last_name}` successful.", 'succes');
                $session->oldUser = $oldUserId;
                $this->_redirect('/');
            } else {
                $this->view->message('Invalid user specified.', 'error');
                $this->_redirect('/');
            }
        } else {
            $this->view->message('You do not have access to impersonate a user.', 'error');
        }

        $this->_redirect('/');
    }


}
