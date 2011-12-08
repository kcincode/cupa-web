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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/leagues.css');
        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        $pageTable = new Cupa_Model_DbTable_Page();
        
        $this->view->page = $pageTable->fetchBy('name', 'leagues');
        $this->view->links = $leagueSeasonTable->generateLinks();
        $this->view->leagues = $leagueSeasonTable->fetchAll();
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



























































