<?php

class LeagueController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/leagues.css');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        if($leagueId) {
            $leagueInformationTable = new Model_DbTable_LeagueInformation();
            $leagueTable = new Model_DbTable_League();
            $this->view->league = $leagueTable->find($leagueId)->current();
            if($this->view->league) {
                $this->view->information = $leagueInformationTable->fetchInformation($leagueId);
                $this->view->userTeams = ($this->view->information->user_teams == 1) ? true : false;

                if(!$this->view->userTeams and $this->getRequest()->getActionName() == 'userteams') {
                    $this->_redirect('league/' . $leagueId);
                }
            }
        }
    }

    public function indexAction()
    {
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $pageTable = new Model_DbTable_Page();

        $this->view->page = $pageTable->fetchBy('name', 'leagues');
        $this->view->links = $leagueSeasonTable->generateLinks();
        $this->view->leagues = $leagueSeasonTable->fetchAllSeasons();
    }

    public function seasonmoveAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $weight = $this->getRequest()->getUserParam('weight');
        $seasonId = $this->getRequest()->getUserParam('season_id');

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $leagueSeasonTable->moveSeason($seasonId, $weight);

        $this->_redirect('leagues');
    }

    public function seasoneditAction()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $this->view->page = $page;

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $seasonId = $this->getRequest()->getUserParam('season_id');
        $this->view->season = $leagueSeasonTable->find($seasonId)->current();

        $form = new Form_LeagueSeasonEdit($this->view->season);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->season->name = $data['name'];
                $this->view->season->when = $data['when'];
                $this->view->season->information = $data['information'];
                $this->view->season->save();

                $this->view->message("Season `{$data['name']}` updated", 'success');
                $this->_redirect('leagues');
            }
        }

        $this->view->form = $form;
    }

    public function seasonaddAction()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        $this->view->page = $page;
        $form = new Form_LeagueSeasonEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $leagueSeasonTable = new Model_DbTable_LeagueSeason();
                $season = $leagueSeasonTable->createRow();
                $season->name = $data['name'];
                $season->when = $data['when'];
                $season->information = $data['information'];
                $season->weight = $leagueSeasonTable->fetchNextWeight();
                $season->save();

                $this->view->message('Season created');
                $this->_redirect('leagues');
            }
        }

        $this->view->form = $form;
    }

    public function seasondeleteAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $seasonId = $this->getRequest()->getUserParam('season_id');
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $where = $leagueSeasonTable->getAdapter()->quoteInto('id = ?', $seasonId);
        $leagueSeasonTable->delete($where);

        $this->_redirect('leagues');
    }

    public function pageAction()
    {
        $pageTable = new Model_DbTable_Page();
        $leagueTable = new Model_DbTable_League();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $season = $this->getRequest()->getUserParam('type');
        $this->view->season = $season;

        $leagueName = $this->getRequest()->getUserParam('name');
        $this->view->slug = $leagueName;

        $this->view->page = $pageTable->fetchBy('name', $season . '_league');
        $this->view->links = $leagueSeasonTable->generateLinks();
        $this->view->leagues = $leagueTable->fetchCurrentLeaguesBySeason($season, $this->view->isViewable('leagues_edit'), $this->view->isViewable('leagues_delete'));

        if(count($this->view->leagues) != 0) {
            if($leagueName == 'default') {
                $this->view->league = $this->view->leagues[0];
            } else {
                foreach($this->view->leagues as $league) {
                    if($this->view->slugify($this->view->leaguename($league['id'], true, false, false, true)) == $leagueName) {
                        $this->view->league = $league;
                        break;
                    }
                }

                if(empty($this->view->league)) {
                    $this->_redirect('leagues/' . $season);
                }
            }

            if($this->view->league['is_archived'] == 1) {
                $this->view->message('This league page has been archived and therefore is not viewable to users.', 'error');
            }
        } else {
            $this->_redirect('leagues');
        }
    }

    public function archiveAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $league = $leagueTable->find($leagueId)->current();
        $league->is_archived = ($league->is_archived == 1) ? 0 : 1;
        $league->save();

        $season = $leagueSeasonTable->fetchName($league->season);

        $this->_redirect('leagues/' . $season . '/' . $this->view->slugify($this->view->leaguename($league['id'], true, false, false, true)));
    }

    public function pageeditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $leagueInformationTable = new Model_DbTable_LeagueInformation();
        $this->view->league = $leagueTable->fetchLeagueData($leagueId);
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league['season']);

        $this->view->page = $pageTable->fetchBy('name', $this->view->season . '_league');

        if(empty($this->view->league)) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $form = new Form_LeagueEdit($leagueId, 'league');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $league = $leagueTable->find($leagueId)->current();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);

                $league->year = $data['year'];
                $league->season = $data['season'];
                $league->day = $data['day'];

                $leagueInformation->is_youth = $data['is_youth'];
                $leagueInformation->is_pods = $data['is_pods'];
                $leagueInformation->is_hat = $data['is_hat'];
                $leagueInformation->is_clinic = $data['is_clinic'];
                $leagueInformation->user_teams = $data['user_teams'];
                $leagueInformation->contact_email = $data['contact_email'];
                $leagueInformation->save();

                if($leagueInformation->user_teams == 1) {
                    $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
                    $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();

                    $question = $leagueQuestionTable->fetchQuestion('user_teams');
                    if($question) {
                        $leagueQuestionListTable->addQuestionToLeague($league->id, $question->id, 1, -15);
                    }
                }

                $league->visible_from = date('Y-m-d H:i:s', strtotime($data['visible_from']));
                $league->name = $data['name'];
                $league->save();

                $this->view->message('League data saved', 'success');
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function pageinformationeditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $this->view->league = $leagueTable->fetchLeagueData($leagueId);
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league['season']);

        $this->view->page = $pageTable->fetchBy('name', $this->view->season . '_league');

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $form = new Form_LeagueEdit($leagueId, 'information');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }

            // disable the fields if hidden
            if($post['tournament_ignore']) {
                $form->getElement('tournament_name')->setRequired(false);
                $form->getElement('tournament_map_link')->setRequired(false);
                $form->getElement('tournament_address_street')->setRequired(false);
                $form->getElement('tournament_address_city')->setRequired(false);
                $form->getElement('tournament_address_state')->setRequired(false);
                $form->getElement('tournament_address_zip')->setRequired(false);
                $form->getElement('tournament_start')->setRequired(false);
                $form->getElement('tournament_end')->setRequired(false);
            }

            if($post['draft_ignore']) {
                    $form->getElement('draft_name')->setRequired(false);
                    $form->getElement('draft_map_link')->setRequired(false);
                    $form->getElement('draft_address_street')->setRequired(false);
                    $form->getElement('draft_address_city')->setRequired(false);
                    $form->getElement('draft_address_state')->setRequired(false);
                    $form->getElement('draft_address_zip')->setRequired(false);
                    $form->getElement('draft_start')->setRequired(false);
                    $form->getElement('draft_end')->setRequired(false);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $leagueMemberTable = new Model_DbTable_LeagueMember();

                // remove all of the directors that are not in the list
                $dbDirectors = array();
                foreach($leagueMemberTable->fetchAllByType($leagueId, 'director') as $director) {
                    if(!in_array($director->user_id, array_values($data['directors']))) {
                        $director->delete();
                    } else {
                        $dbDirectors[] = $director->user_id;
                    }
                }

                // add the directors that are not in the DB
                foreach($data['directors'] as $directorId) {
                    if(!in_array($directorId, $dbDirectors)) {
                        $leagueMember = $leagueMemberTable->createRow();
                        $leagueMember->league_id = $leagueId;
                        $leagueMember->user_id = $directorId;
                        $leagueMember->position = 'director';
                        $leagueMember->league_team_id = null;
                        $leagueMember->paid = 0;
                        $leagueMember->release = 0;
                        $leagueMember->created_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_by = $this->view->user->id;
                        $leagueMember->save();
                    }
                }

                $leagueLocationTable = new Model_DbTable_LeagueLocation();
                $league = $leagueLocationTable->fetchByType($leagueId, 'league');
                $league->location = $data['league_name'];
                $league->map_link = $data['league_map_link'];
                $league->photo_link = (empty($data['league_photo_link'])) ? null : $data['league_photo_link'];
                $league->address_street = $data['league_address_street'];
                $league->address_city = $data['league_address_city'];
                $league->address_state = $data['league_address_state'];
                $league->address_zip = $data['league_address_zip'];
                $league->start = date('Y-m-d H:i:s', strtotime($data['league_start']));
                $league->end = date('Y-m-d H:i:s', strtotime($data['league_end']));
                $league->save();

                $tournament = $leagueLocationTable->fetchByType($leagueId, 'tournament');
                if($tournament) {
                    if($data['tournament_ignore'] == 1) {
                        $tournament->delete();
                    } else {
                        $tournament->type = 'tournament';
                        $tournament->location = $data['tournament_name'];
                        $tournament->map_link = $data['tournament_map_link'];
                        $tournament->photo_link = (empty($data['tournament_photo_link'])) ? null : $data['tournament_photo_link'];
                        $tournament->address_street = $data['tournament_address_street'];
                        $tournament->address_city = $data['tournament_address_city'];
                        $tournament->address_state = $data['tournament_address_state'];
                        $tournament->address_zip = $data['tournament_address_zip'];
                        $tournament->start = date('Y-m-d H:i:s', strtotime($data['tournament_start']));
                        $tournament->end = date('Y-m-d H:i:s', strtotime($data['tournament_end']));
                        $tournament->save();
                    }
                } else if($data['tournament_ignore'] == 0) {
                    $tournament = $leagueLocationTable->createRow();
                    $tournament->league_id = $leagueId;
                    $tournament->type = 'tournament';
                    $tournament->location = $data['tournament_name'];
                    $tournament->map_link = $data['tournament_map_link'];
                    $tournament->photo_link = (empty($data['tournament_photo_link'])) ? null : $data['tournament_photo_link'];
                    $tournament->address_street = $data['tournament_address_street'];
                    $tournament->address_city = $data['tournament_address_city'];
                    $tournament->address_state = $data['tournament_address_state'];
                    $tournament->address_zip = $data['tournament_address_zip'];
                    $tournament->start = date('Y-m-d H:i:s', strtotime($data['tournament_start']));
                    $tournament->end = date('Y-m-d H:i:s', strtotime($data['tournament_end']));
                    $tournament->save();
                }


                $draft = $leagueLocationTable->fetchByType($leagueId, 'draft');
                if($draft) {
                    if($data['draft_ignore'] == 1) {
                        $draft->delete();
                    } else {
                        $draft->type = 'draft';
                        $draft->location = $data['draft_name'];
                        $draft->map_link = $data['draft_map_link'];
                        $draft->photo_link = (empty($data['draft_photo_link'])) ? null : $data['draft_photo_link'];
                        $draft->address_street = $data['draft_address_street'];
                        $draft->address_city = $data['draft_address_city'];
                        $draft->address_state = $data['draft_address_state'];
                        $draft->address_zip = $data['draft_address_zip'];
                        $draft->start = date('Y-m-d H:i:s', strtotime($data['draft_start']));
                        $draft->end = date('Y-m-d H:i:s', strtotime($data['draft_end']));
                        $draft->save();
                    }
                } else if($data['draft_ignore'] == 0) {
                    $draft = $leagueLocationTable->createRow();
                    $draft->league_id = $leagueId;
                    $draft->type = 'draft';
                    $draft->location = $data['draft_name'];
                    $draft->map_link = $data['draft_map_link'];
                    $draft->photo_link = (empty($data['draft_photo_link'])) ? null : $data['draft_photo_link'];
                    $draft->address_street = $data['draft_address_street'];
                    $draft->address_city = $data['draft_address_city'];
                    $draft->address_state = $data['draft_address_state'];
                    $draft->address_zip = $data['draft_address_zip'];
                    $draft->start = date('Y-m-d H:i:s', strtotime($data['draft_start']));
                    $draft->end = date('Y-m-d H:i:s', strtotime($data['draft_end']));
                    $draft->save();
                }

                $this->view->message('League Information updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, todayBtn: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->headScript()->appendScript('$(".draft").blur(function(){ if($(this).val() != "") { $("#draft_ignore").prop("checked", false); } });');
        $this->view->headScript()->appendScript('$(".tournament").blur(function(){ if($(this).val() != "") { $("#tournament_ignore").prop("checked", false); } });');

        $this->view->form = $form;
    }

    public function pageregistrationeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $this->view->league = $leagueTable->fetchLeagueData($leagueId);
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league['season']);

        $this->view->page = $pageTable->fetchBy('name', $this->view->season . '_league');

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_LeagueEdit($leagueId, 'registration');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }

            if(empty($post['total_players'])) {
                $form->getElement('total_players')->setRequired(false);
                $form->getElement('male_players')->setRequired(true);
                $form->getElement('female_players')->setRequired(true);
            } else {
                $form->getElement('male_players')->setRequired(false);
                $form->getElement('female_players')->setRequired(false);
                $form->getElement('total_players')->setRequired(true);
            }

            if($this->view->league['information']['user_teams'] == 0) {
                $form->getElement('teams')->setRequired(false);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $league = $leagueTable->find($leagueId)->current();
                $league->registration_begin = date('Y-m-d H:i:s', strtotime($data['registration_begin'] . ':00'));
                $league->registration_end = date('Y-m-d H:i:s', strtotime($data['registration_end'] . ':00'));
                $league->save();

                $leagueLimitTable = new Model_DbTable_LeagueLimit();
                $leagueLimit = $leagueLimitTable->fetchLimits($leagueId);

                if($data['limit_select'] == 1) {
                    $leagueLimit->male_players = (empty($data['male_players'])) ? null : $data['male_players'];
                    $leagueLimit->female_players = (empty($data['female_players'])) ? null : $data['female_players'];
                    $leagueLimit->total_players = null;
                } else {
                    $leagueLimit->male_players = null;
                    $leagueLimit->female_players = null;
                    $leagueLimit->total_players = (empty($data['total_players'])) ? null : $data['total_players'];
                }

                $leagueLimit->teams = (empty($data['teams'])) ? null : $data['teams'];
                $leagueLimit->save();

                $leagueInformationTable = new Model_DbTable_LeagueInformation();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);
                $leagueInformation->paypal_code = (empty($data['paypal_code'])) ? null : $data['paypal_code'];
                $leagueInformation->cost = $data['cost'];
                $leagueInformation->save();

                $this->view->message('League registration updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, todayBtn: true, minuteStep: 15, format: \'yyyy-mm-dd hh:ii\' });');
        $this->view->form = $form;
    }

    public function pageregistrationquestionseditAction()
    {
        $this->view->requiredQuestions = array('new_player', 'comments');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $this->view->league = $leagueTable->fetchLeagueData($leagueId);
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league['season']);

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_LeagueEdit($leagueId, 'questions');
        $addQuestionForm = new Form_LeagueQuestionAdd($leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($this->view->league['id'], true, false, false, true)));
            }

            if($addQuestionForm->isValid($post)) {
                $data = $addQuestionForm->getValues();

                $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
                $leagueQuestionListTable->addQuestionToLeague($leagueId, $data['question'], 1);

                $this->view->message('Question added', 'success');
                $this->_redirect('league/' . $this->view->league['id'] . '/edit_registration_questions');
            } else {
                $this->view->message('You must select a valid question to add', 'error');
            }

        }

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();

        $this->view->questions = $leagueQuestionTable->fetchAllQuestionsFromLeague($leagueId);
        $this->view->form = $form;
        $this->view->addQuestionForm = $addQuestionForm;
    }

    public function togglequestionrequiredAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $questionId = $this->getRequest()->getUserParam('question_id');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
        $leagueQuestionListTable->toggleRequired($leagueId, $questionId);

        $this->_redirect('league/' . $leagueId . '/edit_registration_questions');
    }

    public function movequestionAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $questionId = $this->getRequest()->getUserParam('question_id');
        $direction = $this->getRequest()->getUserParam('direction');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
        $leagueQuestionListTable->move($leagueId, $questionId, $direction);

        $this->_redirect('league/' . $leagueId . '/edit_registration_questions');
    }

    public function questionremoveAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $questionId = $this->getRequest()->getUserParam('question_id');
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->fetchLeagueData($leagueId);
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $season = $leagueSeasonTable->fetchName($league['season']);

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');

        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        if(!$question) {
            $this->_redirect('league/' . $leagueId . '/edit_registration_questions');
        }

        $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
        $leagueQuestionListTable->removeQuestionFromLeague($leagueId, $questionId);

        $this->view->message('Question `' . $question->name . '` removed from the league.', 'success');
        $this->_redirect('league/' . $leagueId . '/edit_registration_questions');
    }

    public function pagedescriptioneditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $this->view->league = $leagueTable->fetchLeagueData($leagueId);
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league['season']);

        $this->view->page = $pageTable->fetchBy('name', $this->view->season . '_league');

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $form = new Form_LeagueEdit($leagueId, 'description');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($leagueId, true, false, false, true)));
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $league = $leagueTable->find($leagueId)->current();
                $league->info = (empty($data['info'])) ? null : $data['info'];
                $league->save();

                $leagueInformationTable = new Model_DbTable_LeagueInformation();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);
                $leagueInformation->description = $data['description'];
                $leagueInformation->save();

                $this->view->message('League description updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '/' . $this->view->slugify($this->view->leaguename($leagueId, true, false, false, true)));
            }
        }

        $this->view->form = $form;
    }

    public function pageaddAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $season = $this->getRequest()->getUserParam('season');

        $form = new Form_LeagueCreate($season);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $season);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $leagueTable = new Model_DbTable_League();
                $id = $leagueTable->createLeague($data);

                if(is_numeric($id)) {
                    $this->view->message('League created');
                    $this->_redirect('leagues/' . $season . '/' . $this->view->slugify($this->view->leaguename($id, true, false, false, true)));
                }
                $this->view->message('Could not create the league', 'error');
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function teamsAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = 'league/' . $leagueId;

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $this->view->teams = $leagueTeamTable->fetchAllTeams($leagueId);
    }

    public function loadplayersAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();

        $teamId = $this->getRequest()->getUserParam('team_id');

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $leagueInformationTable = new Model_DbTable_LeagueInformation();

        $this->view->team = $leagueTeamTable->find($teamId)->current();
        $this->view->information = $leagueInformationTable->fetchInformation($this->view->team->league_id);
        if($this->view->information->is_youth) {
            $leagueMemberYouthTable = new Model_DbTable_LeagueMemberYouth();
            $this->view->players = $leagueMemberYouthTable->fetchAllPlayerData($this->view->team->league_id, $teamId);
        } else {
            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $this->view->players = $leagueMemberTable->fetchAllPlayerData($this->view->team->league_id, $teamId);
        }
    }

    public function teamsaddAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-colorpicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-colorpicker.js');

        $form = new Form_LeagueTeamEdit($leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $leagueTeamTable = new Model_DbTable_LeagueTeam();
                if($leagueTeamTable->isUnique($leagueId, $data['name'])) {
                    $id = $leagueTeamTable->insert(array(
                        'name' => $data['name'],
                        'league_id' => $leagueId,
                        'color' => $data['color'],
                        'color_code' => $data['color_code'],
                        'text_code' => '#000000',
                        'final_rank' => null,
                    ));

                    if(is_numeric($id)) {
                        $team = $leagueTeamTable->find($id)->current();
                        if($this->view->information->is_youth) {
                            $userTable = new Model_DbTable_User();
                            $userProfileTable = new Model_DbTable_UserProfile();
                            $leagueMemberYouthTable = new Model_DbTable_LeagueMemberYouth();

                            foreach($data['coaches'] as $coachId) {
                                $user = $userTable->find($coachId)->current();
                                $userProfile = $userProfileTable->find($coachId)->current();

                                $leagueMember = $leagueMemberYouthTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->first_name = $user->first_name;
                                $leagueMember->last_name = $user->last_name;
                                $leagueMember->email = $user->email;
                                $leagueMember->phone = $userProfile->phone;
                                $leagueMember->position = 'coach';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->save();
                            }

                            foreach($data['asst_coaches'] as $coachId) {
                                $user = $userTable->find($coachId)->current();
                                $userProfile = $userProfileTable->find($coachId)->current();

                                $leagueMember = $leagueMemberYouthTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->first_name = $user->first_name;
                                $leagueMember->last_name = $user->last_name;
                                $leagueMember->email = $user->email;
                                $leagueMember->phone = $userProfile->phone;
                                $leagueMember->position = 'assistant_coach';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->save();
                            }
                        } else {
                            $leagueMemberTable = new Model_DbTable_LeagueMember();

                            foreach($data['captains'] as $captainId) {
                                $leagueMember = $leagueMemberTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->user_id = $captainId;
                                $leagueMember->position = 'captain';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->paid = 0;
                                $leagueMember->release = 0;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->save();
                            }
                        }

                        $this->view->message('Team created', 'success');
                        $this->_redirect('league/' . $leagueId);
                    }
                } else {
                    $this->view->message('Duplicate Team exists.', 'error');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->headScript()->appendScript('$(".colorpicker").colorpicker();');
        $this->view->form = $form;
    }

    public function teamseditAction()
    {
        $teamId = $this->getRequest()->getUserParam('team_id');

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $team = $leagueTeamTable->find($teamId)->current();

        if(!$team) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $isCaptain = ($this->view->isViewable('league_team_edit') && !$this->view->isViewable('league_team_add')) ? true : false;

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-colorpicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-colorpicker.js');

        $form = new Form_LeagueTeamEdit($team->league_id, $team, $isCaptain);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $team->league_id);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                if(!$isCaptain) {
                    if($this->view->information->is_youth) {
                        // generate coaches array
                        $userTable = new Model_DbTable_User();
                        $userProfileTable = new Model_DbTable_UserProfile();
                        $leagueMemberYouthTable = new Model_DbTable_LeagueMemberYouth();

                        $coaches = array();
                        foreach($data['coaches'] as $coach) {
                            $user = $userTable->find($coach)->current();
                            $coaches[] = $user->first_name . '-' . $user->last_name . '-' . $user->email;
                        }

                        // remove all of the coaches that are no longer in the list
                        $coachesDb = array();
                        foreach($leagueMemberYouthTable->fetchAllByType($team->league_id, 'coach', $team->id) as $coach) {
                            if(!in_array($coach->first_name . '-' . $coach->last_name . '-' . $coach->email, array_values($coaches))) {
                                $coach->delete();
                            } else {
                                $coachesDb[] = $coach->first_name . '-' . $coach->last_name . '-' . $coach->email;
                            }
                        }

                        // add all the coaches that aren't in
                        foreach($data['coaches'] as $captainId) {
                            $user = $userTable->find($captainId)->current();
                            $userProfile = $userProfileTable->find($captainId)->current();
                            $key = $user->first_name . '-' . $user->last_name . '-' . $user->email;

                            if(!in_array($key, $coachesDb)) {
                                $leagueMember = $leagueMemberYouthTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->first_name = $user->first_name;
                                $leagueMember->last_name = $user->last_name;
                                $leagueMember->email = $user->email;
                                $leagueMember->phone = $userProfile->phone;
                                $leagueMember->position = 'coach';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->save();
                            }
                        }


                        $coaches = array();
                        foreach($data['asst_coaches'] as $coach) {
                            $user = $userTable->find($coach)->current();
                            $coaches[] = $user->first_name . '-' . $user->last_name . '-' . $user->email;
                        }

                        // remove all of the coaches that are no longer in the list
                        $coachesDb = array();
                        foreach($leagueMemberYouthTable->fetchAllByType($team->league_id, 'assistant_coach', $team->id) as $coach) {
                            if(!in_array($coach->first_name . '-' . $coach->last_name . '-' . $coach->email, array_values($coaches))) {
                                $coach->delete();
                            } else {
                                $coachesDb[] = $coach->first_name . '-' . $coach->last_name . '-' . $coach->email;
                            }
                        }

                        // add all the coaches that aren't in
                        foreach($data['asst_coaches'] as $captainId) {
                            $user = $userTable->find($captainId)->current();
                            $userProfile = $userProfileTable->find($captainId)->current();
                            $key = $user->first_name . '-' . $user->last_name . '-' . $user->email;

                            if(!in_array($key, $coachesDb)) {
                                $leagueMember = $leagueMemberYouthTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->first_name = $user->first_name;
                                $leagueMember->last_name = $user->last_name;
                                $leagueMember->email = $user->email;
                                $leagueMember->phone = $userProfile->phone;
                                $leagueMember->position = 'assistant_coach';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->save();
                            }
                        }

                    } else {
                        $leagueMemberTable = new Model_DbTable_LeagueMember();

                        // remove all of the directors that are not in the list
                        $dbCaptains = array();
                        foreach($leagueMemberTable->fetchAllByType($team->league_id, 'captain', $team->id) as $captain) {
                            if(!in_array($captain->user_id, array_values($data['captains']))) {
                                $captain->delete();
                            } else {
                                $dbCaptains[] = $captain->user_id;
                            }
                        }

                        // add the directors that are not in the DB
                        foreach($data['captains'] as $captainId) {
                            if(!in_array($captainId, $dbCaptains)) {
                                $leagueMember = $leagueMemberTable->createRow();
                                $leagueMember->league_id = $team->league_id;
                                $leagueMember->user_id = $captainId;
                                $leagueMember->position = 'captain';
                                $leagueMember->league_team_id = $team->id;
                                $leagueMember->paid = 0;
                                $leagueMember->release = 0;
                                $leagueMember->created_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_at = date('Y-m-d H:i:s');
                                $leagueMember->modified_by = $this->view->user->id;
                                $leagueMember->save();
                            }
                        }

                        $team->name = $data['name'];
                        $team->color = $data['color'];
                        $team->color_code = $data['color_code'];
                        $team->final_rank = (empty($data['final_rank'])) ? null : $data['final_rank'];
                        $team->save();
                    }
                }

                if(!empty($_FILES['logo']['tmp_name'])) {
                    $image = new Model_SimpleImage();
                    $image->load($_FILES['logo']['tmp_name']);
                    $image->resize(85,85);
                    $image->save(APPLICATION_WEBROOT . '/images/team_logos/' . $teamId . '.jpg');
                }

                $this->view->message("Team `{$team->name}` updated", 'success');
                $this->_redirect('league/' . $team->league_id);
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->headScript()->appendScript('$(".colorpicker").colorpicker();');
        $this->view->form = $form;
        $this->view->team = $team;
    }

    public function teamdeleteAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $teamId = $this->getRequest()->getUserParam('team_id');

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $team = $leagueTeamTable->find($teamId)->current();

        if(!$team) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // save the league Id and delete the team
        $leagueId = $team->league_id;
        $name = $team->name;
        $team->delete();

        $this->view->message("Deleted the team `$name`", 'success');

        // redirect to teams page
        $this->_redirect('league/' . $leagueId);
    }

    public function scheduleAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueGameTable = new Model_DbTable_LeagueGame();
        $this->view->games = $leagueGameTable->fetchSchedule($leagueId);

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $this->view->teams = $leagueTeamTable->fetchAllTeams($leagueId);
    }

    public function scheduleaddAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $form = new Form_LeagueScheduleEdit(null, $leagueId);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId . '/schedule');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $data['day'] = date('Y-m-d H:i:s', strtotime($data['day']));

                $leagueGameTable = new Model_DbTable_LeagueGame();
                $leagueGameDataTable = new Model_DbTable_LeagueGameData();
                $game = $leagueGameTable->fetchGame($leagueId, $data['day'], $data['week'], $data['field']);

                if($leagueGameDataTable->isUnique($game, $data['home_team'], $data['away_team'])) {
                    $game = $leagueGameTable->fetchGame($leagueId, $data['day'], $data['week'], $data['field']);

                    if(!$game) {
                        $game = $leagueGameTable->createRow();
                        $game->league_id = $leagueId;
                    }

                    $game->day = $data['day'];
                    $game->week = $data['week'];
                    $game->field = $data['field'];
                    $game->save();

                    $homeTeam = $leagueGameDataTable->fetchGameData($game->id, 'home');
                    $awayTeam = $leagueGameDataTable->fetchGameData($game->id, 'away');

                    if(!$homeTeam) {
                        $homeTeam = $leagueGameDataTable->createRow();
                        $awayTeam = $leagueGameDataTable->createRow();

                        $homeTeam->league_game_id = $game->id;
                        $awayTeam->league_game_id = $game->id;
                        $homeTeam->type = 'home';
                        $awayTeam->type = 'away';
                    }

                    $homeTeam->league_team_id = $data['home_team'];
                    $awayTeam->league_team_id = $data['away_team'];

                    $homeTeam->score = 0;
                    $awayTeam->score = 0;

                    $homeTeam->save();
                    $awayTeam->save();

                    $this->view->message('Created game.', 'success');
                    $this->_redirect('league/' . $leagueId . '/schedule');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, todayBtn: true, minuteStep: 15, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function scheduleeditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $gameId = $this->getRequest()->getUserParam('game_id');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Model_DbTable_LeagueGame();
        $game = $leagueGameTable->find($gameId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$game) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_LeagueScheduleEdit($gameId, $leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId . '/schedule');
            }

            if($form->isValid(($post))) {
                $data = $form->getValues();
                $data['day'] = date('Y-m-d H:i:s', strtotime($data['day']));

                $leagueGameDataTable = new Model_DbTable_LeagueGameData();
                $homeGameData = $leagueGameDataTable->fetchGameData($gameId, 'home');
                $awayGameData = $leagueGameDataTable->fetchGameData($gameId, 'away');

                $game->day = $data['day'];
                $game->week = $data['week'];
                $game->field = $data['field'];
                $game->save();

                $homeGameData->league_team_id = $data['home_team'];
                $awayGameData->league_team_id = $data['away_team'];
                $homeGameData->score = $data['home_score'];
                $awayGameData->score = $data['away_score'];
                $homeGameData->score = $data['home_score'];
                $awayGameData->score = $data['away_score'];

                $homeGameData->save();
                $awayGameData->save();

                $this->view->message('Game data updated', 'success');
                $this->_redirect('league/' . $leagueId . '/schedule');
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, todayBtn: true, minuteStep: 15, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function scheduledeleteAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $gameId = $this->getRequest()->getUserParam('game_id');

        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Model_DbTable_LeagueGame();
        $game = $leagueGameTable->find($gameId)->current();

        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$game) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $leagueGameDataTable = new Model_DbTable_LeagueGameData();
        $leagueGameData = $leagueGameDataTable->fetchGameData($gameId);

        if(count($leagueGameData)) {
            $leagueGameData[0]->delete();
            $leagueGameData[1]->delete();
        }

        $this->view->message('Game deleted.', 'success');
        $this->_redirect('league/' . $leagueId . '/schedule');
    }

    public function schedulegenerateAction()
    {
        $session = new Zend_Session_Namespace('schedule_generation');
        if($this->getRequest()->isGet()) {
            $session->unsetAll();
        }

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Model_DbTable_LeagueGame();
        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->league = $league;

        $form = new Form_GenerateSchedule($league);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId . '/schedule');
            }

            if($form->isValid($post)) {
                $leagueGameDataTable = new Model_DbTable_LeagueGameData();

                // get start time from the league locations
                $leagueLocationTable = new Model_DbTable_LeagueLocation();
                $leagueLocation = $leagueLocationTable->fetchByType($leagueId, 'league');

                $startHour = date('H', strtotime($leagueLocation->start));
                $endHour = date('H', strtotime($leagueLocation->end));
                $dayHours = 24 - ($endHour - $startHour);
                $weekSeconds = 7 * $dayHours * 60 * 60;
                $weeks = ceil((strtotime($leagueLocation->end) - strtotime($leagueLocation->start)) / $weekSeconds);

                $fields = explode(',', $post['number_of_fields']);

                $leagueTeamTable = new Model_DbTable_LeagueTeam();
                $teams = $leagueTeamTable->fetchAllTeams($leagueId)->toArray();
                $numTeams = count($teams);

                $numFields = count($fields);
                if($numFields < floor($numTeams / 2)) {
                    $this->view->message('Not enough fields to play games. You need at least ' . floor($numTeams / 2) . ' fields.', 'warning');
                    $session->unsetAll();
                    $form->populate($post);
                    $this->view->form = $form;
                    return;
                }

                // shuffle team array so that the generation will be different
                shuffle($teams);

                if($post['home_advantage'] != 0) {
                    // reset the fields array with home field as the first item
                    $newFields = array();
                    $newFields[0] = $post['home_field'];
                    foreach($fields as $field) {
                        if($field != $post['home_field']) {
                            $newFields[] = $field;
                        }
                    }
                    $fields = $newFields;
                    unset($newFields);

                    $newTeams = array();
                    $newTeams[0] = array();
                    foreach($teams as $team) {
                        if($team['id'] == $post['home_advantage']) {
                            $newTeams[0] = $team;
                        } else {
                            $newTeams[] = $team;
                        }
                    }
                    $teams = $newTeams;
                    unset($newTeams);
                }

                $results = array();
                for($week = 1; $week <= $weeks; $week++) {
                    $str = $leagueLocation->start . ' +' . $week . ' Weeks';
                    $date = date('Y-m-d H:i:s', strtotime($str));

                    if($post['home_advantage'] != 0) {
                        foreach($fields as $idx => $field) {
                            if($teams[$idx]['id'] == $post['home_advantage']) {
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $post['home_field'],
                                    'away_team' => $teams[$numTeams - $idx - 1]['id'],
                                    'away_name' => $teams[$numTeams - $idx - 1]['name'],
                                    'home_team' => $teams[$idx]['id'],
                                    'home_name' => $teams[$idx]['name'],
                                );
                            } else if ($teams[$numTeams - $idx - 1]['id'] == $post['home_advantage']) {
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $post['home_field'],
                                    'away_team' => $teams[$idx]['id'],
                                    'away_name' => $teams[$idx]['name'],
                                    'home_team' => $teams[$numTeams - $idx - 1]['id'],
                                    'home_name' => $teams[$numTeams - $idx - 1]['name'],
                                );
                            }

                            if($field == $post['home_field']) {
                                continue;
                            }

                            if($idx <= floor($numTeams / 2)) {
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $field,
                                    'away_team' => $teams[$idx]['id'],
                                    'away_name' => $teams[$idx]['name'],
                                    'home_team' => $teams[$numTeams - $idx - 1]['id'],
                                    'home_name' => $teams[$numTeams - $idx - 1]['name'],
                                );
                            } else {
                                // more fields than teams
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $field,
                                    'away_team' => null,
                                    'away_name' => null,
                                    'home_team' => null,
                                    'home_name' => null,
                                );
                            }
                        }
                        $teams = $this->rotateTeams($teams);
                    } else {
                        foreach($fields as $idx =>$field) {
                            if($idx <= floor($numTeams / 2)) {
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $field,
                                    'away_team' => $teams[$idx]['id'],
                                    'away_name' => $teams[$idx]['name'],
                                    'home_team' => $teams[$numTeams - $idx - 1]['id'],
                                    'home_name' => $teams[$numTeams - $idx - 1]['name'],
                                );
                            } else {
                                // more fields than teams
                                $results[$week][] = array(
                                    'date' => $date,
                                    'field' => $field,
                                    'away_team' => null,
                                    'away_name' => null,
                                    'home_team' => null,
                                    'home_name' => null,
                                );
                            }
                        }
                        $teams = $this->rotateTeams($teams);
                    }
                }

                // remove all of the current games for the league
                $leagueGameTable->getAdapter()->query('DELETE FROM league_game WHERE league_id = ' . $leagueId);

                // save the new schedule
                foreach($results as $week => $tmp) {
                    foreach($tmp as $data) {
                        $gameId = $leagueGameTable->createGame($leagueId, $data['date'], $week, $data['field']);
                        $leagueGameDataTable->addGameData($gameId, 'home', $data['home_team']);
                        $leagueGameDataTable->addGameData($gameId, 'away', $data['away_team']);
                    }
                }

                $this->view->message('League schedule generated', 'success');
                $this->_redirect('league/' . $leagueId . '/schedule');
            }
        }

        $this->view->form = $form;
    }

    private function rotateTeams($teams)
    {
        $lastElement = array_pop($teams);
        $firstElement = array_shift($teams);

        array_unshift($teams, $lastElement);
        array_unshift($teams, $firstElement);

        return $teams;
    }

    public function emailAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $form = new Form_LeagueContact($leagueId, $this->view->user, $this->view->isViewable('league_players'));
        $form->getElement('subject')->setValue('[' . $this->view->leaguename($leagueId, true, true, true, true) . '] Information');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if($form->isValid($post)) {
                $leagueMemberTable = new Model_DbTable_LeagueMember();
                $data = $leagueMemberTable->fetchAllEmails($leagueId, $this->view->user, $this->view->isViewable('league_players'));
                $mail = new Zend_Mail();
                $mail->setSubject($post['subject']);
                $mail->setFrom($post['from']);

                // log the email
                $leagueEmailTable = new Model_DbTable_LeagueEmail();
                $leagueEmailTable->log($post, $data);

                foreach($post['to'] as $to) {
                    foreach($data[$to] as $email) {
                        if(empty($email)) {
                            continue;
                        }

                        $mail->clearRecipients();
                        if(APPLICATION_ENV == 'production') {
                            $mail->addTo($email);
                            $mail->setBodyHtml($post['content']);
                        } else {
                            $mail->addTo('kcin1018@gmail.com');
                            $mail->setBodyHtml("TO: $email\r\n\r\n" . $post['content']);
                        }
                        $mail->send();
                    }
                }

                $this->view->message('Email sent.', 'success');
                $this->_redirect('league/' . $leagueId . '/email');
            }
        }

        $this->view->form = $form;
    }

    public function rankingsAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = 'league/' . $leagueId;

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $this->view->teams = $leagueTeamTable->fetchAllTeamRanks($leagueId);
    }

    public function rankingseditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['reset'])) {
                $leagueTeamTable->clearFinalResults($leagueId);
                $this->view->message('League final rankings cleared', 'success');
                $this->_redirect('league/' . $leagueId . '/rankings');
            } else if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId . '/rankings');
            } else {
                foreach($post['rank'] as $teamId => $rank) {
                    $team = $leagueTeamTable->find($teamId)->current();
                    if($team) {
                        $team->final_rank = $rank;
                        $team->save();
                    }
                }
            }

            $this->view->message('League final rankings updated', 'success');
            $this->_redirect('league/' . $leagueId . '/rankings');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = 'league/' . $leagueId;

        $this->view->teams = $leagueTeamTable->fetchAllTeams($leagueId, 'final_rank ASC');
    }

    public function playersAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->players = $leagueMemberTable->fetchPlayerInformation($leagueId);
        $this->view->genderCounts = $leagueMemberTable->fetchAllPlayersByGender($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_players.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);


            echo "first_name,last_name,email";
            $i = 1;
            foreach($this->view->players as $player) {
                if($i == 1) {
                    foreach($player['profile'] as $key => $value) {
                        echo ",$key";
                    }
                    foreach($player['answers'] as $key => $value) {
                        echo ",$key";
                    }
                    echo "\n";
                }

                echo "{$player['first_name']},{$player['last_name']},{$player['email']}";
                foreach($player['profile'] as $key => $value) {
                    echo "," . str_replace(',', ' ', addslashes($value));
                }

                foreach($player['answers'] as $key => $value) {
                    echo "," . str_replace("\r\n", '  ', str_replace(',', ' ', addslashes($value)));
                }
                echo "\n";

                $i++;
            }
            exit();
        }
    }

    public function shirtsAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $this->view->shirts = $leagueAnswerTable->fetchShirts($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            //apache_setenv('no-gzip', '1');
            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_shirts.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "color,Youth Small,Youth Medium,Youth Large,Small,Medium,Large,Extra Large,2X Extra Large\n";

            foreach($this->view->shirts as $color => $shirt) {
                foreach(array('YS', 'YM', 'YL', 'S', 'M', 'L', 'XL', 'XXL') as $size) {
                    $lowSize = strtolower($size);
                    $$lowSize = (isset($shirt[$size])) ? $shirt[$size] : 0;
                }
                echo "{$color},{$ys},{$ym},{$yl},{$s},{$m},{$l},{$xl},{$xxl}\n";
            }
            exit();
        }
    }

    public function emergencyAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->contacts = $leagueMemberTable->fetchAllEmergencyContacts($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            //apache_setenv('no-gzip', '1');
            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_emergency.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "player,contacts\n";

            foreach($this->view->contacts as $userId => $tmp) {
                if($userId) {
                    echo $this->view->fullname($userId);
                    foreach($tmp as $data) {
                        echo ',' . $data['name'] . ' (' . $data['phone'] . ')';
                    }
                    echo "\n";
                }
            }

            flush();
        }
    }

    public function statusAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->all = $this->getRequest()->getUserParam('all');

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->statuses = $leagueMemberTable->fetchPlayerStatuses($leagueId, $this->view->league->year);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_status.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "name,waiver,release,paid,owed\n";

            foreach($this->view->statuses as $status) {
                $waiver = ($status['waiver'] == $this->view->league->year) ? 'Yes' : 'No';
                $release = ($status['release'] == 1) ? 'Yes' : 'No';
                $paid = ($status['paid'] == 1) ? 'Yes' : 'No';
                $balance = (empty($status['balance'])) ? 0 : $status['balance'];
                echo "{$this->view->fullname($status['user_id'])},{$waiver},{$release},{$paid},{$balance}\n";
            }
            exit();
        }
    }

    public function statustoggleAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $userId = $this->getRequest()->getUserParam('user_id');
        $type = $this->getRequest()->getUserParam('type');

        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $member = $leagueMemberTable->fetchMember($leagueId, $userId);

        if($type == 'waiver') {
            $userWaiverTable = new Model_DbTable_UserWaiver();
            $checked = ($userWaiverTable->hasWaiver($userId, $league->year)) ? 'false' : 'true';

            $userWaiverTable->updateWaiver($userId, $league->year, $checked , $this->view->user->id);
            $result = ($checked == 'true') ? $league->year : null;
        } else {
            $member->$type = ($member->$type == 1) ? 0 : 1;
            $member->save();
            $result = $member->$type;
        }

        $status = array(
            $type => $result,
            'user_id' => $userId,
        );

        echo $this->view->generateStatusButton($type, $status, $league);
    }

    public function registerAction()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $key = $this->getRequest()->getUserParam('key');

        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $this->view->season = $leagueSeasonTable->fetchName($this->view->league->season);

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->waitlist = ($this->view->isLeagueRegistrationFull($leagueId)) ? true : false;

        // do registration checks to make sure a user is able to register
        $this->view->registrationMessage = $this->view->getLeagueRegistrationMessage($leagueId);
        if($this->view->registrationMessage !== true) {
            $userTable = new Model_DbTable_User();
            if($this->view->isRegistered($leagueId, $this->view->user->id) && !$userTable->hasMinors($this->view->user->id)) {
                $this->_redirect('/league/' . $leagueId . '/register_success');
            }

            if(!$this->view->waitlist && $key != sha1($this->view->leaguename($leagueId, true, true, true, true))) {
                //$this->view->message($this->view->registrationMessage, 'error');
                $this->renderScript('league/registration-error.phtml');
                return;
            }
        }

        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $state = $this->getRequest()->getUserParam('state');
        $session->waitlist = $this->view->waitlist;
        $session->key = $key;

        if($state != 'user') {
            if($this->view->isRegistered($leagueId, $session->registrantId)) {
                $this->view->message('You have already registered as this user.', 'warning');
                $this->_redirect('/league/' . $leagueId . '/register_success');
            }
        }

        $form = new Form_LeagueRegister($leagueId, $this->view->user->id, $state);
        $this->{'register' . $state}($leagueId, $form);

        $this->view->state = $state;
        $this->view->form = $form;
    }

    private function registerUser($leagueId, &$form)
    {
        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $leagueTable = new Model_DbTable_League();

        $forceRegistration = (!empty($session->key)) ? '/' . $session->key : '';

        $league = $leagueTable->find($leagueId)->current();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $season  = $leagueSeasonTable->fetchName($league->season);

        $userTable = new Model_DbTable_User();
        if(!$userTable->hasMinors($this->view->user->id)) {
            $session->registrantId = $this->view->user->id;

            $this->_redirect('league/' . $leagueId . '/register/personal' . $forceRegistration);
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('leagues/' . $season . '/' . $this->view->slugify($this->view->leaguename($leagueId, true, false, false, true)));
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $session->registrantId = $data['user'];
                $this->_redirect('league/' . $leagueId . '/register/personal' . $forceRegistration);
            }
        }
    }

    private function registerPersonal($leagueId, &$form)
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datepicker.js');

        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $forceRegistration = (!empty($session->key)) ? '/' . $session->key : '';

        $userEmergencyTable = new Model_DbTable_UserEmergency();
        $this->view->contacts = $userEmergencyTable->fetchAllContacts($session->registrantId);
        unset($session->personal);

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');

        // handle post request
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['back'])) {
                $this->_redirect('league/' . $leagueId . '/register/user' . $forceRegistration);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // make sure that the birthday is > 8 yrs old
                list($m, $d, $y) = explode('/', $data['birthday']);
                list($curM, $curD, $curY) = explode('/', date('m/d/Y'));
                if(($curY - $y) < 8) {
                    $form->getElement('birthday')->addError('Birthday is not valid.');
                    return;
                }

                $session->personal = $data;
                $this->_redirect('league/' . $leagueId . '/register/league' . $forceRegistration);
            }
        }

    }

    private function registerLeague($leagueId, &$form)
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/league_register.js');

        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $forceRegistration = (!empty($session->key)) ? '/' . $session->key : '';
        unset($session->league);

        if(empty($session->personal)) {
            $this->_redirect('league/' . $leagueId . '/register/personal' . $forceRegistration);
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['back'])) {
                $this->_redirect('league/' . $leagueId . '/register/personal' . $forceRegistration);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                foreach(array('user_team_new', 'user_team_select') as $key) {
                    if(isset($data[$key])) {
                        $data['user_teams'] = $data[$key];
                        unset($data[$key]);
                    }
                }

                $session->league = $data;
                $this->_redirect('league/' . $leagueId . '/register/done' . $forceRegistration);
            }
        }
    }

    private function registerDone($leagueId, &$form)
    {
        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $forceRegistration = (!empty($session->key)) ? '/' . $session->key : '';
        unset($session->done);

        if(empty($session->league)) {
            $this->_redirect('league/' . $leagueId . '/register/league' . $forceRegistration);
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['back'])) {
                $this->_redirect('league/' . $leagueId . '/register/league' . $forceRegistration);
            } else {
                $this->saveRegistrationData($leagueId, $session);
            }
        }
    }

    private function saveRegistrationData($leagueId, $session)
    {
        if(isset($session->registrantId)) {
            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $member = $leagueMemberTable->fetchMember($leagueId, $session->registrantId, null, ($session->waitlist) ? 'waitlist' : 'player');

            // all data entered save the registrant
            $userTable = new Model_DbTable_User();
            $user = $userTable->find($session->registrantId)->current();
            $userProfileTable = new Model_DbTable_UserProfile();
            $userProfile = $userProfileTable->find($session->registrantId)->current();

            $user->first_name = $session->personal['first_name'];
            $user->last_name = $session->personal['last_name'];

            // only update the user email/phone if they are not a minor
            if(empty($user->parent)) {
                $user->email = $session->personal['email'];
                $userProfile->phone = $session->personal['phone'];
            }
            $user->save();

            $userProfile->gender = $session->personal['gender'];
            $userProfile->birthday = date('Y-m-d', strtotime($session->personal['birthday']));
            $userProfile->nickname = $session->personal['nickname'];
            $userProfile->height = $session->personal['height'];
            $userProfile->level = $session->personal['level'];
            $userProfile->experience = $session->personal['experience'];
            $userProfile->save();

            $userEmergencyTable = new Model_DbTable_UserEmergency();
            for($i = 1; $i < 3; $i++) {
                $contactPhone = $session->personal['contactPhone' . $i];
                $contactName = $session->personal['contactName' . $i];

                $contact = $userEmergencyTable->fetchContact($session->registrantId, $contactPhone);
                $nameData = explode(' ', $contactName);
                if(count($nameData) == 1) {
                    $first = $nameData[0];
                    $last = '';
                } else if(count($nameData) == 2) {
                    $first = $nameData[0];
                    $last = $nameData[1];
                }

                if(!$contact) {
                    $userEmergencyTable->insert(array(
                        'user_id' => $session->registrantId,
                        'first_name' => ucwords(trim($first)),
                        'last_name' => ucwords(trim($last)),
                        'phone' => $contactPhone,
                        'weight' => $i,
                    ));
                } else {
                    $contact->first_name = ucwords(trim($first));
                    $contact->last_name = ucwords(trim($last));
                    $contact->save();
                }
            }

            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $leagueMember = $leagueMemberTable->fetchMember($leagueId, $session->registrantId);
            if(!$leagueMember) {
                $leagueMemberId = $leagueMemberTable->insert(array(
                    'league_id' => $leagueId,
                    'user_id' => $session->registrantId,
                    'position' => ($session->waitlist) ? 'waitlist' : 'player',
                    'league_team_id' => null,
                    'paid' => 0,
                    'release' => ($this->view->displayAge($session->personal['birthday']) >= 18) ? 1 :0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_at' => date('Y-m-d H:i:s'),
                    'modified_by' => $this->view->user->id,
                ));
            } else {
                $leagueMemberId = $leagueMember->id;
            }

            $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
            $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
            foreach($session->league as $questionName => $answer) {
                $question = $leagueQuestionTable->fetchQuestion($questionName);
                if($question) {
                    $leagueAnswerTable->addAnswer($leagueMemberId, $question->id, $answer);
                }
            }

            // redirect to success/payment screen
            $type = ($session->waitlist) ? 'waitlisted' : 'registered';
            $this->view->message('You have successfully ' . $type . ' for ' . $this->view->leaguename($leagueId, true, true, true, true));
        } else {

            $url = $_SERVER['REQUEST_URI'];

            $params = array(
                'get' => $this->getRequest()->getParams(),
                'post' => $this->getRequest()->getPost(),
            );
            $params = Zend_Json::encode($this->_params, true);
            $userId = Zend_Auth::getInstance()->getIdentity();

            $mail = new Zend_Mail();
            $mail->setFrom('no-reply@cincyultimate.org');
            $mail->addTo('webmaster@cincyultimate.org');
            $mail->setSubject('[CUPA] Apllication Error: Registration');
            $mail->setBodyText("Registrant: {$session->registrantId} (" . Zend_Auth::getInstance()->getIdentity() . ")\r\nPersonal:" . print_r($session->personal, true) . "\r\nLeague:" . print_r($session->league, true) . "\r\nUSER ID: $userId\r\nURL: {$url}\r\nPARAMS: {$params}\r\n\r\n");
            $mail->send();
            $this->view->message('There was an error processing your request, please make sure you have entered all data.', 'error');
            return;
        }

        $userWaiverTable = new Model_DbTable_UserWaiver();
        // if the user has not signed a waiver redirect to online waiver
        if($userProfileTable->isEighteenOrOver($session->registrantId) and !$userWaiverTable->hasWaiver($session->registrantId, $leagueId)) {
            $session->unsetAll();
            $this->_redirect('league/' . $leagueId . '/waiver');
        } else if(!$userWaiverTable->hasWaiver($session->registrantId, $leagueId)) {
            $this->view->message('You are not old enough to sign the online waiver.  If this is not true please check your birthday in the system under the MY PROFILE link and update it.', 'warning');
        }

        $session->unsetAll();
        $this->_redirect('league/' . $leagueId . '/register_success');
    }

    public function registersuccessAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // stop if user not logged in
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('league/' . $leagueId . '/register');
        }

        $leagueInformationTable = new Model_DbTable_LeagueInformation();
        $information = $leagueInformationTable->fetchInformation($leagueId);
        $this->view->paypal = $information->paypal_code;
        $this->view->cost = $information->cost;

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $userTable = new Model_DbTable_User();

        $minors = $userTable->fetchAllMinors($this->view->user->id);
        $userIds = array();
        $userIds[] = $this->view->user->id;
        $this->view->hasMinors = false;
        if(count($minors)) {
            foreach($minors as $id => $minor) {
                $userIds[] = $id;
            }
            $this->view->hasMinors = true;
        }

        $this->view->players = $leagueMemberTable->fetchUserRegistrants($leagueId, $userIds);
        if(count($this->view->players) == count($userIds)) {
            $this->view->hasMinors = false;
        }

        if(count($this->view->players) == 0) {
            $this->view->players = $leagueMemberTable->fetchUserWaitlists($leagueId, $userIds);
            if(count($this->view->players) == count($userIds)) {
                $this->view->hasMinors = false;
            }

            if(count($this->view->players) == 0) {
                $this->_redirect('league/' . $leagueId . '/register');
            }
        }
    }

    public function manageAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $teamId = $this->getRequest()->getUserParam('team_id');
        if(!$teamId) {
            $teamId = 0;
        }
        $this->view->teamId = $teamId;

        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $removeForm = new Form_LeagueManage($leagueId, 'remove');
        $addForm = new Form_LeagueManage($leagueId, 'add');

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['add'])) {
                if($addForm->isValid($post)) {
                    $data = $addForm->getValues();

                    foreach($data['user'] as $user) {
                        $leagueMemberTable->addNewPlayer($leagueId, $user);
                    }

                    $this->view->message('Players added to league.', 'success');
                    $this->_redirect('league/' . $leagueId . '/manage');
                }
            } else if(isset($post['remove'])) {
                if($removeForm->isValid($post)) {
                    $data = $removeForm->getValues();

                    foreach($data['user'] as $user) {
                        $leagueMemberTable->removePlayer($user);
                    }

                    $this->view->message('Players removed from league.', 'success');
                    $this->_redirect('league/' . $leagueId . '/manage');
                }
            } else if(isset($post['manage-add'])) {
                unset($post['players']);
                unset($post['manage-clear-team']);

                if(empty($post['available'])) {
                    $this->view->message('You must select at least on player to add', 'error');
                } else if($teamId != 0) {
                    foreach($post['available'] as $leagueMemberId) {
                        $leagueMember = $leagueMemberTable->find($leagueMemberId)->current();
                        $leagueMember->league_team_id = $teamId;
                        $leagueMember->modified_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_by = $this->view->user->id;
                        $leagueMember->save();
                    }
                    $this->view->message('Player(s) added', 'success');
                } else {
                    $this->view->message('You must select a team to add the player(s) to.', 'error');
                }
            } else if(isset($post['manage-remove'])) {
                unset($post['available']);
                unset($post['manage-clear-team']);

                if(empty($post['players'])) {
                    $this->view->message('You must select at least on player to remove', 'error');
                } else {
                    foreach($post['players'] as $leagueMemberId) {
                        $leagueMember = $leagueMemberTable->find($leagueMemberId)->current();
                        $leagueMember->league_team_id = null;
                        $leagueMember->modified_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_by = $this->view->user->id;
                        $leagueMember->save();
                    }
                    $this->view->message('Player(s) removed', 'success');
                }
            } else if(isset($post['manage-clear'])) {
                if(empty($teamId)) {
                    $this->view->message('You must select a team to clear first', 'error');
                } else {
                    $teamMembers = $leagueMemberTable->fetchAllByType($leagueId, 'player', $teamId);
                    foreach($teamMembers as $member) {
                        $leagueMember = $leagueMemberTable->find($member->id)->current();
                        $leagueMember->league_team_id = null;
                        $leagueMember->modified_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_by = $this->view->user->id;
                        $leagueMember->save();
                    }
                    $this->view->message('Team cleared', 'success');
                }
            }
        }

        $key = sha1($this->view->leaguename($leagueId, true, true, true, true));
        $this->view->url = 'http://cincyultimate.org/league/' . $leagueId . '/register/user/' . $key;
        $this->view->teams = $leagueTeamTable->fetchAllTeams($leagueId);
        $this->view->available = $leagueMemberTable->fetchPlayersByTeam($leagueId, null);
        $this->view->teamPlayers = $leagueMemberTable->fetchPlayersByTeam($leagueId, $teamId);
        $this->view->addForm = $addForm;
        $this->view->removeForm = $removeForm;

        $this->view->headScript()->appendScript('$(".select2").select2();');
    }

    public function userteamsAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $this->view->players = $leagueAnswerTable->fetchUserTeamRequests($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            //apache_setenv('no-gzip', '1');
            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_userteams.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "first_name,last_name,requested_team\n";

            foreach($this->view->players as $player) {
                echo "{$player['first_name']},{$player['last_name']},{$player['team']}\n";
            }
            exit();
        }
    }

    public function waiverAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // redirect to the login page if the user is not logged in
        if(!isset($this->view->user)) {
            $this->_redirect('league/' . $leagueId . '/register_success');
        }

        // make sure user is over 18
        $userProfileTable = new Model_DbTable_UserProfile();
        if(!$userProfileTable->isEighteenOrOver($this->view->user->id)) {
            $this->view->message('You are not able to sign a waiver since you are younger than 18.', 'info');
            $this->_redirect('league/' . $leagueId . '/register_success');
        }

        $userWaiverTable = new Model_DbTable_UserWaiver();
        if($userWaiverTable->hasWaiver($this->view->user->id, $this->view->league->year)) {
           $this->view->message('You have already signed a waiver for the ' . $this->view->league->year . ' year.', 'info');
           $this->_redirect('league/' . $leagueId . '/register_success');
        }

        $form = new Form_LeagueWaiver($this->view->user);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->view->message('You disagreed with the waiver, before you may play in this league you must sign a waiver for the year.', 'error');
                $this->_redirect('league/' . $leagueId . '/register_success');
            }

            if($form->isValid($post)) {
                $userWaiverTable->updateWaiver($this->view->user->id, $this->view->league->year, 1, $this->view->user->id);
                $this->view->message('User waiver signed', 'success');
                $this->_redirect('league/' . $leagueId . '/register_success');
            }
        }

        $this->view->form = $form;
    }

    public function waiveryearAction()
    {
        $year = $this->getRequest()->getUserParam('year');

        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find(1)->current();
        $this->view->league->year = $year;

        // redirect to the profile page if user is not logged in
        if(!isset($this->view->user)) {
            $this->_redirect('profile');
        }

        // make sure user is over 18
        $userProfileTable = new Model_DbTable_UserProfile();
        if(!$userProfileTable->isEighteenOrOver($this->view->user->id)) {
            $this->view->message('You are not able to sign a waiver since you are younger than 18.', 'info');
            $this->_redirect('profile');
        }

        $userWaiverTable = new Model_DbTable_UserWaiver();
        if($userWaiverTable->hasWaiver($this->view->user->id, $year)) {
           $this->view->message('You have already signed a waiver for the ' . $year . ' year.', 'info');
           $this->_redirect('profile');
        }

        $form = new Form_LeagueWaiver($this->view->user);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->view->message('You disagreed with the waiver.', 'error');
                $this->_redirect('profile/status');
            }

            if($form->isValid($post)) {
                $userWaiverTable->updateWaiver($this->view->user->id, $year, 1, $this->view->user->id);
                $this->view->message('User waiver signed', 'success');
                $this->_redirect('profile/status');
            }
        }

        $this->view->form = $form;

        $this->renderScript('league/waiver.phtml');
    }

    public function waitlistAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if($this->getRequest()->getParam('add')) {
            // add the player from waitlist to player
            $id = $this->getRequest()->getParam('member');

            $leagueMember = $leagueMemberTable->find($id)->current();
            $leagueMember->position = 'player';
            $leagueMember->modified_at = date('Y-m-d H:i:s');
            $leagueMember->modified_by = $this->view->user->id;
            $leagueMember->save();

            $this->_redirect('league/' . $leagueId . '/waitlist');
        }

        if($this->getRequest()->getParam('remove')) {
            // delete the player from waitlist
            $id = $this->getRequest()->getParam('member');

            $leagueMember = $leagueMemberTable->find($id)->current();
            $leagueMember->delete();

            $this->_redirect('league/' . $leagueId . '/waitlist');
        }

        $this->view->players = $leagueMemberTable->fetchPlayerInformation($leagueId, 'waitlist');
        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            ob_end_clean();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public', FALSE);
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            if(isset($_SERVER['HTTP_USER_AGENT']) and (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                header('Content-Type: application/force-download');
            }
            header('Accept-Ranges: bytes');
            header('Content-Disposition: attachment; filename="' . str_replace(' ', '-', $this->view->leaguename($this->view->league, true, true, true, true)) . '_waitlist.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "first_name,last_name,email";
            $i = 1;
            foreach($this->view->players as $player) {
                if($i == 1) {
                    foreach($player['profile'] as $key => $value) {
                        echo ",$key";
                    }
                    foreach($player['answers'] as $key => $value) {
                        echo ",$key";
                    }
                    echo "\n";
                }

                echo "{$player['first_name']},{$player['last_name']},{$player['email']}";
                foreach($player['profile'] as $key => $value) {
                    echo "," . str_replace(',', ' ', $value);
                }
                foreach($player['answers'] as $key => $value) {
                    echo "," . str_replace("\r\n", '  ', str_replace(',', ' ', $value));
                }
                echo "\n";

                $i++;
            }
            exit();
        }
    }

    public function coachesAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->coaches = $leagueMemberTable->fetchAllByType($leagueId, 'coaches');
    }

    public function coacheditAction()
    {
        $coachId = $this->getRequest()->getUserParam('coach_id');
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $coach = $leagueMemberTable->find($coachId)->current();

        $form = new Form_LeagueCoach($coach);
        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if($form->isValid($post)) {
                $data = $form->getValues();
                Zend_Debug::dump($data);
            }
        }


        $this->view->form = $form;
    }
}
