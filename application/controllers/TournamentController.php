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
        
        if(empty($this->_year)) {
            $this->_helper->_layout->setLayout('layout');
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $this->view->tournament = $tournamentTable->fetchTournament($this->_year, $this->_name);
        
        //Zend_Debug::dump($this->view->tournament);
        if(!$this->view->tournament) {
            $this->_helper->_layout->setLayout('layout');
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $tournamentInformationTable = new Model_DbTable_TournamentInformation();
        $this->view->tournamentInfo = $tournamentInformationTable->find($this->view->tournament->id)->current();
        //Zend_Debug::dump($this->view->tournamentInfo);
    }

    public function indexAction()
    {
        $this->_forward('home');
    }
    
    public function homeAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/home.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tournament/home.js');
        $this->view->section = 'home';
        
        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $this->view->updates = $tournamentUpdateTable->fetchUpdates($this->view->tournament->id);
    }

    public function homeeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/homeedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->section = 'home';
        
        $form = new Form_TournamentEdit($this->view->tournament->id, 'home');
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }
            
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $this->view->tournamentInfo->description = $data['description'];
                $this->view->tournamentInfo->save();

                $this->view->message('Description updated successfully.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            } else {
                $form->populate($post);
            }
        }
        
        $this->view->form = $form;
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





































