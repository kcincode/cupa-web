<?php

class PageController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function homeAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/home.css');
        
        // link to the db table
        $newsTable = new Cupa_Model_DbTable_News();
     
        // get all news and seperate by type
        $allNews = array();        
        foreach($newsTable->fetchAllNews() as $news) {
            $allNews[$news['category']][] = $news;
        }
        
        // set the view variable
        $this->view->news = $allNews;
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

        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if(!Zend_Auth::getInstance()->hasIdentity() or
           Zend_Auth::getInstance()->hasIdentity() and
           (!$userRoleTable->hasRole($this->view->user->id, 'admin') and
           !$userRoleTable->hasRole($this->view->user->id, 'editor') and
           !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id))) {
            $this->view->message('You either are not logged in or you do not have permission to edit this page.');
            $this->_redirect('/' . $page->name);
        }

        if($this->getRequest()->isPost()) {
            $post = $this-getRequest()->getPost();
             
            $page->content = $post['content'];
            $page->updated_at = date('Y-m-d H:i:s');
            $page->save();
            $this->view->message('Page udpated successfully.', 'success');
            $this->_redirect('/' . $page->name);
        }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/edit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        
    }

    public function adminAction()
    {
        // action body
    }

    public function contactAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/contact.css');
        
        // initialize the contact form and add the users email if valid
        $form = new Cupa_Form_Contact();
        
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $form->getElement('from')->setValue($this->view->user->email);
        }
        
        // handle the form post
        if($this->getRequest()->isPost()) {
            // get the posted data
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                // if form is valid get form values
                $data = $form->getValues();
                
                Cupa_Model_Email::sendContactEmail($data);
                $this->view->message('Email sent successfully.', 'success');
                $this->_redirect('/contact');
            } else {
                // display the form errors
                $form->populate($post);
            }
        }
        
        // add the form variable to the view
        $this->view->form = $form;
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

    public function allnewsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/allnews.css');
        
        $category = $this->getRequest()->getUserParam('category');
        $this->view->category = ucwords($category);
        
        $newsTable = new Cupa_Model_DbTable_News();
        $this->view->news = $newsTable->fetchNewsByCategory($category);
        
        if(!count($this->view->news)) {
            // throw a 404 error there is no news returned
            throw new Zend_Controller_Dispatcher_Exception('Category does not exist');
        }
    }

    public function newsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/news.css');
        
        $slug = $this->getRequest()->getUserParam('slug');
        $newsTable = new Cupa_Model_DbTable_News();
        $news = $newsTable->fetchNewsBySlug($slug);
        
        if($news and $news->is_visible) {
            $this->view->news = $news;
        } else {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
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
