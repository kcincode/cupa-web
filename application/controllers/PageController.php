<?php

class PageController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function homeAction()
    {
        if(strstr($_SERVER['HTTP_USER_AGENT'], 'chromeframe') === false && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            $this->view->message('You are using an IE browser or an older browser, to enhance your experience please download the google chrome frame <a href="http://www.google.com/chromeframe">here</a>', 'warning');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/home.css');

        // link to the db table
        $newsTable = new Model_DbTable_News();

        // initialize news array
        $newsCategoryTable = new Model_DbTable_NewsCategory();
        $allNews = array();
        foreach($newsCategoryTable->fetchAll() as $category) {
            $allNews[$category->name] = array();
        }

        // populate the news array
        foreach($newsTable->fetchNewsByCategory('all') as $news) {
            $allNews[$news['category']][] = $news;
        }

        // set the view variable
        $this->view->news = $allNews;

        $session = new Zend_Session_Namespace('newsbackbutton');
        $session->unsetAll();
    }

    public function viewAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);

        if(($page && $page->is_visible) ||
           ($page && !$page->is_visible && ($this->view->isViewable('page_edit')))) {
            if(!$page->is_visible) {
                $this->view->message('*** This page is not yet visible to the public ***', 'warning');
            }
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
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);

        if(!$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_PageEdit($page);
        $this->view->page = $page;

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/' . $page->name);
            }

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

                $this->view->message('Page updated', 'success');
                $this->_redirect('/' . $page->name);
            }
       }

        $this->view->form = $form;
    }

    public function adminAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $page = $this->getRequest()->getUserParam('page');
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', $page);

        if(!$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_PageAdmin($page);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/' . $page->name);
            }

            if($form->isValid($post)) {
                $page->parent = ($post['parent'] == 0) ? null : $post['parent'];
                $page->name = $post['name'];
                $page->is_visible = $post['is_visible'];
                $page->updated_at = date('Y-m-d H:i:s');
                $page->last_updated_by = $this->view->user->id;
                $page->save();

                $this->view->message('Page updated', 'success');
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        // initialize the contact form and add the users email if valid
        $form = new Form_Contact();

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

                Model_Email::sendContactEmail($data);
                $this->view->message('Email sent', 'success');
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $officerTable = new Model_DbTable_Officer();
        $this->view->officers = $officerTable->fetchAllOfficers();

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');
        $this->view->page = $page;
        $this->view->links = $pageTable->fetchChildren($page);
    }

    public function officerseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');

        $officerId = $this->getRequest()->getUserParam('officer');
        $officerTable = new Model_DbTable_Officer();
        $officer = $officerTable->find($officerId)->current();

        if(!$officer) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_OfficerEdit($officer);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/officers');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $officer->user_id = $data['user_id'];
                $officer->position = $data['position'];
                $officer->since = date('Y-m-d', strtotime($data['since']));
                $officer->to = (empty($data['to'])) ? null : date('Y-m-d', strtotime($data['to']));
                $officer->weight = $data['weight'];
                $officer->description = $data['description'];
                $officer->save();

                if(!empty($data['image'])) {
                    $destination = APPLICATION_WEBROOT . '/images/officers/' . $officer->user_id . '.jpg';

                    $simpleImage = new Model_SimpleImage();
                    $simpleImage->load($_FILES['image']['tmp_name']);
                    $simpleImage->resize(256, 256);
                    $simpleImage->save($destination);
                }

                $this->view->message('Officer updated', 'success');
                $this->_redirect('/officers');
            }
        }

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
        $this->view->form = $form;
    }

    public function officersaddAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datepicker.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'officers');
        $form = new Form_OfficerEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('officers');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $officerTable = new Model_DbTable_Officer();
                $officer = $officerTable->createRow();
                $officer->user_id = $data['user_id'];
                $officer->position = $data['position'];
                $officer->since = date('Y-m-d', strtotime($data['since']));
                $officer->to = (empty($data['to'])) ? null : date('Y-m-d', strtotime($data['to']));
                $officer->weight = $data['weight'];
                $officer->save();

                if(!empty($data['image'])) {
                    $destination = APPLICATION_WEBROOT . '/images/officers/' . $officer->user_id . '.jpg';

                    $simpleImage = new Model_SimpleImage();
                    $simpleImage->load($_FILES['image']['tmp_name']);
                    $simpleImage->resize(256, 256);
                    $simpleImage->save($destination);
                }

                $this->view->message('Officer created');
                $this->_redirect('officers');
            }
        }

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
        $this->view->form = $form;
    }

    public function officersdeleteAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $officerId = $this->getRequest()->getUserParam('officer');
        $officerTable = new Model_DbTable_Officer();
        $officer = $officerTable->find($officerId)->current();

        if(!$officer) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $officer->delete();
        $this->view->message('Officer deleted', 'success');
        $this->_redirect('/officers');
    }

    public function minutesAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $minuteTable = new Model_DbTable_Minute();
        $this->view->minutes = $minuteTable->fetchAllMinutes();

        $pageTable = new Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'board_meeting_minutes');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
    }

    public function minuteseditAction()
    {
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'board_meeting_minutes');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $minuteId = $this->getRequest()->getUserParam('minute');
        $minuteTable = new Model_DbTable_Minute();
        $minute = $minuteTable->find($minuteId)->current();

        if($minute) {
            $this->view->minute = $minute;
            $form = new Form_MinuteEdit($minute);

            if($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                if(isset($post['cancel'])) {
                    $this->_redirect('/board_meeting_minutes');
                }

                if($form->isValid($post)) {
                    $data = $form->getValues();

                    $minute->when = date('Y-m-d H:i:s', strtotime($data['when'] . ':00'));
                    $minute->location = $data['location'];
                    $minute->is_visible = $data['is_visible'];

                    if(!empty($data['pdf'])) {
                        $fp = fopen($_FILES['pdf']['tmp_name'], 'r');
                        if($fp) {
                            $minute->pdf = addslashes(fread($fp, $_FILES['pdf']['size']));
                            fclose($fp);
                        } else {
                            $this->view->message('Could not upload meeting mintues pdf.', 'error');
                        }
                    }

                    $minute->save();

                    $this->view->message('Meeting minutes updated.', 'success');
                    $this->_redirect('/board_meeting_minutes');
                }
            }

            $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
            $this->view->form = $form;
        }
    }

    public function minutesaddAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'board_meeting_minutes');
        $form = new Form_MinuteEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/board_meeting_minutes');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $minuteTable = new Model_DbTable_Minute();
                $minute = $minuteTable->createRow();
                $minute->location = $data['location'];
                $minute->when = date('Y-m-d H:i:s', strtotime($data['when']));
                $minute->pdf = null;
                $minute->is_visible = $data['is_visible'];

                if(!empty($data['pdf'])) {
                    $fp = fopen($_FILES['pdf']['tmp_name'], 'r');
                    if($fp) {
                        $minute->pdf = addslashes(fread($fp, $_FILES['pdf']['size']));
                        fclose($fp);
                    } else {
                        $this->view->message('Could not upload meeting mintues pdf.', 'error');
                    }
                }

                $minute->save();

                $this->view->message('Minutes created', 'success');
                $this->_redirect('/board_meeting_minutes');
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function minutesdeleteAction()
    {
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'board_meeting_minutes');

        $minuteId = $this->getRequest()->getUserParam('minute');
        $minuteTable = new Model_DbTable_Minute();
        $minute = $minuteTable->find($minuteId)->current();

        if($minute) {
            $minute->delete();
        }

        $this->view->message('Minutes deleted successful.', 'success');
        $this->_redirect('/board_meeting_minutes');
    }

    public function minutesdownloadAction()
    {
        $minuteId = $this->getRequest()->getUserParam('minute');

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $minuteTable = new Model_DbTable_Minute();
        $minute = $minuteTable->find($minuteId)->current();

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
        header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $minute->when) . '-' . str_replace(' ', '_', $minute->location) . '.pdf";');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . sizeof($boardMeeting->pdf));

        set_time_limit(0);
        echo stripslashes($minute->pdf);
        exit();

        return;
    }

    public function directorsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $tournamentTable = new Model_DbTable_Tournament();
        $this->view->tournaments = $tournamentTable->fetchAllTournamentsWithDirectors();

        $leagueTable = new Model_DbTable_League();
        $this->view->leagues = $leagueTable->fetchAllLeaguesWithDirectors();

        $pageTable = new Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'directors');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
    }

    public function pickupAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $pageTable = new Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'pickup');
        $this->view->links = $pageTable->fetchChildren($this->view->page);

        $pickupTable = new Model_DbTable_Pickup();
        $this->view->pickups = $pickupTable->fetchAllPickups();

        $tournamentTable = new Model_DbTable_Tournament();
        $this->view->tournaments = $tournamentTable->fetchAllTournamentsForPage();
    }

    public function pickupaddAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'pickup');

        $form = new Form_PickupEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/pickup');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $pickupTable = new Model_DbTable_Pickup();
                $pickup = $pickupTable->createRow();
                $pickup->title = $data['title'];
                $pickup->day = $data['day'];
                $pickup->time = $data['time'];
                $pickup->info = $data['info'];
                $pickup->user_id = ($data['user_id'] == 0) ? null : $data['user_id'];
                $pickup->email = (empty($data['email'])) ? null : $data['email'];
                $pickup->location = $data['location'];
                $pickup->map = (empty($data['map'])) ? null : $data['map'];
                $pickup->is_visible = $data['is_visible'];
                $pickup->weight = $pickupTable->fetchHighestWeight();
                $pickup->save();

                $this->view->message('Pickup created', 'success');
                $this->_redirect('/pickup');
            }
        }

        $this->view->form = $form;
    }

    public function pickupeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'pickup');

        $pickupTable = new Model_DbTable_Pickup();
        $pickup = $pickupTable->find($this->getRequest()->getUserParam('pickup'))->current();
        $form = new Form_PickupEdit($pickup);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/pickup');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $pickup->title = $data['title'];
                $pickup->day = $data['day'];
                $pickup->time = $data['time'];
                $pickup->info = $data['info'];
                $pickup->user_id = ($data['user_id'] == 0) ? null : $data['user_id'];
                $pickup->email = (empty($data['email'])) ? null : $data['email'];
                $pickup->location = $data['location'];
                $pickup->map = (empty($data['map'])) ? null : $data['map'];
                $pickup->weight = $data['weight'];
                $pickup->is_visible = $data['is_visible'];
                $pickup->save();

                $this->view->message('Pickup updated', 'success');
                $this->_redirect('/pickup');
            }
        }

        $this->view->form = $form;
    }

    public function pickupdeleteAction()
    {
        $pickupTable = new Model_DbTable_Pickup();
        $pickupId = $this->getRequest()->getUserParam('pickup');
        $pickup = $pickupTable->find($pickupId)->current();

        $pickup->delete();

        $this->view->message('Pickup deleted', 'success');
        $this->_redirect('/pickup');
    }

    public function clubsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $clubTable = new Model_DbTable_Club();
        $this->view->activeClubs = $clubTable->fetchAllByType('current');
        $this->view->pastClubs = $clubTable->fetchAllByType('past');

        $pageTable = new Model_DbTable_Page();
        $this->view->page = $pageTable->fetchBy('name', 'clubs');
        $this->view->links = $pageTable->fetchChildren($this->view->page);
    }

    public function clubsaddAction()
    {
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'clubs');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $form = new Form_ClubEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/clubs');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $clubTable = new Model_DbTable_Club();
                $club = $clubTable->createRow();
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

                $clubCaptainTable = new Model_DbTable_ClubCaptain();
                $clubCaptainTable->updateCaptains($data['captains'], $club->id);

                $this->view->message('Club Team created');
                $this->_redirect('/clubs');
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function clubseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $clubTable = new Model_DbTable_Club();
        $pageTable = new Model_DbTable_Page();

        $page = $pageTable->fetchBy('name', 'clubs');
        $clubId = $this->getRequest()->getUserParam('club');

        $club = $clubTable->find($clubId)->current();

        if(!$club) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $form = new Form_ClubEdit($club);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/clubs');
            }

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

                $clubCaptainTable = new Model_DbTable_ClubCaptain();
                $clubCaptainTable->updateCaptains($data['captains'], $club->id);

                $this->view->message('Team ' . $club->name . ' updated', 'success');
                $this->_redirect('/clubs');
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function clubsdeleteAction()
    {
        // disable the layout
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $clubTable = new Model_DbTable_Club();
        $pageTable = new Model_DbTable_Page();

        $page = $pageTable->fetchBy('name', 'clubs');

        $clubId = $this->getRequest()->getUserParam('club');
        if(is_numeric($clubId)) {
            $clubTable = new Model_DbTable_Club();
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $category = $this->getRequest()->getUserParam('category');
        $this->view->category = ucwords($category);

        $newsTable = new Model_DbTable_News();
        $this->view->news = $newsTable->fetchNewsByCategory($category, false, true);

        if(!count($this->view->news)) {
            $this->_redirect('allnews');
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

        $this->view->backUrl = (empty($session->url)) ? $this->view->baseUrl() . '/allnews' : $session->url;
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $slug = $this->getRequest()->getUserParam('slug');
        $newsTable = new Model_DbTable_News();
        $news = $newsTable->fetchNewsBySlug($slug);

        if($news && $this->view->isViewable('news_view')) {
            if(!$news->is_visible) {
                $this->view->message('*** This news story is not yet visible to the public ***', 'warning');
            }

            $this->view->news = $news;
        } else {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('News item not found');
        }
    }

    public function newsaddAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $form = new Form_News();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/allnews');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $newsTable = new Model_DbTable_News();

                $test = $newsTable->fetchNewsBySlug($newsTable->slugifyTitle($data['title']));
                if(empty($test)) {

                    $news = $newsTable->createRow();
                    $news->is_visible = $data['is_visible'];
                    $news->category_id = $data['category'];
                    $news->title = $data['title'];
                    $news->slug = $newsTable->slugifyTitle($data['title']);
                    $news->url = (empty($data['url'])) ? null : $data['url'];
                    $news->info = $data['info'];
                    $news->content = $data['content'];
                    $news->posted_by = $this->view->user->id;
                    $news->edited_at = date('Y-m-d H:i:s');
                    $news->type = $newsTable->getNewsType($data);
                    $news->last_edited_by = $this->view->user->id;
                    $news->posted_at = date('Y-m-d H:i:s');
                    $news->remove_at = (empty($post['remove_at'])) ? null : date('Y-m-d H:i:s', strtotime($post['remove_at']));
                    $news->save();

                    $this->view->message('News item created');
                    $this->_redirect('/news/' . $news->slug);
                } else {
                    $this->view->message('News title already exists please try a different one.', 'error');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function newseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');

        $slug = $this->getRequest()->getUserParam('slug');
        $newsTable = new Model_DbTable_News();
        $news = $newsTable->fetchNewsBySlug($slug);

        $form = new Form_News($news);

        if($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/news/' . $news->slug);
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $news->is_visible = $data['is_visible'];
                $news->category_id = $data['category'];
                $news->title = $data['title'];
                $news->slug = $newsTable->slugifyTitle($data['title']);
                $news->url = (empty($data['url'])) ? null : $data['url'];
                $news->info = $data['info'];
                $news->content = $data['content'];
                $news->edited_at = date('Y-m-d H:i:s');
                $news->type = $newsTable->getNewsType($data);
                $news->last_edited_by = $this->view->user->id;
                $news->remove_at = (empty($data['remove_at']) or $data['remove_at'] == '0000-00-00 00:00:00') ? null : date('Y-m-d H:i:s', strtotime($data['remove_at']));
                $news->save();

                $this->view->message('News item updated', 'success');
                $this->_redirect('/news/' . $news->slug);
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->form = $form;
    }

    public function formsAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $type = $this->getRequest()->getUserParam('type');
        $year = $this->getRequest()->getUserParam('year');

        $formTable = new Model_DbTable_Form();
        $this->view->forms = $formTable->fetchForms($type, $year);

        if($type != 'all' and $year != 0) {
            $form = $this->view->forms;

            if(empty($form)) {
                $this->_redirect('forms');
            }

            // download the form
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
            header('Content-Disposition: attachment; filename="' . $form['year'] . '_' . $form['name'] . '.' . $form['type'] . '";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo stripslashes($form['data']);
            flush();

            return;
        }
    }

    public function formsaddAction()
    {
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'forms');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $form = new Form_FormEdit();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/forms');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $formTable = new Model_DbTable_Form();
                $formData = $formTable->createRow();
                $formData->year = $data['year'];
                $formData->name = $data['name'];

                if(!empty($data['file'])) {
                    $fp = fopen($_FILES['file']['tmp_name'], 'r');
                    $filesize = $_FILES['file']['size'];
                    $md5 = md5_file($_FILES['file']['tmp_name']);

                    $bootstrap = $this->getInvokeArg('bootstrap');
                    $validForms = explode(',', $bootstrap->getOption('validForms'));

                    if(!$formTable->isUnique($md5)) {
                        $this->view->message('The uploaded file is a duplicate of another file already uploaded.', 'warning');
                    } else {
                        if($fp) {
                            $extension = strtolower(end(explode('.', $data['file'])));
                            if(in_array($extension, $validForms)) {
                                $formData->md5 = $md5;
                                $formData->size = $filesize;
                                $formData->data = addslashes(fread($fp, $filesize));
                                $formData->type = $extension;
                                $formData->save();
                            } else {
                                $this->view->message('The uploaded file is not a valid type.', 'warning');
                            }
                            fclose($fp);
                        }
                    }
                }

                $formData->modified_at = date('Y-m-d H:i:s');
                $formData->uploaded_at = date('Y-m-d H:i:s');
                $formData->modified_by = $this->view->user->id;
                $formData->save();

                $this->view->message('Form created', 'success');
                $this->_redirect('/forms');
            }
        }

        $this->view->form = $form;
    }

    public function formseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $formId = $this->getRequest()->getUserParam('form_id');

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'forms');

        if(!$formId or !$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $formTable = new Model_DbTable_Form();
        $formData = $formTable->find($formId)->current();
        $form = new Form_FormEdit($formData, $this->view->user->id);
        $bootstrap = $this->getInvokeArg('bootstrap');
        $validForms = explode(',', $bootstrap->getOption('validForms'));

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/forms');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $update = 0;

                if(!empty($data['file'])) {
                    $fp = fopen($_FILES['file']['tmp_name'], 'r');
                    $filesize = $_FILES['file']['size'];
                    $md5 = md5_file($_FILES['file']['tmp_name']);

                    if(!$formTable->isUnique($md5, $formId)) {
                        $this->view->message('The uploaded file is a duplicate of another file already uploaded.', 'warning');
                    } else {
                        if($fp) {
                            $extension = strtolower(end(explode('.', $data['file'])));
                            if(in_array($extension, $validForms)) {
                                $formData->md5 = $md5;
                                $formData->size = $filesize;
                                $formData->data = addslashes(fread($fp, $filesize));
                                $formData->type = $extension;
                                $formData->save();
                                $update = 1;
                            } else {
                                $this->view->message('The uploaded file is not a valid type.', 'warning');
                            }
                            fclose($fp);
                        }
                    }
                }

                if($data['name'] != $formData->name or $data['year'] != $formData->year) {
                    $formTable->udpateForm($data['year'], $data['name']);
                    $update = 1;
                }

                if($update == 1) {
                    $formData->modified_at = date('Y-m-d H:i:s');
                    $formData->modified_by = $this->view->user->id;
                    $formData->save();
                    $this->view->message('Form ' . $formData->year . ' ' . $formData->name . ' updated', 'success');
                    $this->_redirect('/forms');
                }
            }
        }

        $this->view->form = $form;
    }

    public function formsdeleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $formTable = new Model_DbTable_Form();
        $formId = $this->getRequest()->getUserParam('form_id');
        $form = $formTable->find($formId)->current();

        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'forms');

        if(!$form or !$page) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->message('Form ' . $form->year . '_' . $form->name . ' deleted', 'success');
        $form->delete();
        $this->_redirect('forms');
    }

    public function tournamentaddAction()
    {
        $pageTable = new Model_DbTable_Page();
        $page = $pageTable->fetchBy('name', 'pickup');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $form = new Form_TournamentCreate();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/pickup');
            }

            if($post['name'] != 'new' and $post['name'] != 0) {
                $form->getElement('new_name')->setRequired(false);
                $post['new_name'] = null;
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $tournamentTable = new Model_DbTable_Tournament();
                if($data['name'] == 'new') {
                    $data['name'] = $data['new_name'];
                } else {
                    $tmp = $tournamentTable->find($data['name'])->current();
                    $data['name'] = $tmp->name;
                    unset($tmp);
                }

                $tournament = $tournamentTable->createBlankTournament($data['year'], $data['name'], $data['directors']);
                if($tournament) {
                    $this->view->message('Tournament Created', 'success');
                    $this->_redirect('/tournament/' .  $data['name'] . '/' . $data['year']);
                } else {
                    $this->view->message('Tournament Already Exists', 'error');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function paypalAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();
        $id = $request->getUserParam('id');
        $type = $request->getUserParam('type');
        $userId = $request->getUserParam('user_id');

        $paypalConfig = $this->getInvokeArg('bootstrap')->getOption('paypal');

        if(!$userId) {
            $userId = (Zend_Auth::getInstance()->hasIdentity()) ? Zend_Auth::getInstance()->getIdentity() : 0;
        }

        $server = (APPLICATION_ENV == 'production') ? 'http://cincyultimate.org' : 'http://localhost/cupa';
        $paypalConfig['return_url'] = $server . '/paypal_success/' . $id . '/' . $type . '/' . $userId;
        $paypalConfig['cancel_url'] = $server . '/paypal_fail/' . $id . '/' . $type;
        $paypalConfig['use_proxy'] = null;
        $paypalConfig['proxy_host'] = null;
        $paypalConfig['proxy_port'] = null;

        $paypal = new Model_Paypal($paypalConfig, (APPLICATION_ENV == 'production') ? false : true);
        $cost = 0;
        switch($type) {
            case 'league':
                $leagueInformationTable = new Model_DbTable_LeagueInformation();
                $info = $leagueInformationTable->fetchInformation($id);
                if(isset($info->cost)) {
                    $cost = $info->cost;
                }
                $paypal->description = $this->view->leaguename($id, true, true, true, true) . ' - $' . $cost;
                $redirect = '/league/' . $id . '/register_success';
                break;
            case 'tournament':
                $tournamentInformationTable = new Model_DbTable_TournamentInformation();
                $info = $tournamentInformationTable->fetchInfo($id);
                if(isset($info->cost)) {
                    $cost = $info->cost;
                }
                $tournamentTable = new Model_DbTable_Tournament();
                $tournamentTeamTable = new Model_DbTable_TournamentTeam();
                $tournament = $tournamentTable->find($id)->current();

                $team = $tournamentTeamTable->find($request->getParam('team_id'))->current();
                $paypal->description = $tournament->display_name . ' ' . $tournament->year . ' - ' . $team->name . ' - $' . $cost;

                $paypal->return_url = $paypalConfig['return_url'] . '/' . $request->getParam('team_id');
                $redirect = '/tournament/' . $tournament->name . '/' . $tournament->year . '/payment';
                break;
        }

        if(!Zend_Auth::getInstance()->hasIdentity() && $type != 'tournament') {
            $this->view->message('You must be logged in to pay via paypal.', 'error');
            $this->_redirect($redirect);
        }

        // if cost == 0 or not set
        if(empty($cost)) {
            $this->view->message('Error trying to pay with paypal.', 'error');
            $this->_redirect($redirect);
        }

        $paypal->amount_total = $cost;
        $paypal->set_express_checkout();

        if(!$paypal->_error) {
            $_SESSION['token'] = $paypal->token;
            $paypal->set_express_checkout_successful_redirect();
            return;
        }

        $this->view->message('Error trying to pay with paypal.', 'error');
        $this->_redirect($redirect);
    }

    public function paypalsuccessAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();
        $id = $request->getUserParam('id');
        $type = $request->getUserParam('type');
        $userId = $request->getUserParam('user_id');

        $paypalConfig = $this->getInvokeArg('bootstrap')->getOption('paypal');
        $paypal = new Model_Paypal($paypalConfig, (APPLICATION_ENV == 'production') ? false : true);
        $paypal->token = $request->getParam('token');

        if($paypal->get_express_checkout_details()) {
            $data = array();
            $data['confirm'] = Zend_Json::encode($paypal->Response);
            $paypal->amount_total = $paypal->Response['AMT'];
            if($paypal->do_express_checkout_payment()) {
                $data['complete'] = Zend_Json::encode($paypal->Response);
            }
            $data = implode('::', $data);
            $paypalTable = new Model_DbTable_Paypal();
            $paypalId = $paypalTable->log($userId, $id, $type, $data);
        }

        switch($type) {
            case 'league':
                $redirect = 'league/' . $id . '/register_success';

                $leagueMemberTable = new Model_DbTable_LeagueMember();
                $member = $leagueMemberTable->fetchMember($id, $userId);
                $member->paid = 1;
                $member->save();

                break;
            case 'tournament':
                $tournamentTable = new Model_DbTable_Tournament();
                $tournamentTeamTable = new Model_DbTable_TournamentTeam();
                $tournament = $tournamentTable->find($id)->current();

                $team = $tournamentTeamTable->find($request->getParam('team_id'))->current();
                $team->paid = 1;
                $team->save();

                $redirect = 'tournament/' . $tournament->name . '/' . $tournament->year . '/teams';
                break;
        }

        $this->view->message('Your payment has been completed.', 'success');
        $this->_redirect($redirect);
    }

    public function paypalfailAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();
        $id = $request->getUserParam('id');
        $type = $request->getUserParam('type');

        switch($type) {
            case 'league':
                $redirect = 'league/' . $id . '/register_success';
                break;
            case 'tournament':
                $tournamentTable = new Model_DbTable_Tournament();
                $tournament = $tournamentTable->find($id)->current();
                $redirect = 'tournament/' . $tournament->name . '/' . $tournament->year . '/payment';
                break;
        }

        $paypalConfig = $this->getInvokeArg('bootstrap')->getOption('paypal');
        $paypal = new Model_Paypal($paypalConfig, (APPLICATION_ENV == 'production') ? false : true);
        $paypal->token = $request->getParam('token');

        if($paypal->get_express_checkout_details()) {
            $this->view->message('You did not complete the transaction.', 'error');
        } else {
            $this->view->message('Error occured with paypal.', 'error');
        }

        $this->_redirect($redirect);
    }
}
