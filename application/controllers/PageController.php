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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/officers.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/officers.js');

        $officerTable = new Cupa_Model_DbTable_Officer();
        $this->view->officers = $officerTable->fetchAllOfficers();
        
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');
        $this->view->page = $page;
        $this->view->links = $pageTable->fetchChildren($page);
    }

    public function officerseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/officersedit.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/officersedit.js');

        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');

        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id) and
             !$userRoleTable->hasRole($this->view->user->id, 'admin')))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $officerId = $this->getRequest()->getUserParam('officer');
        $officerTable = new Cupa_Model_DbTable_Officer();
        $officer = $officerTable->find($officerId)->current();
        
        if(!$officer) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $form = new Cupa_Form_OfficerEdit();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                $officer->user_id = $data['user_id'];
                $officer->position = $data['position'];
                $officer->since = $data['since'];
                $officer->to = (empty($data['to'])) ? null : $data['to'];
                $officer->weight = $data['weight'];
                $officer->save();
                
                $this->view->message('Officer updated successfully.', 'success');
                $this->_redirect('/officers');
            } else {
                $form->populate($post);
            }
        }
        
        $form->loadFromOfficer($officer);
        $this->view->form = $form;
    }

    public function officersaddAction()
    {
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');

        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id) and
             !$userRoleTable->hasRole($this->view->user->id, 'admin')))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/officers');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();
        
        $officerTable = new Cupa_Model_DbTable_Officer();
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $officer = $officerTable->createRow();
            $officer->user_id = null;
            $officer->position = $post['position'];
            $officer->since = date('Y-m-d');
            $officer->to = null;
            $officer->weight = $officerTable->getNextWeight($post['position']);
            $officer->save();
            
            $this->view->message('Officer created successfully.');
            echo Zend_Json::encode(array('result' => 'success', 'data' => $officer->id));
        }
    }
    
    public function officersdeleteAction()
    {
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
             !$userRoleTable->hasRole($this->view->user->id, 'admin'))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $officerId = $this->getRequest()->getUserParam('officer');
        $officerTable = new Cupa_Model_DbTable_Officer();
        $officer = $officerTable->find($officerId)->current();
        
        if(!$officer) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $officer->delete();
        $this->view->message('Officer deleted successfully.', 'success');
        $this->_redirect('/officers');
    }

    public function minutesAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/minutes.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/minutes.js');
        
        $minuteTable = new Cupa_Model_DbTable_Minute();
        $this->view->minutes = $minuteTable->fetchAllMinutes();
        
        $pageTable = new Cupa_Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'board_meeting_minutes');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
    }

    public function minuteseditAction()
    {
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'board_meeting_minutes');
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'admin') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'edior', $page->id)))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/minutesedit.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jquery-ui-timepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/minutesedit.js');
        
        $minuteId = $this->getRequest()->getUserParam('minute');
        $minuteTable = new Cupa_Model_DbTable_Minute();
        $minute = $minuteTable->find($minuteId)->current();

        if($minute) {
            $this->view->minute = $minute;
            $form = new Cupa_Form_MinuteEdit();
            $form->loadFromMinute($minute);

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                if($form->isValid($post)) {
                    $data = $form->getValues();

                    $minute->when = $data['when'];
                    $minute->location = $data['location'];
                    $minute->is_visible = $data['is_visible'];
                    
                    if(!empty($data['pdf'])) {
                        if(file_exists($_FILES['pdf']['tmp_name'])) {
                            $fp = fopen($_FILES['pdf']['tmp_name'], 'r');
                        } else {
                            $fp = fopen('/tmp/' . $_FILES['pdf']['name'], 'r');
                        }
                        if($fp) {
                            $minute->pdf = addslashes(fread($fp, $_FILES['pdf']['size']));
                            fclose($fp);
                        } else {
                            $this->view->message('Could not upload meeting mintues pdf.', 'error');
                        }
                    }

                    $minute->save();
                    $this->view->message('Meeting minutes updated successfully.', 'success');
                    //$this->_redirect('/board_meeting_minutes');
                } else {
                    $form->populate($post);
                }
            }

            $this->view->form = $form;
        }
    }

    public function minutesaddAction()
    {
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'board_meeting_minutes');
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'admin') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'edior', $page->id)))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $minuteTable = new Cupa_Model_DbTable_Minute();
            $minute = $minuteTable->createRow();
            $minute->location = $post['location'];
            $minute->when = date('Y-m-d H:i:s');
            $minute->pdf = null;
            $minute->is_visible = 0;
            $minute->save();

            $this->view->message('Minutes created successfully.', 'success');
            echo Zend_Json::encode(array('result' => 'success', 'data' => $minute->id));
        }

        // disable the layout
        $this->_helper->layout()->disableLayout();
    }
    
    public function minutesdownloadAction()
    {
        $minuteId = $this->getRequest()->getUserParam('minute');
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $minuteTable = new Cupa_Model_DbTable_Minute();
        $minute = $minuteTable->find($minuteId)->current();

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
        header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $minute->when) . '-' . str_replace(' ', '_', $minute->location) . '.pdf";');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . sizeof($boardMeeting->pdf));

        set_time_limit(0);
        echo stripslashes($minute->pdf);
        flush();

        return;
    }

    public function directorsAction()
    {
        // action body
    }

    public function pickupAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/pickup.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/pickup.js');


        $pageTable = new Cupa_Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'pickup');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
        
        $pickupTable = new Cupa_Model_DbTable_Pickup();
        $this->view->pickups = $pickupTable->fetchAllPickups();
    }

    public function pickupaddAction()
    {
        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'pickup');
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'admin') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id)))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }
        
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $pickupTable = new Cupa_Model_DbTable_Pickup();
            
            if($pickupTable->isUnique($post['pickup'])) {
                $pickup = $pickupTable->createRow();
                $pickup->title = $post['pickup'];
                $pickup->day = 'Unknown';
                $pickup->time = 'Unknown';
                $pickup->info = '';
                $pickup->user_id = null;
                $pickup->email = null;
                $pickup->location = 'Unknown';
                $pickup->map = null;
                $pickup->is_visible = 0;
                $pickup->save();

                $this->view->message('Pickup created successfully.', 'success');
                echo Zend_Json::encode(array('result' => 'success', 'data' => $pickup->id));
                return;
            } else {
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'Pickup Already Exists'));
                return;
            }
        }
    }

    public function pickupeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/pickupedit.css');
        
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/tinymce/tiny_mce.js');

        $pageTable = new Cupa_Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'pickup');
        
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'admin') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor') and
             !$userRoleTable->hasRole($this->view->user->id, 'editor', $page->id)))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $pickupTable = new Cupa_Model_DbTable_Pickup();
        $form = new Cupa_Form_PickupEdit();
        $pickupId = $this->getRequest()->getUserParam('pickup');
        $pickup = $pickupTable->find($pickupId)->current();
        $form->loadFromPickup($pickup);
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            $pickup->title = $post['title'];
            $pickup->day = $post['day'];
            $pickup->time = $post['time'];
            $pickup->info = $post['info'];
            $pickup->user_id = ($post['user_id'] == 0) ? null : $post['user_id'];
            $pickup->email = (empty($post['email'])) ? null : $post['email'];
            $pickup->location = $post['location'];
            $pickup->map = (empty($post['map'])) ? null : $post['map'];
            $pickup->is_visible = $post['is_visible'];
            $pickup->save();

            $this->view->message('Pickup updated successfully.', 'success');
            $this->_redirect('/pickup');
        }
        
        $this->view->pickup = $pickup;
        $this->view->form = $form;
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

    public function allnewsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/allnews.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/page/news.js');

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
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews/youth' or 
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews/leagues' or 
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews/pickup' or 
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews/info' or 
           $_SERVER['HTTP_REFERER'] == 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl() . '/allnews') {
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
            throw new Zend_Controller_Dispatcher_Exception('News item not found');
        }
    }

    public function newsaddAction()
    {
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'reporter') and
             !$userRoleTable->hasRole($this->view->user->id, 'admin')))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('News item not found');
        }
        
        // make sure its an AJAX request
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/allnews');
        }
        
        // disable the layout
        $this->_helper->layout()->disableLayout();
        
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $this->_helper->viewRenderer->setNoRender(true);
            $newsTable = new Cupa_Model_DbTable_News();
            
            if($newsTable->isUnique($post['title'])) {
                $news = $newsTable->createRow();
                $news->title = $post['title'];
                $news->is_visible = 0;
                $news->slug = $newsTable->slugifyTitle($post['title']);
                $news->url = null;
                $news->info = '';
                $news->content = '';
                $news->type = $newsTable->getNewsType($news);
                $news->last_edited_by = $this->view->user->id;
                $news->edited_at = date('Y-m-d H:i:s');
                $news->save();
            
                $this->view->message('News item created successfully.');
                echo Zend_Json::encode(array('result' => 'success', 'data' => $news->id));
            } else {
                $this->_helper->viewRenderer->setNoRender(true);
                echo Zend_Json::encode(array('result' => 'error', 'message' => 'News Title Already Exists'));
                return;
            }                    
        }
        
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    }

    public function newseditAction()
    {
        $userRoleTable = new Cupa_Model_DbTable_UserRole();
        if((!Zend_Auth::getInstance()->hasIdentity() or 
            (!$userRoleTable->hasRole($this->view->user->id, 'reporter') and
             !$userRoleTable->hasRole($this->view->user->id, 'admin')))) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('News item not found');
        }
        
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
