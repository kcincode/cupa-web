<?php

class PageController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function homeAction()
    {
        $this->view->message('This is just a test message', 'success');
    }

    public function viewAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        
        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);
        
        if($page and $page->is_visible) {
            $this->view->page = $page;
            $this->view->links = $pageTable->fetchChildren($page);
        } else {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
    }

    public function editAction()
    {
        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);
        
        if($page) {
            $this->view->page = $page;
        } else {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
    }

    public function adminAction()
    {
        // action body
    }

    public function contactAction()
    {
        // action body
    }

    public function officersAction()
    {
        // action body
    }

    public function officerseditAction()
    {
        // action body
    }

    public function officersaddAction()
    {
        // action body
    }

    public function minutesAction()
    {
        // action body
    }

    public function minuteseditAction()
    {
        // action body
    }

    public function minutesaddAction()
    {
        // action body
    }

    public function directorsAction()
    {
        // action body
    }

    public function pickupAction()
    {
        // action body
    }

    public function pickupaddAction()
    {
        // action body
    }

    public function pickupeditAction()
    {
        // action body
    }

    public function clubsAction()
    {
        // action body
    }

    public function clubsaddAction()
    {
        // action body
    }

    public function clubseditAction()
    {
        // action body
    }

    public function linksAction()
    {
        // action body
    }

    public function linksaddAction()
    {
        // action body
    }

    public function linkseditAction()
    {
        // action body
    }

    public function newsAction()
    {
        // action body
    }

    public function newsaddAction()
    {
        // action body
    }

    public function newseditAction()
    {
        // action body
    }


}








































