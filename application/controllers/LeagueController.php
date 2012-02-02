<?php

class LeagueController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/common.css');
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/index.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/index.js');

        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        $pageTable = new Cupa_Model_DbTable_Page();
        
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
        
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        $leagueSeasonTable->moveSeason($seasonId, $weight);
        
        $this->_redirect('leagues');
    }
    
    public function seasoneditAction()
    {
        $pageTable = new Cupa_Model_DbTable_Page();
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
        
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();

        $seasonId = $this->getRequest()->getUserParam('season_id');
        $this->view->season = $leagueSeasonTable->find($seasonId)->current();
        
        $form = new Cupa_Form_LeagueSeasonEdit();
        $form->loadFromSeason($this->view->season);
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $this->view->season->name = $data['name'];
                $this->view->season->when = $data['when'];
                $this->view->season->information = $data['information'];
                $this->view->season->save();
                
                $this->view->message("Season `{$data['name']}` updated successfully.", 'success');
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
        
        $pageTable = new Cupa_Model_DbTable_Page();
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
            
            $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
            if($leagueSeasonTable->isUnique($post['name'])) {
                $season = $leagueSeasonTable->createRow();
                $season->name = $post['name'];
                $season->when = 'Unknown';
                $season->information = '';
                $season->weight = $leagueSeasonTable->fetchNextWeight();
                $season->save();
                
                $this->view->message('Season created successfully.');
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
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        
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
        
        $pageTable = new Cupa_Model_DbTable_Page();
        $leagueTable = new Cupa_Model_DbTable_League();
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $pageTable = new Cupa_Model_DbTable_Page();
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        $leagueInformationTable = new Cupa_Model_DbTable_LeagueInformation();
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
                
        $form = new Cupa_Form_LeagueEdit();
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
                    
                }
                
                $league->visible_from = $data['visible_from'];
                $league->name = $data['name'];
                $league->save();
                
                $this->view->message('League data saved successfully.'. 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $pageTable = new Cupa_Model_DbTable_Page();
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
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

        $form = new Cupa_Form_LeagueEdit();
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
                $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
                
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
                
                $leagueLocationTable = new Cupa_Model_DbTable_LeagueLocation();
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

                $this->view->message('League Information updated successfully.', 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $pageTable = new Cupa_Model_DbTable_Page();
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
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

        $form = new Cupa_Form_LeagueEdit();
        $form->loadSection($leagueId, 'registration');
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            if(isset($post['new_question'])) {
                if($post['id'] != 0) {
                    $leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();
                    $leagueQuestionListTable->addQuestionToLeague($leagueId, $post['id'], 1);
                    // disable the layout
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    echo $this->view->baseUrl() . '/league/' . $leagueId . '/edit_registration';
                    return;
                } else {
                    $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
                    $questionId = $leagueQuestionTable->createQuestion($post['name'], 'Placeholder title', $post['type'], null);
                    
                    $leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();
                    $leagueQuestionListTable->addQuestionToLeague($leagueId, $questionId, 1);

                    // disable the layout
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    echo $this->view->baseUrl() . '/league_question/' . $leagueId . '/' . $questionId;
                    return;
                }
            }
            
            if(isset($post['remove_question'])) {
                Zend_Debug::dump($post);
                
            }
            
            
            if($post['limit_select'] == 1) {
                $form->getElement('total_players')->setRequired(false);
            } else {
                $form->getElement('male_players')->setRequired(false);
                $form->getElement('female_players')->setRequired(false);
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
                                
                $leagueLimitTable = new Cupa_Model_DbTable_LeagueLimit();               
                $leagueLimit = $leagueLimitTable->fetchLimits($leagueId);
                
                if($data['limit_select'] == 1) {
                    $leagueLimit->male_players = $data['male_players'];
                    $leagueLimit->female_players = $data['female_players'];
                    $leagueLimit->total_players = null;
                } else {
                    $leagueLimit->male_players = null;
                    $leagueLimit->female_players = null;
                    $leagueLimit->total_players = $data['total_players'];
                }
                
                $leagueLimit->teams = (empty($data['teams'])) ? null : $data['teams'];
                $leagueLimit->save();
                
                $leagueInformationTable = new Cupa_Model_DbTable_LeagueInformation();
                $leagueInformation = $leagueInformationTable->fetchInformation($leagueId);
                $leagueInformation->paypal_code = (empty($data['paypal_code'])) ? null : $data['paypal_code'];
                $leagueInformation->save();
                
                $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
                $leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();

                foreach($post['question'] as $weight => $questionName) {
                    $leagueQuestion = $leagueQuestionTable->fetchQuestion($questionName);
                    $required = (isset($post['required'][$questionName])) ? 1 : 0;
                    $leagueQuestionListTable->updateQuestionList($leagueId, $leagueQuestion->id, $required, $weight);
                }
                
                $this->view->message('League registration updated successfully.', 'success');
                $this->_redirect('leagues/' . $this->view->season . '#leagues-' . $leagueId);
                
            } else {
                $form->populate($post);
            }
        }
        
        $this->view->form = $form;
        
        $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $league = $leagueTable->fetchLeagueData($leagueId);
        
        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        $this->view->leagueId = $league['id'];
        
        $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        if(!$question) {
            $this->_redirect('league/' . $leagueId . '/page_registration');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('leagues');
        }
        
        $form = new Cupa_Form_LeagueQuestionEdit($question);
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
                
                $this->view->message('Question `' . $question->name . '` updated successfully.', 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $league = $leagueTable->fetchLeagueData($leagueId);
        
        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        if(!$question) {
            $this->_redirect('league/' . $leagueId . '/page_registration');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('leagues');
        }

        $leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();
        $leagueQuestionListTable->removeQuestionFromLeague($leagueId, $questionId);
        
        $this->view->message('Question `' . $question->name . '` removed from the league.', 'success');
        $this->_redirect('league/' . $leagueId . '/edit_registration');
    }
    
    public function pagedescriptioneditAction()
    {
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Cupa_Model_DbTable_League();
        $pageTable = new Cupa_Model_DbTable_Page();
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
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
        
        $form = new Cupa_Form_LeagueEdit();
        $form->loadSection($leagueId, 'description');
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();

                $leagueInformationTable = new Cupa_Model_DbTable_LeagueInformation();
                $leagueInformationTable->description = $data['description'];
                $leagueInformationTable->save();
                
                $this->view->message('League description updated successfully.', 'success');
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
            
            $leagueTable = new Cupa_Model_DbTable_League();
            if($leagueTable->isUnique($post['year'], $post['season'], $post['day'])) {
                
                $id = $leagueTable->createBlankLeague($post['year'], $post['season'], $post['day'], null, $this->view->user->id);
                if(is_numeric($id)) {
                    $this->view->message('League created successfully.');
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

    public function formsAction()
    {
        // action body
    }

    public function formsaddAction()
    {
        // action body
    }

    public function formseditAction()
    {
        // action body
    }

    public function teamsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/teams.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/teams.js');
        
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = 'league/' . $leagueId;

        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
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
        
        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
        
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

            $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
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

                    $this->view->message("Successfully created the team `{$post['name']}`", 'success');
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
        
        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        $team = $leagueTeamTable->find($teamId)->current();

        if(!$team) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        if(!$this->view->isLeagueDirector($team->league_id)) {
            $this->_redirect('league/' . $team->league_id);
        }

        $form = new Cupa_Form_LeagueTeamEdit($team);
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
                
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
                
                $this->view->message("Team `{$team->name}` updated successfully.", 'success');
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

        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
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

        $this->view->message("Successfully deleted the team `$name`", 'success');

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
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
        $this->view->games = $leagueGameTable->fetchSchedule($leagueId);
        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
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

        $form = new Cupa_Form_LeagueScheduleEdit(null, $leagueId);

        if($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNoRender(true);
            $post = $this->getRequest()->getPost();

            $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
            $leagueGameDataTable = new Cupa_Model_DbTable_LeagueGameData();
            $game = $leagueGameTable->fetchGame($post['day'], $post['week'], $post['field']);

            if($leagueGameDataTable->isUnique($game, $post['home_team'], $post['away_team'])) {
                $game = $leagueGameTable->fetchGame($post['day'], $post['week'], $post['field']);

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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/scheduleedit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/scheduleedit.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $gameId = $this->getRequest()->getUserParam('game_id');

        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
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

        $form = new Cupa_Form_LeagueScheduleEdit($gameId, $leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid(($post))) {
                $data = $form->getValues();
                $leagueGameDataTable = new Cupa_Model_DbTable_LeagueGameData();
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

                $this->view->message('Game data updated successfully.', 'success');
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

        $leagueTable = new Cupa_Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
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

        $leagueGameDataTable = new Cupa_Model_DbTable_LeagueGameData();
        $leagueGameData = $leagueGameDataTable->fetchGameData($gameId);

        if(count($leagueGameData)) {
            $leagueGameData[0]->delete();
            $leagueGameData[1]->delete();
        }

        $this->view->message('Successfully deleted the game.', 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();

        $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
        if(!$league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/schedule');
        }
        
        $this->view->league = $league;

        $form = new Cupa_Form_GenerateSchedule($league);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(isset($post['save'])) {
                $leagueGameDataTable = new Cupa_Model_DbTable_LeagueGameData();
                
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
                
                $this->view->message('League Schedule generated successfully.', 'success');
                $this->_redirect('league/' . $leagueId . '/schedule');
            }
            if($form->isValid($post)) {
                
                // get start time from the league locations
                $leagueLocationTable = new Cupa_Model_DbTable_LeagueLocation();
                $leagueLocation = $leagueLocationTable->fetchByType($leagueId, 'league');
                
                $startHour = date('H', strtotime($leagueLocation->start));
                $endHour = date('H', strtotime($leagueLocation->end));
                $dayHours = 24 - ($endHour - $startHour);
                $weekSeconds = 7 * $dayHours * 60 * 60;
                $weeks = ceil((strtotime($leagueLocation->end) - strtotime($leagueLocation->start)) / $weekSeconds);
                
                $fields = explode(',', $post['number_of_fields']);
                
                $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $form = new Cupa_Form_LeagueContact($leagueId, $this->view->user, $this->view->isLeagueDirector($leagueId));
        $form->getElement('subject')->setValue('[' . $this->view->leaguename($leagueId, true, true, true, true) . '] Information');
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
                $data = $leagueMemberTable->fetchAllEmails($leagueId, $this->view->user, $this->view->isLeagueDirector($leagueId));
                $mail = new Zend_Mail();
                $mail->setSubject($post['subject']);
                $mail->setFrom($post['from']);
                
                foreach($data[$post['to']] as $email) {
                    $mail->clearRecipients();
                    if(APPLICATION_ENV == 'production') {
                        $mail->addTo($email);
                        $mail->setBodyText($post['content']);
                    } else {
                        $mail->addTo('kcin1018@gmail.com');
                        $mail->setBodyText("TO: $email\r\n\r\n" . $post['content']);
                    }
                    $mail->send();
                }
                
                
                $this->view->message('Email sent successfully.', 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = 'league/' . $leagueId;

        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        $this->view->teams = $leagueTeamTable->fetchAllTeamRanks($leagueId);
    }

    public function rankingseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/rankingsedit.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/rankingsedit.js');
        
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId . '/rankings');
        }

        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(isset($post['clear'])) {
                $leagueTeamTable->clearFinalResults($leagueId);
                $this->view->message('League final rankings cleared successfully.', 'success');
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
            
            $this->view->message('League final rankings updates successfully.', 'success');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }
        
        $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
        $this->view->players = $leagueMemberTable->fetchPlayerInformation($leagueId);
        
        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            apache_setenv('no-gzip', '1');
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
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->view->league = $leagueTable->find($leagueId)->current();
        
        if(!$this->view->league) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        if(!$this->view->isLeagueDirector($leagueId)) {
            $this->_redirect('league/' . $leagueId);
        }

        
        $leagueAnswerTable = new Cupa_Model_DbTable_LeagueAnswer();
        $this->view->shirts = $leagueAnswerTable->fetchShirts($leagueId);

        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            
            apache_setenv('no-gzip', '1');
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
        // action body
    }

    public function statusAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/status.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl(). '/js/league/status.js');
        
        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Cupa_Model_DbTable_League();
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
            
            $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
            $member = $leagueMemberTable->fetchMember($leagueId, $userId);

            if($field != 'waiver') {
                $member->$field = ($checked == 'true') ? 1 : 0;
                $member->save();
            } else {
                $userWaiverTable = new Cupa_Model_DbTable_UserWaiver();
                $userWaiverTable->updateWaiver($userId, $this->view->league->year, $checked, $this->view->user->id);
            }
            
        }
        
        $this->view->all = $this->getRequest()->getUserParam('all');
        
        $leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
        $this->view->statuses = $leagueMemberTable->fetchPlayerStatuses($leagueId, $this->view->league->year);
        
        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            apache_setenv('no-gzip', '1');
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

    public function manageAction()
    {
        // action body
    }

    public function manageaddAction()
    {
        // action body
    }

    public function manageremoveAction()
    {
        // action body
    }


}
