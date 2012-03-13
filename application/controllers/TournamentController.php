<?php

class TournamentController extends Zend_Controller_Action
{
    private $_name;
    private $_year;

    public function init()
    {
        // change the layout file for all pages.
        $this->_helper->_layout->setLayout('tournament');
        
        $this->_name = $this->getRequest()->getUserParam('name');
        $this->_year = $this->getRequest()->getUserParam('year');
        
        $tournamentTable = new Model_DbTable_Tournament();
        
        if(empty($this->_name)) {
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        } else if(empty($this->_year)) {
            $this->_year = $tournamentTable->fetchMostCurrentYear($this->_name);
        }
        
        $this->view->tournament = $tournamentTable->fetchTournament($this->_year, $this->_name);
        if(!$this->view->tournament) {
            $this->_helper->_layout->setLayout('layout');
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $tournamentInformationTable = new Model_DbTable_TournamentInformation();
        $this->view->tournamentInfo = $tournamentInformationTable->find($this->view->tournament->id)->current();
    }

    public function indexAction()
    {
        Zend_Debug::dump($this->view->tournamentInfo);
    }

    public function homeAction()
    {
        // action body
    }

    public function homeaddAction()
    {
        // action body
    }

    public function homeeditAction()
    {
        // action body
    }

    public function bidAction()
    {
        // action body
    }

    public function bideditAction()
    {
        // action body
    }

    public function teamsAction()
    {
        // action body
    }

    public function teamsaddAction()
    {
        // action body
    }

    public function teamseditAction()
    {
        // action body
    }

    public function scheduleAction()
    {
        // action body
    }

    public function scheduleeditAction()
    {
        // action body
    }

    public function directionsAction()
    {
        // action body
    }

    public function directionseditAction()
    {
        // action body
    }

    public function lodgingAction()
    {
        // action body
    }

    public function lodgingeditAction()
    {
        // action body
    }

    public function lodgingaddAction()
    {
        // action body
    }

    public function contactAction()
    {
        // action body
    }

    public function contactaddAction()
    {
        // action body
    }

    public function contacteditAction()
    {
        // action body
    }


}





































