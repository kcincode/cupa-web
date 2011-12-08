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
        
        $this->view->page = $pageTable->fetchBy('name', $season . '_league');
        $this->view->links = $leagueSeasonTable->generateLinks();
        $this->view->leagues = $leagueTable->fetchCurrentLeaguesBySeason($season);
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



























































