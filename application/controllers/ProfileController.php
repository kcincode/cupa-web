<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction()
    {
        $state = $this->getRequest()->getUserParam('state');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/' . $state . '.css');

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userTable = new Model_DbTable_User();
        $this->view->data = $userTable->fetchProfile($this->view->user);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            Zend_Debug::dump($post);
        }
        Zend_Debug::dump($this->view->data);

        $this->renderScript('profile/' . $state . '.phtml');
    }

    public function personaleditAction()
    {
        
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
