<?php

class LeagueController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
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
        $this->view->leagues = $leagueTable->fetchCurrentLeaguesBySeason($season);
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
                    
                    $leagueInformation->is_youth = $data['is_youth'];
                    $leagueInformation->is_pods = $data['is_pods'];
                    $leagueInformation->is_hat = $data['is_hat'];
                    $leagueInformation->is_clinic = $data['is_clinic'];
                    $leagueInformation->user_teams = $data['user_teams'];
                    $leagueInformation->save();
                    
                }
                $league->name = $data['name'];
                $league->save();
                
                $this->view->message('League data saved successfully.'. 'success');
                $this->_redirect('leagues/' . $this->view->season);
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
                $this->_redirect('leagues/' . $this->view->season);
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

        $form = new Cupa_Form_LeagueEdit();
        $form->loadSection($leagueId, 'registration');
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
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
                
                $this->view->message('League registration updated successfully.', 'success');
                $this->_redirect('leagues/' . $this->view->season);
                
            } else {
                $form->populate($post);
            }
        }
        
        $this->view->form = $form;
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
                $this->_redirect('leagues/' . $this->view->season);
                
            } else {
                $form->populate($post);
            }
        }
        
        $this->view->form = $form;
    }
    
    public function pageaddAction()
    {
        
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

    public function scheduleaddAction()
    {
        // action body
    }

    public function scheduleeditAction()
    {
        // action body
    }

    public function schedulegenerateAction()
    {
        // action body
    }

    public function finalAction()
    {
        // action body
    }

    public function finaladminAction()
    {
        // action body
    }

    public function emailAction()
    {
        // action body
    }

    public function rankingsAction()
    {
        // action body
    }

    public function rankingseditAction()
    {
        // action body
    }

    public function playersAction()
    {
        // action body
    }

    public function shirtsAction()
    {
        // action body
    }

    public function emergencyAction()
    {
        // action body
    }

    public function statusAction()
    {
        // action body
    }

    public function statusmarkAction()
    {
        // action body
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



























































