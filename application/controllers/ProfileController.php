<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/common.css');
    }

    public function indexAction()
    {
        $state = $this->getRequest()->getUserParam('state');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/' . $state . '.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/profile/' . $state . '.js');

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userTable = new Model_DbTable_User();
        $this->view->data = $userTable->fetchProfile($this->view->user);
        $form = new Form_Profile($this->view->user, $state);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                $this->$state($this->view->user, $data);
            } else {
                $this->view->message('There are errors with your submission.', 'error');
                $form->populate($post);
            }
        }

        $this->view->form = $form;
        $this->view->state = $state;
        $this->renderScript('profile/' . $state . '.phtml');
    }

    public function personal($user, $data)
    {
        foreach($data as $key => $value) {
            $user->username = $data['username'];
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->updated_at = date('Y-m-d H:i:s');
            $user->save();

            $userProfileTable = new Model_DbTable_UserProfile();
            $userProfile = $userProfileTable->find($user->id)->current();
            $userProfile->gender = $data['gender'];
            $userProfile->birthday = $data['birthday'];
            $userProfile->phone = $data['phone'];
            $userProfile->nickname = (empty($data['nickname'])) ? null : $data['nickname'];
            $userProfile->height = $data['height'];
            $userProfile->level = $data['level'];
            $userProfile->experience = $data['experience'];
            $userProfile->save();

        }
        $this->view->message('Personal profile updated.', 'success');
    }

    public function minorsaddAction()
    {

    }

    public function minorseditAction()
    {

    }

    public function publicAction()
    {
        $session = new Zend_Session_Namespace('previous');
        $this->view->previousPage = $session->previousPage;

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/public.css');

        $userId = $this->getRequest()->getUserParam('user_id');
        $userTable = new Model_DbTable_User();
        $user = $userTable->find($userId)->current();

        if(!$user) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->data = $userTable->fetchProfile($user);
    }

    public function passwordAction()
    {
        // action body
    }


}
