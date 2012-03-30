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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        if($this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tournament/home.js');
        }
        $this->view->section = 'home';

        if($this->getRequest()->isPost()) {
            if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }

            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $post = $this->getRequest()->getPost();
            $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();

            if($tournamentUpdateTable->isUnique($this->view->tournament->id, $post['title'])) {

                $updateId = $tournamentUpdateTable->insert(array(
                    'tournament_id' => $this->view->tournament->id,
                    'title' => $post['title'],
                    'content' => 'Update text here',
                    'posted' => date('Y-m-d H:i:s'),
                ));

                $this->view->message('Tournament update created successfully.');
                echo Zend_Json::encode(array('result' => 'success', 'url' => $this->view->baseUrl() . '/tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/updateedit/' . $updateId));
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Name Already Exists'));
                return;
            }
        }

        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $this->view->updates = $tournamentUpdateTable->fetchUpdates($this->view->tournament->id);
    }

    public function homeeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/homeedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->section = 'home';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }


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

    public function updateeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/homeedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->section = 'home';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }

        $updateId = $this->getRequest()->getUserParam('update_id');
        $form = new Form_TournamentEdit($this->view->tournament->id, 'update', $updateId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
                $update = $tournamentUpdateTable->find($updateId)->current();
                $update->title = $data['title'];
                $update->content = $data['content'];
                $update->save();

                $this->view->message('Tournament update successfully updated.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function bidAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/bid.css');
        $this->view->section = 'bid';

        $form = new Form_TournamentEdit($this->view->tournament->id, 'bidsubmit');
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                // insert bid into the database
                $tournamentTeamTable = new Model_DbTable_TournamentTeam();
                if($tournamentTeamTable->isUnique($this->view->tournament->id, $data['name'], $data['division'])) {
                    $team = $tournamentTeamTable->createRow();
                    $team->tournament_id = $this->view->tournament->id;
                    $team->name = ucwords($data['name']);
                    $team->city = $data['city'];
                    $team->state = $data['state'];
                    $team->contact_name = $data['contact_name'];
                    $team->contact_phone = $data['contact_phone'];
                    $team->contact_email = $data['contact_email'];
                    $team->division = $data['division'];
                    $team->accepted = 0;
                    $team->paid = 0;
                    $team->save();

                    $tournamentDivisionTable = new Model_DbTable_TournamentDivision();
                    $division = $tournamentDivisionTable->find($data['division'])->current();


                    $this->view->message('Bid for the team `' . $data['name'] . '` in the `' . $division->name . '` division submitted successfully.', 'success');
                    $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/payment');

                } else {
                    $this->view->message('The team `' . $data['name'] . '` in the `' . $division->name . '` division has already submitted a bid.', 'error');
                    $form->populate($post);
                }

            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function bideditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/bidedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tournament/bidedit.js');
        $this->view->section = 'bid';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');
        }

        //edit the cost, due date, mail payment, paypal
        $form = new Form_TournamentEdit($this->view->tournament->id, 'bid');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->tournamentInfo->cost = $data['cost'];
                $this->view->tournamentInfo->bid_due = $data['bid_due'];
                $this->view->tournamentInfo->paypal = (empty($data['paypal'])) ? null : $data['paypal'];
                $this->view->tournamentInfo->mail_payment = (empty($data['mail_payment'])) ? null : $data['mail_payment'];
                $this->view->tournamentInfo->save();

                $this->view->message('Bid setting saved successfully.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');

            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function paymentAction()
    {
        $this->view->section = 'bid';
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





































