<?php

class TournamentController extends Zend_Controller_Action
{
    protected $_name;
    protected $_year;

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
            $this->_year = $tournamentTable->fetchMostCurrentYear($this->_name, ($this->view->hasRole('admin') or $this->view->hasRole('manager')));
        }

        if(empty($this->_year)) {
            $this->_helper->_layout->setLayout('layout');
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->tournament = $tournamentTable->fetchTournament($this->_year, $this->_name);
        $tournamentTmp = $tournamentTable->fetchTournament($this->_year, $this->_name, true);
        if(empty($this->view->tournament) and ($this->view->hasRole('admin') || $this->view->hasRole('manager') || $this->view->isTournamentAdmin($tournamentTmp->id))) {
            $this->view->tournament = $tournamentTable->fetchTournament($this->_year, $this->_name, true);
        }

        if(!$this->view->tournament) {
            $this->_helper->_layout->setLayout('layout');
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $tournamentInformationTable = new Model_DbTable_TournamentInformation();
        $this->view->tournamentInfo = $tournamentInformationTable->find($this->view->tournament->id)->current();
    }

    public function indexAction()
    {
        $this->_forward('home');
    }

    public function homeAction()
    {
        $this->view->section = 'home';

        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $this->view->updates = $tournamentUpdateTable->fetchUpdates($this->view->tournament->id);
    }

    public function updateaddAction()
    {
        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $form = new Form_TournamentEdit($this->view->tournament->id, 'update');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
                $tournamentUpdateTable->insert(array(
                    'tournament_id' => $this->view->tournament->id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'posted' => date('Y-m-d H:i:s'),
                ));

                $this->view->message('Tournament update created.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }
        }

        $this->view->form = $form;
    }

    public function homeeditAction()
    {
        $this->view->section = 'home';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
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

                $this->view->message('Description updated.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function updateeditAction()
    {
        $this->view->section = 'home';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

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

                $this->view->message('Tournament update modified', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }
        }

        $this->view->form = $form;
    }

    public function updatedeleteAction()
    {
        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
        }

        $updateId = $this->getRequest()->getUserParam('update_id');

        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $update = $tournamentUpdateTable->find($updateId)->current();
        $update->delete();
        $this->view->message('Tournament update removed', 'success');
        $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
    }

    public function bidAction()
    {
        $this->view->section = 'bid';

        $form = new Form_TournamentEdit($this->view->tournament->id, 'bidsubmit');
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // insert bid into the database
                $tournamentTeamTable = new Model_DbTable_TournamentTeam();
                $tournamentDivisionTable = new Model_DbTable_TournamentDivision();
                $division = $tournamentDivisionTable->find($data['division'])->current();

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

                    $mail = new Zend_Mail();
                    $mail->setFrom('webmaster@cincyultimate.org');
                    $mail->setSubject('[' . $this->view->tournament->year . ' ' . $this->view->tournament->display_name . ' Bid]');
                    $body = '';
                    $tournamentMemberTable = new Model_DbTable_TournamentMember();
                    foreach($tournamentMemberTable->fetchAllDirectors($this->view->tournament->id) as $director) {
                        if(APPLICATION_ENV == 'production') {
                            if(!empty($director->email)) {
                                $mail->addTo($director->email);
                            } else {
                                $userTable = new Model_DbTable_User();
                                $user = $userTable->find($director->user_id)->current();
                                $mail->addTo($user->email);
                            }
                        } else {
                            if(!empty($director->email)) {
                                $body .= $director->email . "\r\n";
                            } else {
                                $userTable = new Model_DbTable_User();
                                $user = $userTable->find($director->user_id)->current();
                                $body .= $user->email . "\r\n";
                            }
                        }
                    }
                    $body .= "\r\nTournament Directors,\r\n";
                    $body .= "  A team has submitted a bid to the tournament.  Details below:\r\n\r\n";
                    foreach($team as $key => $value) {
                        if(!in_array($key, array('id', 'tournament_id', 'accepted', 'paid'))) {
                            if($key == 'division') {
                                $body .= 'Division: ' . $division->name . "\r\n";
                            } else {
                                $body .= ucwords(str_replace('_', ' ', $key)) . ': ' . $value . "\r\n";
                            }
                        }
                    }
                    $body .= "\r\n\r\nThanks,\r\nCUPA Tournament System";
                    $mail->setBodyText($body);

                    $this->view->message('Bid for the team `' . $data['name'] . '` in the `' . $division->name . '` division submitted', 'success');

                    if(APPLICATION_ENV == 'production') {
                        $mail->send();
                        $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/payment');
                    } else {
                        Zend_Debug::dump($mail);
                    }
                } else {
                    $this->view->message('The team `' . $data['name'] . '` in the `' . $division->name . '` division has already submitted a bid.', 'error');
                }
            }
        }

        $this->view->form = $form;
    }

    public function bideditAction()
    {
        $this->view->section = 'bid';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        //edit the cost, due date, mail payment, paypal
        $form = new Form_TournamentEdit($this->view->tournament->id, 'bid');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->tournamentInfo->cost = $data['cost'];
                $this->view->tournamentInfo->bid_due = date('Y-m-d H:i:s', strtotime($data['bid_due']));
                $this->view->tournamentInfo->paypal = (empty($data['paypal'])) ? null : $data['paypal'];
                $this->view->tournamentInfo->mail_payment = (empty($data['mail_payment'])) ? null : $data['mail_payment'];
                $this->view->tournamentInfo->save();

                $this->view->message('Bid setting saved', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/bid');
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function paymentAction()
    {
        $this->view->section = 'bid';
    }

    public function teamsAction()
    {
        $this->view->section = 'teams';

        $tournamentTeamTable = new Model_DbTable_TournamentTeam();
        $this->view->teams = $tournamentTeamTable->fetchAllTeams($this->view->tournament->id);

        $tournamentDivisionTable = new Model_DbTable_TournamentDivision();
        $this->view->divisions = $tournamentDivisionTable->fetchDivisions();
    }

    public function teamaddAction()
    {
        $this->view->section = 'teams';

        $form = new Form_TournamentEdit($this->view->tournament->id, 'team');

        if($this->getRequest()->isPost()) {
            if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
            }

            $post = $this->getRequest()->getPost();

            if($form->isValid($post)) {
                $data = $form->getValues();

                $tournamentTeamTable = new Model_DbTable_TournamentTeam();
                $teamId = $tournamentTeamTable->createTeam($this->view->tournament->id, $data);

                $this->view->message('Basic team created', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
            }
        }

        $this->view->form = $form;
    }

    public function teamdeleteAction()
    {
        $teamId = $this->getRequest()->getUserParam('team_id');

        if(!is_numeric($teamId) or !$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
        }

        $tournamentTeamTable = new Model_DbTable_TournamentTeam();
        $team = $tournamentTeamTable->find($teamId)->current();
        if($team) {
            $team->delete();
            $this->view->message('Team deleted', 'success');
        }

        $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
    }

    public function teameditAction()
    {
        $this->view->section = 'teams';
        $teamId = $this->getRequest()->getUserParam('team_id');

        if(!is_numeric($teamId) or !$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
        }

        $tournamentTeamTable = new Model_DbTable_TournamentTeam();
        $team = $tournamentTeamTable->find($teamId)->current();
        $form = new Form_TournamentEdit($this->view->tournament->id, 'team', $teamId);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $tournamentTeamTable->updateValues($teamId, $data);

                $this->view->message('Updated team', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/teams');
            }
        }

        $this->view->form = $form;
        $this->view->team = $team;
    }

    public function scheduleAction()
    {
        $this->view->section = 'schedule';
    }

    public function scheduleeditAction()
    {
        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/schedule');
        }

        $this->view->section = 'schedule';
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $form = new Form_TournamentEdit($this->view->tournament->id, 'schedule');


        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/schedule');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->tournamentInfo->scorereporter_link = (!empty($data['scorereporter_link'])) ? $data['scorereporter_link'] : null;
                $this->view->tournamentInfo->schedule_text = $data['schedule_text'];
                $this->view->tournamentInfo->save();

                $this->view->message('Schedule updated.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/schedule');
            }
        }

        $this->view->form = $form;
    }

    public function locationAction()
    {
        $this->view->section = 'location';
    }

    public function locationeditAction()
    {
        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/location');
        }

        $this->view->section = 'location';

        $form = new Form_TournamentEdit($this->view->tournament->id, 'location');
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/location');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $this->view->tournamentInfo->location = $data['location'];
                $this->view->tournamentInfo->location_street = $data['location_street'];
                $this->view->tournamentInfo->location_city = $data['location_city'];
                $this->view->tournamentInfo->location_state = $data['location_state'];
                $this->view->tournamentInfo->location_zip = $data['location_zip'];
                $this->view->tournamentInfo->save();

                $this->view->message('Location updated', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/location');
            }
        }

        $this->view->form = $form;
    }

    public function lodgingAction()
    {
        $this->view->section = 'lodging';

        $tournamentLodgingTable = new Model_DbTable_TournamentLodging();
        $this->view->lodging = $tournamentLodgingTable->fetchAllLodgings($this->view->tournament->id);
    }

    public function lodgingaddAction()
    {
        $this->view->section = 'lodging';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
        }

        $form = new Form_TournamentEdit($this->view->tournament->id, 'lodging');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $tournamentLodgingTable = new Model_DbTable_TournamentLodging();

                $lodging = $tournamentLodgingTable->createRow();
                $lodging->tournament_id = $this->view->tournament->id;
                $lodging->title = $data['title'];
                $lodging->street = $data['street'];
                $lodging->city = $data['city'];
                $lodging->state = $data['state'];
                $lodging->zip = $data['zip'];
                $lodging->phone = (empty($data['phone'])) ? null : $data['phone'];
                $lodging->link = (empty($data['link'])) ? null : $data['link'];
                $lodging->other = (empty($data['other'])) ? null : $data['other'];
                $lodging->save();

                $this->view->message('Lodging created.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
            }
        }

        $this->view->form = $form;
    }

    public function lodgingeditAction()
    {
        $this->view->section = 'lodging';

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
        }

        $lodgingId = $this->getRequest()->getUserParam('lodging_id');
        $form = new Form_TournamentEdit($this->view->tournament->id, 'lodging', $lodgingId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $tournamentLodgingTable = new Model_DbTable_TournamentLodging();
                $lodging = $tournamentLodgingTable->find($lodgingId)->current();

                $lodging->title = $data['title'];
                $lodging->street = $data['street'];
                $lodging->city = $data['city'];
                $lodging->state = $data['state'];
                $lodging->zip = $data['zip'];
                $lodging->phone = (empty($data['phone'])) ? null : $data['phone'];
                $lodging->link = (empty($data['link'])) ? null : $data['link'];
                $lodging->other = (empty($data['other'])) ? null : $data['other'];

                $lodging->save();
                $this->view->message('Lodging updated.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
            }
        }

        $this->view->form = $form;
    }

    public function lodgingdeleteAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
        }

        $lodgingId = $this->getRequest()->getUserParam('lodging_id');

        $tournamentLodgingTable = new Model_DbTable_TournamentLodging();
        $lodging = $tournamentLodgingTable->find($lodgingId)->current();
        if($lodging) {
            $lodging->delete();
            $this->view->message('Lodging deleted.', 'success');
        }

        $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/lodging');
    }

    public function contactAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/contact.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tournament/contact.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->section = 'contact';
        $tournamentMemberTable = new Model_DbTable_TournamentMember();

        if($this->getRequest()->isPost()) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $post = $this->getRequest()->getPost();

            if(isset($post['user_id'])) {
                $userTable = new Model_DbTable_User();
                $user = $userTable->find($post['user_id'])->current();
                if($user) {
                    $contactId = $tournamentMemberTable->addMember($this->view->tournament->id, $post['user_id']);

                    $this->view->message('Contact created.', 'success');
                    echo Zend_Json::encode(array('result' => 'success', 'url' => $this->view->baseUrl() . '/tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contactedit/' . $contactId));
                    return;
                } else {
                    echo Zend_Json::encode(array('result' => 'error', 'message' => 'User not valid'));
                    return;
                }
            }
        }

        $userTable = new Model_DbTable_User();
        $this->view->users = $userTable->fetchAllUsers();
        $this->view->members = $tournamentMemberTable->fetchAllMembers($this->view->tournament->id);
    }

    public function contacteditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/tournament/contact.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tournament/contactedit.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->section = 'contact';

        $contactId = $this->getRequest()->getUserParam('contact_id');

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
        }

        $tournamentMemberTable = new Model_DbTable_TournamentMember();
        $member = $tournamentMemberTable->find($contactId)->current();
        $form = new Form_TournamentEdit($this->view->tournament->id, 'contact', $contactId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                if($data['user_id'] != 0) {
                    // user user id and email if set
                    $member->user_id = $data['user_id'];
                    $member->name = null;
                    $member->email = (isset($data['email'])) ? $data['email'] : null;
                    $member->save()
                            ;
                    $this->view->message('Tournament contact updated', 'success');
                    $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
                } else if($data['user_id'] == 0 and (!empty($data['name']) and !empty($data['email']))) {
                    // use name and email
                    $member->user_id = null;
                    $member->name = $data['name'];
                    $member->email = $data['email'];
                    $member->save();

                    $this->view->message('Tournament contact updated', 'success');
                    $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
                } else {
                    $this->view->message('You must either select a user or enter BOTH name and email.', 'error');
                }
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function contactdeleteAction()
    {
        $contactId = $this->getRequest()->getUserParam('contact_id');

        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
        }

        $tournamentMemberTable = new Model_DbTable_TournamentMember();
        $member = $tournamentMemberTable->find($contactId)->current();

        $member->delete();
        $this->view->message('Tournament contact deleted', 'success');
        $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
    }

    public function adminAction()
    {
        if(!$this->view->isTournamentAdmin($this->view->tournament->id)) {
            $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year . '/contact');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datepicker.js');

        $form = new Form_TournamentEdit($this->view->tournament->id, 'admin');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->tournament->display_name = $data['display_name'];
                $this->view->tournament->is_visible = $data['is_visible'];
                $this->view->tournament->use_bid = $data['use_bid'];
                $this->view->tournament->save();

                $this->view->tournamentInfo->start = date('Y-m-d', strtotime($data['start']));
                $this->view->tournamentInfo->end = date('Y-m-d', strtotime($data['end']));
                $this->view->tournamentInfo->save();

                if(!empty($data['image'])) {
                    $destination = APPLICATION_WEBROOT . '/images/tournaments/' . $this->view->tournament->name . '.jpg';
                    $simpleImage = new Model_SimpleImage();
                    $simpleImage->load($_FILES['image']['tmp_name']);
                    $simpleImage->resize(960, 150);
                    $simpleImage->save($destination);
                }

                $this->view->message('Tournament settings updated.', 'success');
                $this->_redirect('tournament/' . $this->view->tournament->name . '/' . $this->view->tournament->year);
            }
        }

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
        $this->view->form = $form;
    }
}
