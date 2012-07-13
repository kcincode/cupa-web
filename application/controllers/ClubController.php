<?php

class ClubController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$clubId = $this->getRequest()->getUserParam('club_id');
    	$clubTable = new Model_DbTable_Club();
    	$this->view->club = $clubTable->find($clubId)->current();
    	Zend_Debug::dump($this->view->club->toArray());
    }
}