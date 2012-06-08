<?php

class LeagueController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/common.css');
        $leagueId = $this->getRequest()->getUserParam('league_id');
        if($leagueId) {
            $leagueInformationTable = new Model_DbTable_LeagueInformation();
            $leagueTable = new Model_DbTable_League();
            $this->view->league = $leagueTable->find($leagueId)->current();
            $information = $leagueInformationTable->fetchInformation($leagueId);
            $this->view->userTeams = ($information->user_teams == 1) ? true : false;

            if(!$this->view->userTeams and $this->getRequest()->getActionName() == 'userteams') {
                $this->_redirect('league/' . $leagueId);
            }
        }
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/index.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/index.js');

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
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $this->view->page = $page;

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            $this->_redirect('leagues');
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/index.css');

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $seasonId = $this->getRequest()->getUserParam('season_id');
        $this->view->season = $leagueSeasonTable->find($seasonId)->current();

        $form = new Form_LeagueSeasonEdit();
        $form->loadFromSeason($this->view->season);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                $this->view->season->name = $data['name'];
                $this->view->season->when = $data['when'];
                $this->view->season->information = $data['information'];
                $this->view->season->save();

                $this->view->message("Season `{$data['name']}` updated", 'success');
                $this->_redirect('leagues');
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function seasonaddAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'leagues');
        $this->view->page = $page;

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            return;
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);

            $leagueSeasonTable = new Model_DbTable_LeagueSeason();
            if($leagueSeasonTable->isUnique($post['name'])) {
                $season = $leagueSeasonTable->createRow();
                $season->name = $post['name'];
                $season->when = 'Unknown';
                $season->information = '';
                $season->weight = $leagueSeasonTable->fetchNextWeight();
                $season->save();

                $this->view->message('Season created');
                echo Zend_Json::encode(array('result' => 'success', 'data' => $season->id));
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Season Already Exists'));
                return;
            }
        }
    }

    public function seasondeleteAction()
    {
        if(!$this->view->hasRole('admin')) {
            $this->_redirect('leagues');
        }

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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/page.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/page.js');

        $pageTable = new Model_DbTable_Page();
        $leagueTable = new Model_DbTable_League();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();

        $season = $this->getRequest()->getUserParam('type');
        $this->view->season = $season;

        $this->view->page = $pageTable->fetchBy('name', $season . '_league');
        $this->view->links = $leagueSeasonTable->generateLinks();
        $admin = $this->view->hasRole('admin') or $this->view->hasRole('editor') or $this->view->hasRole('editor', $this->view->page->id);
        $this->view->leagues = $leagueTable->fetchCurrentLeaguesBySeason($season, $admin);
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

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            $this->_redirect('leagues/' . $this->view->season);
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/pageedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/pageedit.js');

        $form = new Form_LeagueEdit();
        $form->loadSection($leagueId, 'league');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                $league = $leagueTable->find($leagueId)->current();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);

                if($this->view->hasRole('admin')) {
                    $league->year = $data['year'];
                    $league->season = $data['season'];
                    $league->day = $data['day'];
                    $league->is_archived = $data['is_archived'];

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
                }

                $league->visible_from = $data['visible_from'];
                $league->name = $data['name'];
                $league->save();

                $this->view->message('League data saved'. 'success');
                $this->_redirect('leagues/' . $this->view->season . '#leagues-' . $leagueId);
            } else {
                $form->populate($post);
            }
        }

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

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            $this->_redirect('leagues/' . $this->view->season);
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/pageedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/pageedit.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');

        $form = new Form_LeagueEdit();
        $form->loadSection($leagueId, 'information');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            // disable the fields if hidden
            if($post['tournament_ignore']) {
                $form->getElement('tournament_name')->setRequired(false);
                $form->getElement('tournament_map_link')->setRequired(false);
                $form->getElement('tournament_address')->setRequired(false);
                $form->getElement('tournament_start')->setRequired(false);
                $form->getElement('tournament_end')->setRequired(false);
            }

            if($post['draft_ignore']) {
                    $form->getElement('draft_name')->setRequired(false);
                    $form->getElement('draft_map_link')->setRequired(false);
                    $form->getElement('draft_address')->setRequired(false);
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

                $league = $leagueTable->find($leagueId)->current();
                $league->info = (empty($data['info'])) ? null : $data['info'];
                $league->save();

                $leagueLocationTable = new Model_DbTable_LeagueLocation();
                $league = $leagueLocationTable->fetchByType($leagueId, 'league');
                $league->location = $data['league_name'];
                $league->map_link = $data['league_map_link'];
                $league->photo_link = (empty($data['league_photo_link'])) ? null : $data['league_photo_link'];
                $matches = array();
                preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $data['league_address'], $matches);
                $league->address_street = $matches[1];
                $league->address_city = $matches[2];
                $league->address_state = $matches[3];
                $league->address_zip = $matches[4];
                $league->start = $data['league_start'];
                $league->end = $data['league_end'];
                $league->save();


                if(!$data['tournament_ignore']) {
                    $tournament = $leagueLocationTable->fetchByType($leagueId, 'tournament');
                    if($tournament) {
                        if($data['tournament_ignore']) {
                            $tournament->delete();
                        } else {
                            $tournament->location = $data['tournament_name'];
                            $tournament->map_link = $data['tournament_map_link'];
                            $tournament->photo_link = (empty($data['tournament_photo_link'])) ? null : $data['tournament_photo_link'];
                            $matches = array();
                            preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $data['tournament_address'], $matches);
                            $tournament->address_street = $matches[1];
                            $tournament->address_city = $matches[2];
                            $tournament->address_state = $matches[3];
                            $tournament->address_zip = $matches[4];
                            $tournament->start = $data['tournament_start'];
                            $tournament->end = $data['tournament_end'];
                            $tournament->save();
                        }
                    } else if(!$data['tournament_ignore']) {
                        $tournament = $leagueLocationTable->createRow();
                        $tournament->location = $data['tournament_name'];
                        $tournament->map_link = $data['tournament_map_link'];
                        $tournament->photo_link = (empty($data['tournament_photo_link'])) ? null : $data['tournament_photo_link'];
                        $matches = array();
                        preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $data['tournament_address'], $matches);
                        $tournament->address_street = $matches[1];
                        $tournament->address_city = $matches[2];
                        $tournament->address_state = $matches[3];
                        $tournament->address_zip = $matches[4];
                        $tournament->start = $data['tournament_start'];
                        $tournament->end = $data['tournament_end'];
                        $tournament->save();
                    }
                }


                if(!$data['draft_ignore']) {
                    $draft = $leagueLocationTable->fetchByType($leagueId, 'draft');
                    if($draft) {
                        if(empty($data['draft_ignore'])) {
                            $draft->delete();
                        } else {
                            $draft->location = $data['draft_name'];
                            $draft->map_link = $data['draft_map_link'];
                            $draft->photo_link = (empty($data['draft_photo_link'])) ? null : $data['draft_photo_link'];
                            $matches = array();
                            preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $data['draft_address'], $matches);
                            $draft->address_street = $matches[1];
                            $draft->address_city = $matches[2];
                            $draft->address_state = $matches[3];
                            $draft->address_zip = $matches[4];
                            $draft->start = $data['draft_start'];
                            $draft->end = $data['draft_end'];
                            $draft->save();
                        }
                    } else if(!$data['draft_ignore']) {
                        $draft = $leagueLocationTable->createRow();
                        $draft->location = $data['draft_name'];
                        $draft->map_link = $data['draft_map_link'];
                        $draft->photo_link = (empty($data['draft_photo_link'])) ? null : $data['draft_photo_link'];
                        $matches = array();
                        preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $data['draft_address'], $matches);
                        $draft->address_street = $matches[1];
                        $draft->address_city = $matches[2];
                        $draft->address_state = $matches[3];
                        $draft->address_zip = $matches[4];
                        $draft->start = $data['draft_start'];
                        $draft->end = $data['draft_end'];
                        $draft->save();
                    }
                }

                $this->view->message('League Information updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '#leagues-' . $leagueId);
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function pageregistrationeditAction()
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

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            $this->_redirect('leagues/' . $this->view->season);
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/pageedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/pageedit.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');

        $form = new Form_LeagueEdit();
        $form->loadSection($leagueId, 'registration');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['new_question'])) {
                if($post['id'] != 0) {
                    $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
                    $leagueQuestionListTable->addQuestionToLeague($leagueId, $post['id'], 1);
                    // disable the layout
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    echo $this->view->baseUrl() . '/league/' . $leagueId . '/edit_registration';
                    return;
                } else {
                    $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
                    $questionId = $leagueQuestionTable->createQuestion($post['name'], 'Placeholder title', $post['type'], null);

                    $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
                    $leagueQuestionListTable->addQuestionToLeague($leagueId, $questionId, 1);

                    // disable the layout
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    echo $this->view->baseUrl() . '/league_question/' . $leagueId . '/' . $questionId;
                    return;
                }
            }


            if($post['limit_select'] == 1) {
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
                $league->registration_begin = $data['registration_begin'];
                $league->registration_end = $data['registration_end'];
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

                $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
                $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();

                foreach($post['question'] as $weight => $questionName) {
                    $leagueQuestion = $leagueQuestionTable->fetchQuestion($questionName);
                    $required = (isset($post['required'][$questionName])) ? 1 : 0;
                    $leagueQuestionListTable->updateQuestionList($leagueId, $leagueQuestion->id, $required, $weight);
                }

                $this->view->message('League registration updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '#leagues-' . $leagueId);

            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $this->view->leagueQuestions = $leagueQuestionTable->fetchAllQuestionsFromLeague($leagueId);
        $this->view->allQuestions = $leagueQuestionTable->fetchAllRemainingQuestions($this->view->leagueQuestions);
    }

    public function questioneditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/questionedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/questionedit.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $questionId = $this->getRequest()->getUserParam('question_id');
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->fetchLeagueData($leagueId);

        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        $this->view->leagueId = $league['id'];

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        if(!$question) {
            $this->_redirect('league/' . $leagueId . '/page_registration');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('leagues');
        }

        $form = new Form_LeagueQuestionEdit($question);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('league/' . $leagueId . '/edit_registration');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $question->name = $data['name'];
                $question->title = $data['title'];
                $question->type = $data['type'];
                $question->answers = (empty($data['answers'])) ? null : $this->convertQuestionAnswers($data['answers']);
                $question->save();

                $this->view->message('Question `' . $question->name . '` updated', 'success');
                $this->_redirect('league/' . $leagueId . '/edit_registration');
            } else {
                $form->populate($post);
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/pageedit.css');

        $this->view->form = $form;
    }

    public function convertQuestionAnswers($answers)
    {
        $lines = explode("\r\n", $answers);

        $data = array();
        foreach($lines as $line) {
            list($key, $value) = explode('::', $line);
            $data[trim($key)] = trim($value);
        }

        return Zend_Json::encode($data);
    }

    public function questionremoveAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $questionId = $this->getRequest()->getUserParam('question_id');
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->fetchLeagueData($leagueId);

        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        if(!$question) {
            $this->_redirect('league/' . $leagueId . '/page_registration');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('leagues');
        }

        $leagueQuestionListTable = new Model_DbTable_LeagueQuestionList();
        $leagueQuestionListTable->removeQuestionFromLeague($leagueId, $questionId);

        $this->view->message('Question `' . $question->name . '` removed from the league.', 'success');
        $this->_redirect('league/' . $leagueId . '/edit_registration');
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

        if(!$this->view->hasRole('admin') and
           !$this->view->hasRole('editor') and
           !$this->view->hasRole('editor', $page->id) ) {
            $this->_redirect('leagues/' . $this->view->season);
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/pageedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');

        $form = new Form_LeagueEdit();
        $form->loadSection($leagueId, 'description');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                $leagueInformationTable = new Model_DbTable_LeagueInformation();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);
                $leagueInformation->description = $data['description'];
                $leagueInformation->save();

                $this->view->message('League description updated', 'success');
                $this->_redirect('leagues/' . $this->view->season . '#leagues-' . $leagueId);

            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;

    }

    public function pageaddAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        $this->view->season = $this->getRequest()->getUserParam('season');

        // disable the layout
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);

            $leagueTable = new Model_DbTable_League();
            if($leagueTable->isUnique($post['year'], $post['season'], $post['day'])) {

                $id = $leagueTable->createBlankLeague($post['year'], $post['season'], $post['day'], null, $this->view->user->id);
                if(is_numeric($id)) {
                    $this->view->message('League created');
                    echo Zend_Json::encode(array('result' => 'success', 'data' => strtolower($post['season'])));
                } else {
                    echo Zend_Json::encode(array('result' => 'error', 'message' => 'Error Creating League'));
                    return;
                }
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'League Already Exists'));
                return;
            }
        }
    }

    public function teamsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/teams.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/teams.js');

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
        $leagueMemberTable = new Model_DbTable_LeagueMember();

        $this->view->team = $leagueTeamTable->find($teamId)->current();
        $this->view->players = $leagueMemberTable->fetchAllPlayerData($this->view->team->league_id, $teamId);
    }

    public function teamsaddAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            // make sure the user should be able to add a team
            if(!$this->view->isLeagueDirector($post['league'])) {
                $this->_redirect('league/' . $post['league']);
            }

            $this->_helper->viewRenderer->setNoRender(true);

            $leagueTeamTable = new Model_DbTable_LeagueTeam();
            if($leagueTeamTable->isUnique($post['league'], $post['name'])) {
                $id = $leagueTeamTable->insert(array(
                    'name' => $post['name'],
                    'league_id' => $post['league'],
                    'color' => 'white',
                    'color_code' => '#ffffff',
                    'text_code' => '#000000',
                    'final_rank' => null,
                ));

                if($id) {
                    echo Zend_Json::encode(array('result' => 'success', 'data' => $id));

                    $this->view->message("Created the team `{$post['name']}`", 'success');
                    return;
                }

                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Error creating team.'));
                return;
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Team Already Exists'));
                return;
            }
        }
    }

    public function teamseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/colorpicker/css/layout.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/colorpicker/css/colorpicker.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/teamsedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/teamsedit.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/colorpicker/js/colorpicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/colorpicker/js/eye.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/colorpicker/js/utils.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/colorpicker/js/layout.js');

        $teamId = $this->getRequest()->getUserParam('team_id');

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $team = $leagueTeamTable->find($teamId)->current();

        if(!$team) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($team->league_id)) {
            $this->_redirect('league/' . $team->league_id);
        }

        $form = new Form_LeagueTeamEdit($team);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

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

                $this->view->message("Team `{$team->name}` updated", 'success');
                $this->_redirect('league/' . $team->league_id);
            } else {
                $form->populate($post);
            }
        }


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

        if(!$this->view->isLeagueDirector($team->league_id)) {
            $this->_redirect('league/' . $team->league_id);
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/schedule.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/schedule.js');

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
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();

        $leagueId = $this->getRequest()->getUserParam('league_id');

        if(!$this->view->isLeagueDirector($leagueId)) {
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_LeagueScheduleEdit(null, $leagueId);

        if($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNoRender(true);
            $post = $this->getRequest()->getPost();

            $leagueGameTable = new Model_DbTable_LeagueGame();
            $leagueGameDataTable = new Model_DbTable_LeagueGameData();
            $game = $leagueGameTable->fetchGame($leagueId, $post['day'], $post['week'], $post['field']);

            if($leagueGameDataTable->isUnique($game, $post['home_team'], $post['away_team'])) {
                // TODO: Remove this??
                $game = $leagueGameTable->fetchGame($leagueId, $post['day'], $post['week'], $post['field']);

                if(!$game) {
                    $game = $leagueGameTable->createRow();
                    $game->league_id = $leagueId;
                }

                $game->day = $post['day'];
                $game->week = $post['week'];
                $game->field = $post['field'];
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

                $homeTeam->league_team_id = $post['home_team'];
                $awayTeam->league_team_id = $post['away_team'];

                $homeTeam->score = 0;
                $awayTeam->score = 0;

                $homeTeam->save();
                $awayTeam->save();

                echo Zend_Json::encode(array('result' => 'success', 'data' => $game->id));
                return;
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Game Already Exists'));
                return;
            }
        }

        $this->view->form = $form;
    }

    public function scheduleeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/scheduleedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/scheduleedit.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $gameId = $this->getRequest()->getUserParam('game_id');

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

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/schedule');
        }

        $form = new Form_LeagueScheduleEdit($gameId, $leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid(($post))) {
                $data = $form->getValues();
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
            } else {
                $form->populate($post);
            }
        }

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

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/schedule');
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/generate.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/schedule.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/generate.js');

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

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/schedule');
        }

        $this->view->league = $league;

        $form = new Form_GenerateSchedule($league);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(isset($post['save'])) {
                $leagueGameDataTable = new Model_DbTable_LeagueGameData();

                // remove all of the current games for the league
                $leagueGameTable->getAdapter()->query('DELETE FROM league_game WHERE league_id = ' . $leagueId);

                // save the new schedule
                foreach($session->schedule as $week => $tmp) {
                    foreach($tmp as $data) {
                        $gameId = $leagueGameTable->createGame($leagueId, $data['date'], $week, $data['field']);
                        $leagueGameDataTable->addGameData($gameId, 'home', $data['home_team']);
                        $leagueGameDataTable->addGameData($gameId, 'away', $data['away_team']);
                    }
                }

                $this->view->message('League schedule generated', 'success');
                $this->_redirect('league/' . $leagueId . '/schedule');
            }
            if($form->isValid($post)) {

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

                $session->schedule = $results;
                $this->view->schedule = $results;

            } else {
                $form->populate($post);
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/contact.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/email.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_LeagueContact($leagueId, $this->view->user, $this->view->isLeagueDirector($leagueId));
        $form->getElement('subject')->setValue('[' . $this->view->leaguename($leagueId, true, true, true, true) . '] Information');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $leagueMemberTable = new Model_DbTable_LeagueMember();
                $data = $leagueMemberTable->fetchAllEmails($leagueId, $this->view->user, $this->view->isLeagueDirector($leagueId));
                $mail = new Zend_Mail();
                $mail->setSubject($post['subject']);
                $mail->setFrom($post['from']);

                foreach($post['to'] as $to) {
                    foreach($data[$to] as $email) {
                        $mail->clearRecipients();
                        if(APPLICATION_ENV == 'production') {
                            $mail->addTo($email);
                            $mail->setBodyText($post['content']);
                            $mail->send();
                        } else {
                            $mail->addTo('kcin1018@gmail.com');
                            $mail->setBodyText("TO: $email\r\n\r\n" . $post['content']);
                        }
                    }
                }
                
                $this->view->message('Email sent.', 'success');
                $this->_redirect('league/' . $leagueId . '/email');
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }

    public function rankingsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/teams.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/rankings.js');

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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/rankingsedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/rankingsedit.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/rankings');
        }

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(isset($post['clear'])) {
                $leagueTeamTable->clearFinalResults($leagueId);
                $this->view->message('League final rankings cleared', 'success');
                $this->_redirect('league/' . $leagueId . '/rankings');
            } else {
                $rank = 1;
                foreach($post['ranks'] as $item) {
                    $team = $leagueTeamTable->find($item)->current();
                    if($team) {
                        $team->final_rank = $rank;
                        $team->save();
                    }
                    $rank++;
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/players.css');

        //$this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/status.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->players = $leagueMemberTable->fetchPlayerInformation($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            ////apache_setenv('no-gzip', '1');
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
                    echo "," . str_replace(',', ' ', $value);
                }
                foreach($player['answers'] as $key => $value) {
                    echo "," . str_replace("\r\n", '  ', str_replace(',', ' ', $value));
                }
                echo "\n";

                $i++;
            }

            flush();
        }
    }

    public function shirtsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/shirts.css');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
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

            echo "color,XS,S,M,L,XL,XXL\n";

            foreach($this->view->shirts as $color => $shirt) {
                foreach(array('XS', 'S', 'M', 'L', 'XL', 'XXL') as $size) {
                    $lowSize = strtolower($size);
                    $$lowSize = (isset($shirt[$size])) ? $shirt[$size] : 0;

                }
                echo "{$color},{$xs},{$s},{$m},{$l},{$xl},{$xxl}\n";
            }

            flush();
        }
    }

    public function emergencyAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/emergency.css');


        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/status.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/status.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }

        if($this->getRequest()->isPost()) {
            // make sure its an AJAX request
            if(!$this->getRequest()->isXmlHttpRequest()) {
                $this->_redirect('league/' . $leagueId . '/status');
            }

            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $post = $this->getRequest()->getPost();

            list($field, $userId, $checked) = explode('-', $post['data']);

            $leagueMemberTable = new Model_DbTable_LeagueMember();
            $member = $leagueMemberTable->fetchMember($leagueId, $userId);

            if($field != 'waiver') {
                $member->$field = ($checked == 'true') ? 1 : 0;
                $member->save();
            } else {
                $userWaiverTable = new Model_DbTable_UserWaiver();
                $userWaiverTable->updateWaiver($userId, $this->view->league->year, $checked, $this->view->user->id);
            }

        }

        $this->view->all = $this->getRequest()->getUserParam('all');

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $this->view->statuses = $leagueMemberTable->fetchPlayerStatuses($leagueId, $this->view->league->year);

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

            flush();
        }
    }

    public function registerAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/register.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/register.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // do registration checks to make sure a user is able to register
        $this->view->registrationMessage = $this->view->getLeagueRegistrationMessage($leagueId);
        if($this->view->registrationMessage) {
            $this->view->message($this->view->registrationMessage, 'error');
            return;
        }

        // stop if user not logged in
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $session = new Zend_Session_Namespace('registration' . $leagueId);
        $state = $this->getRequest()->getUserParam('state');
        $userTable = new Model_DbTable_User();

        $form = new Form_LeagueRegister($leagueId, $this->view->user->id, $state);
        if($state == 'user') {
            // reset registration data
            $session->unsetAll();

            if(!$userTable->hasMinors($this->view->user->id)) {
                $session->registrantId = $this->view->user->id;
                $this->_redirect('league/' . $leagueId . '/register/personal');
            }

            // handle post request
            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                if(isset($post['back'])) {
                    $this->_redirect('league/' . $leagueId . '/register/user');
                }

                if($form->isValid($post)) {
                    $session->registrantId = $post['user'];
                    $this->_redirect('league/' . $leagueId . '/register/personal');
                } else {
                    $form->populate($post);
                }
            }

        } else if($state == 'personal') {
            // user has already registered redirect to success
            if($this->view->isRegistered($leagueId, $session->registrantId)) {
                $this->view->message('You have already registered as this user.', 'warning');
                $this->_redirect('/league/' . $leagueId . '/register_success');
            }

            $userEmergencyTable = new Model_DbTable_UserEmergency();
            $this->view->contacts = $userEmergencyTable->fetchAllContacts($session->registrantId);
            unset($session->personal);
            // handle post request
            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                if(isset($post['back'])) {
                    $this->_redirect('league/' . $leagueId . '/register/user');
                }

                if($form->isValid($post)) {
                    unset($post['next']);
                    //store the data in the session
                    $session->personal = $post;

                    $this->_redirect('league/' . $leagueId . '/register/league');
                } else {
                    $this->view->message('There are errors with the infromation you have entered, please correct them.', 'error');
                    $form->populate($post);

                    $userTable = new Model_DbTable_User();
                    $userProfileTable = new Model_DbTable_UserProfile();

                    $user = $userTable->find($session->registrantId)->current();
                    $userProfile = $userProfileTable->find($session->registrantId)->current();
                    if(!empty($user->parent)) {
                        $parent = $userTable->find($user->parent)->current();
                        $parentProfile = $userProfileTable->find($user->parent)->current();

                        $user->email = $parent->email;
                        $userProfile->phone = $parentProfile->phone;
                    }

                    $form->getElement('email')->setValue($user->email);
                    $form->getElement('phone')->setValue($userProfile->phone);
                }
            }

        } else  if($state == 'league') {
            unset($session->league);

            if(empty($session->personal)) {
                $this->_redirect('league/' . $leagueId . '/register/personal');
            }

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                if(isset($post['back'])) {
                    $this->_redirect('league/' . $leagueId . '/register/personal');
                }

                if($form->isValid($post)) {
                    unset($post['save']);
                    foreach(array('user_team_new', 'user_team_select') as $key) {
                        if(isset($post[$key])) {
                            $post['user_teams'] = $post[$key];
                            unset($post[$key]);
                        }
                    }

                    $session->league = $post;

                    // all data entered save the registrant
                    $userTable = new Model_DbTable_User();
                    $userProfileTable = new Model_DbTable_UserProfile();

                    $user = $userTable->find($session->registrantId)->current();
                    $userProfile = $userProfileTable->find($session->registrantId)->current();
                    $user->first_name = $session->personal['first_name'];
                    $user->last_name = $session->personal['last_name'];

                    // only update the user email/phone if they are not a minor
                    if(!empty($user->parent)) {
                        $user->email = $session->personal['email'];
                        $userProfile->phone = $session->personal['phone'];
                    }

                    $userProfile->gender = $session->personal['gender'];
                    $userProfile->birthday = $session->personal['birthday'];
                    $userProfile->nickname = $session->personal['nickname'];
                    $userProfile->height = $session->personal['height'];
                    $userProfile->level = $session->personal['level'];
                    $userProfile->experience = $session->personal['experience'];

                    $userEmergencyTable = new Model_DbTable_UserEmergency();
                    $i = 0;
                    foreach($session->personal['contactNames'] as $contactName) {
                        $contactPhone = $session->personal['contactPhones'][$i];

                        $allContacts = $userEmergencyTable->fetchAllContacts($session->registrantId);

                        // remove contacts that are no longer in db
                        foreach($allContacts as $contact) {
                            if(!in_array($contact->phone, $session->personal['contactPhones'])) {
                                $contact->delete();
                            }
                        }

                        $contact = $userEmergencyTable->fetchContact($session->registrantId, $contactPhone);
                        if(!$contact) {
                            $nameData = explode(' ', $contactName);
                            if(count($nameData) == 1) {
                                $first = $info['name'];
                                $last = '';
                            } else if(count($nameData) == 2) {
                                $first = $nameData[0];
                                $last = $nameData[1];
                            }

                            $userEmergencyTable->insert(array(
                                'user_id' => $session->registrantId,
                                'first_name' => ucwords(trim($first)),
                                'last_name' => ucwords(trim($last)),
                                'phone' => $contactPhone,
                                'weight' => $i,
                            ));
                        }

                        $i++;
                    }

                    $leagueMemberTable = new Model_DbTable_LeagueMember();
                    $leagueMember = $leagueMemberTable->fetchMember($leagueId, $session->registrantId);
                    if(!$leagueMember) {
                        $leagueMemberId = $leagueMemberTable->insert(array(
                            'league_id' => $leagueId,
                            'user_id' => $session->registrantId,
                            'position' => 'player',
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
                    $this->view->message('You have successfully registered for ' . $this->view->leaguename($leagueId, true, true, true, true));

                    $session->unsetAll();

                    // if the user has not signed a waiver redirect to online waiver
                    if($userProfileTable->isEighteenOrOver($userProfile->birthday) and !$userWaiverTable->hasWaiver($session->registrantId, $leagueId)) {
                        $this->_redirect('league/' . $leagueId . '/waiver');
                    }

                    $this->_redirect('league/' . $leagueId . '/register_success');

                } else {
                    $this->view->message('There are errors with the infromation you have entered, please correct them.', 'error');
                    $form->populate($post);
                }
            }
        }

        switch($state) {
            case 'user':
                $title = 'Select a user to register as';
                break;
            case 'personal':
                $title = 'Enter or check your personal data';
                break;
            case 'league':
                $title = 'Answer the league questions';
                break;
        }

        $this->view->title = $title;
        $this->view->state = $state;
        $this->view->form = $form;

    }

    public function registersuccessAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/registersuccess.css');

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
            $this->_redirect('league/' . $leagueId . '/register');
        }
    }

    public function manageAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/manage.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/league/manage.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $teamId = $this->getRequest()->getUserParam('team_id');
        if(!$teamId) {
            $teamId = 0;
        }
        $this->view->teamId = $teamId;

        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league or !$this->view->isLeagueDirector($leagueId)) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(empty($post['teams-select'])) {
                $this->view->message('You must select a team before adding/removing players', 'warning');
            } else {
                $leagueMemberTable = new Model_DbTable_LeagueMember();
                if(isset($post['clear'])) {
                    $teamMembers = $leagueMemberTable->fetchAllByType($leagueId, 'player', $teamId);
                    foreach($teamMembers as $member) {
                        $leagueMember = $leagueMemberTable->find($member->id)->current();
                        $leagueMember->league_team_id = null;
                        $leagueMember->modified_at = date('Y-m-d H:i:s');
                        $leagueMember->modified_by = $this->view->user->id;
                        $leagueMember->save();
                    }
                } else if(isset($post['add'])) {
                    if(isset($post['available-select']) and count($post['available-select'])) {
                        foreach($post['available-select'] as $leagueMemberId) {
                            $leagueMember = $leagueMemberTable->find($leagueMemberId)->current();
                            $leagueMember->league_team_id = $post['teams-select'];
                            $leagueMember->modified_at = date('Y-m-d H:i:s');
                            $leagueMember->modified_by = $this->view->user->id;
                            $leagueMember->save();
                        }
                    } else {
                        $this->view->message('You must select at least one available player before adding.', 'warning');
                    }
                } else if(isset($post['remove'])) {
                    if(isset($post['players-select']) and count($post['players-select'])) {
                        foreach($post['players-select'] as $leagueMemberId) {
                            $leagueMember = $leagueMemberTable->find($leagueMemberId)->current();
                            $leagueMember->league_team_id = null;
                            $leagueMember->modified_at = date('Y-m-d H:i:s');
                            $leagueMember->modified_by = $this->view->user->id;
                            $leagueMember->save();
                        }
                    } else {
                        $this->view->message('You must select at least one team player before removing.', 'warning');
                    }
                }
            }
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $this->view->teams = $leagueTeamTable->fetchAllTeams($leagueId);
        $this->view->available = $leagueMemberTable->fetchPlayersByTeam($leagueId, null);
        $this->view->teamPlayers = $leagueMemberTable->fetchPlayersByTeam($leagueId, $teamId);
        
        $userTable = new Model_DbTable_User();
        $this->view->users = $userTable->fetchAllUsers();
        $this->view->leaguePlayers = $leagueMemberTable->fetchPlayersByLeague($leagueId);
    }
    
    public function addplayerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $players = $this->getRequest()->getParam('players');
        
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $flag = 0;
        if($players) {
            foreach(explode(',', $players) as $playerId) {
                $ret = $leagueMemberTable->addNewPlayer($leagueId, $playerId);
                if($ret == 'duplicate') {
                    if($flag < 1) {
                        $this->view->message('Some players were already members of the league', 'info');
                    }
                    $flag++;
                }
            }
        }
        if($flag != count(explode(',', $players))) {
            $this->view->message('Player(s) added', 'success');
        }
    }

    public function removeplayerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $players = $this->getRequest()->getParam('players');
        
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if($players) {
            foreach(explode(',', $players) as $playerId) {
                $leagueMemberTable->removePlayer($playerId);
            }
        }
        $this->view->message('Player(s) removed', 'success');
    }


    public function moveAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/move.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/league/move.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $this->view->state = $this->getRequest()->getUserParam('state');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }

        $session = new Zend_Session_Namespace('move_players');
        $leagueMemberTable = new Model_DbTable_LeagueMember();

        if($this->view->state == 'players') {
            unset($session->players);
            $this->view->players = $leagueMemberTable->fetchPlayersByLeague($leagueId);

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                if(!isset($post['players'])) {
                    $this->view->message('You must select at least one player to move.', 'warning');
                } else {
                    $session->players = $post['players'];
                    $this->_redirect('league/' . $leagueId . '/move/target');
                }
            }
        } else if($this->view->state == 'target') {
            if(!isset($session->players)) {
                $this->_redirect('league/' . $leagueId . '/move/players');
            }

            unset($session->target);

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                if(!isset($post['target']) or $post['target'] == 0) {
                    $this->view->message('You must select a league to move the players to.', 'warning');
                } else {
                    $session->target = $post['target'];
                    $this->_redirect('league/' . $leagueId . '/move/confirm');
                }
            }

            $this->view->leagues = $leagueTable->fetchAllCurrentLeagues();

        } else if($this->view->state == 'confirm') {
            if(!isset($session->target)) {
                $this->_redirect('league/' . $leagueId . '/move/target');
            }

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                if(isset($post['confirm'])) {
                    foreach($session->players as $player) {
                        $member = $leagueMemberTable->find($player)->current();
                        $member->league_team_id = null;
                        $member->league_id = $session->target;
                        $member->modified_at = date('Y-m-d H:i:s');
                        $member->modified_by = $this->view->user->id;
                        $member->save();
                    }
                    $this->view->message('Players moved', 'success');
                    $session->unsetAll();
                    $this->_redirect('league/' . $leagueId . '/move');
                }
            }

            $this->view->players = $session->players;
            $this->view->target = $session->target;
        }
    }

    public function logoAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/logo.css');
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $teamId = $this->getRequest()->getUserParam('team_id');

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $team = $leagueTeamTable->find($teamId)->current();

        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();
        if(!$team or !$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueCaptain($leagueId, $teamId) and !$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }

        if($this->getRequest()->isPost()) {
            if(!empty($_FILES['file']['tmp_name'])) {
                $image = new Model_SimpleImage();
                $image->load($_FILES['file']['tmp_name']);
                $image->resize(85,85);
                if(APPLICATION_ENV == 'production') {
                    $image->save(APPLICATION_WEBROOT . '/images/team_logos/' . $teamId . '.jpg');
                }
                $image->save(APPLICATION_PATH . '/../public/images/team_logos/' . $teamId . '.jpg');

                $this->view->message('Logo updated.', 'success');
                $this->_redirect('league/' . $leagueId);
            }
        }

        $this->view->league = $league;
        $this->view->team = $team;
    }

    public function userteamsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/userteams.css');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
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

            flush();
        }
    }

    public function waiverAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/waiver.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/league/waiver.js');

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

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->view->message('You disagreed with the waiver, before you may play in this league you must sign a waiver for the year.', 'error');
                $this->_redirect('league/' . $leagueId . '/register_success');
            }

            if(strstr($post['name'], $this->view->user->first_name) === false or strstr($post['name'], $this->view->user->last_name) === false or empty($post['name'])) {
                $this->view->message('You must type your name into the name box to confirm that you read the waiver.', 'error');
            } else {
                $userWaiverTable->updateWaiver($this->view->user->id, $this->view->league->year, 1, $this->view->user->id);
                $this->view->message('User waiver signed', 'success');
                $this->_redirect('league/' . $leagueId . '/register_success');
            }

        }
    }
}
