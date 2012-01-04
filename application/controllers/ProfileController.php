<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function personalAction()
    {
        // action body
    }

    public function minrorsAction()
    {
        // action body
    }

    public function minorsaddAction()
    {
        // action body
    }

    public function minorseditAction()
    {
        // action body
    }

    public function leagueAction()
    {
        // action body
    }

    public function publicAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/public.css');

        $userId = $this->getRequest()->getUserParam('user_id');
        $userTable = new Cupa_Model_DbTable_User();
        $user = $userTable->find($userId)->current();

        if(!$user) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->data = $userTable->getPublicProfile($user);
    }

    public function passwordAction()
    {
        // action body
    }


}















