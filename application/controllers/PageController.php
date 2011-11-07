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
        
        $session = new Zend_Session_Namespace('newsbackbutton');
        $session->unsetAll();
    }

    public function viewAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        
        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if($page and ($page->is_visible or (Zend_Auth::getInstance()->hasIdentity() and ($userRoleTable->hasRole($this->view->user->id, 'admin') or
           $userRoleTable->hasRole($this->view->user->id, 'editor') or
           $userRoleTable->hasRole($this->view->user->id, 'editor', $page->id))))) {
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
        
        if(!$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Cupa_Form_PageEdit();
        $form->loadFromPage($page);

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
            $post = $this->getRequest()->getPost();
            
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $page->title = $data['title'];
                $page->url = (empty($data['url'])) ? null : $data['url'];
                $page->target = $data['target'];
                $page->weight = $data['weight'];
                $page->content = $data['content'];
                $page->updated_at = date('Y-m-d H:i:s');
                $page->last_updated_by = $this->view->user->id;
                $page->save();
                
                $this->view->message('Page updated successfully.', 'success');
                $this->_redirect('/' . $page->name);
            } else {
                $form->populate($post);
            }
       }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/edit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        
        $this->view->page = $page;
        $this->view->form = $form;
    }

    public function adminAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/admin.css');

        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);
        
        if(!$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Cupa_Form_PageAdmin();
        $form->loadFromPage($page);
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if(!Zend_Auth::getInstance()->hasIdentity() or
           Zend_Auth::getInstance()->hasIdentity() and
           (!$userRoleTable->hasRole($this->view->user->id, 'admin'))) {
            $this->view->message('You either are not logged in or you do not have permission to edit this page.');
            $this->_redirect('/' . $page->name);
        }

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $page->parent = ($post['parent'] == 0) ? null : $post['parent'];
                $page->name = $post['name'];
                $page->is_visible = $post['is_visible'];
                $page->updated_at = date('Y-m-d H:i:s');
                $page->last_updated_by = $this->view->user->id;
                $page->save();
                
                $this->view->message('Page updated successfully.', 'success');
                $this->_redirect('/' . $page->name);                
            } else {
                $form->populate($post);
            }
        }
        
        $this->view->page = $page;
        $this->view->form = $form;
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/clubs.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/clubs.js');

        $clubTable = new Cupa_Model_DbTable_Club();
        $this->view->activeClubs = $clubTable->fetchAllByType('current');
        $this->view->pastClubs = $clubTable->fetchAllByType('past');
        
        $pageTable = new Cupa_Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'clubs');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
        
    }

    public function clubsaddAction()
    {
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $clubTable = new Cupa_Model_DbTable_Club();
            if($clubTable->isUnique($post['name'])) {
                $club = $clubTable->createRow();
                $club->name = $post['name'];
                $club->type = 'Unknown';
                $club->begin = 'Unknown';
                $club->content = '';
                $club->updated_at = date('Y-m-d H:i:s');
                $club->last_updated_by = $this->view->user->id;
                $club->save();
                
                $this->view->message('Club Team created successfully.');
                echo Zend_Json::encode(array('result' => 'success', 'data' => $club->id));
            } else {
                $this->_helper->viewRenderer->setNoRender(true);
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Name Already Exists'));
                return;
            }
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();
    }

    public function clubseditAction()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/clubsedit.css');
        
        $clubTable = new Cupa_Model_DbTable_Club();
        $pageTable = new Cupa_Model_DbTable_Page();
        $userRoleTable = new Cupa_Model_DbTable_UserRole();

        $page = $pageTable->fetchBy('name', 'clubs');
        if(!Zend_Auth::getInstance()->hasIdentity() or
           Zend_Auth::getInstance()->hasIdentity() and
           (!$userRoleTable->hasRole($this->view->user->id, 'admin') and 
            !$userRoleTable->hasRole($this->view->user->id, 'editor') and
            !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id))) {
            $this->view->message('You either are not logged in or you do not have permission to edit this team.');
            $this->_redirect('/clubs');
        }

        $clubId = $this->getRequest()->getUserParam('club');
        $club = $clubTable->find($clubId)->current();
        
        if(!$club) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Cupa_Form_ClubEdit();
        $form->loadFromClub($club);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $club->name = $data['name'];
                $club->type = $data['type'];
                $club->facebook = (empty($data['facebook'])) ? null : $data['facebook'];
                $club->twitter = (empty($data['twitter'])) ? null : $data['twitter'];
                $club->begin = $data['begin'];
                $club->end = (empty($data['end'])) ? null : $data['end'];
                $club->email = (empty($data['email'])) ? null : $data['email'];
                $club->website = (empty($data['website'])) ? null : $data['website'];
                $club->content = $data['content'];
                $club->save();
                
                $this->view->message('Team ' . $club->name . ' updated successfully.', 'success');
                $this->_redirect('/clubs');
            } else {
                $form->populate($post);
            }
        }
        
        
        $this->view->club = $club;
        $this->view->form = $form;
    }

    public function clubsdeleteAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $clubTable = new Cupa_Model_DbTable_Club();
        $pageTable = new Cupa_Model_DbTable_Page();
        $userRoleTable = new Cupa_Model_DbTable_UserRole();

        $page = $pageTable->fetchBy('name', 'clubs');
        if(!Zend_Auth::getInstance()->hasIdentity() or
           Zend_Auth::getInstance()->hasIdentity() and
           (!$userRoleTable->hasRole($this->view->user->id, 'admin') and 
            !$userRoleTable->hasRole($this->view->user->id, 'editor') and
            !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id))) {
            $this->view->message('You either are not logged in or you do not have permission to edit this team.');
            $this->_redirect('/clubs');
        }

        $clubId = $this->getRequest()->getUserParam('club');
        if(is_numeric($clubId)) {
            $clubTable = new Cupa_Model_DbTable_Club();
            $club = $clubTable->find($clubId)->current();
            if($club) {
                $club->delete();
                
                $this->view->message('The ' . $club->name . ' club has been removed.', 'success');
            }
        }
        
        $this->_redirect('/clubs');
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
        
        $session = new Zend_Session_Namespace('newsbackbutton');
        $session->unsetAll();
    }

    public function newsAction()
    {
        $session = new Zend_Session_Namespace('newsbackbutton');
        if($_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/' or 
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews/youth') {
            $session->url = $_SERVER['HTTP_REFERER'];
        }
        
        $this->view->backUrl = $session->url;
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/news.css');
        
        $slug = $this->getRequest()->getUserParam('slug');
        $newsTable = new Cupa_Model_DbTable_News();
        $news = $newsTable->fetchNewsBySlug($slug);
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if($news and 
           (Zend_Auth::getInstance()->hasIdentity() and 
            ($userRoleTable->hasRole($this->view->user->id, 'reporter') or
             $userRoleTable->hasRole($this->view->user->id, 'admin')) or 
           $news->is_visible)) {            
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
        $slug = $this->getRequest()->getUserParam('slug');
        $newsTable = new Cupa_Model_DbTable_News();
        $news = $newsTable->fetchNewsBySlug($slug);
        
        $form = new Cupa_Form_News();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $news->is_visible = $post['is_visible'];
            $news->title = $post['title'];
            $news->slug = $newsTable->slugifyTitle($post['title']);
            $news->url = (empty($post['url'])) ? null : $post['url'];
            $news->info = $post['info'];
            $news->content = $post['content'];
            $news->edited_at = date('Y-m-d H:i:s');
            $news->type = $newsTable->getNewsType($news);
            $news->last_edited_by = $this->view->user->id;
            $news->save();

            $this->view->message('News item updated successfully.', 'success');
            $this->_redirect('/news/' . $news->slug);
        }
        
        if($news) {
            $form->loadFromNews($news);
            $this->view->news = $news;
        } else {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/newsedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');
        
        $this->view->form = $form;
    }
}
