<?php

class ClubController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    	$this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/club/index.css');

    	$this->view->headScript()->appendFile($this->view->baseUrl() . '/js/club/index.js');

    	$clubId = $this->getRequest()->getUserParam('club_id');
    	$clubTable = new Model_DbTable_Club();
    	$this->view->club = $clubTable->find($clubId)->current();

    	$clubMemberTable = new Model_DbTable_ClubMember();
    	$this->view->years = $clubMemberTable->fetchAllMemberByYear($clubId);
    }
}