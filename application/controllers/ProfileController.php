<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datepicker.js');
    }

    public function indexAction()
    {
        $state = $this->getRequest()->getUserParam('state');

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userTable = new Model_DbTable_User();
        $this->view->data = $userTable->fetchProfile($this->view->user);
        $form = new Form_Profile($this->view->user, $state);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($state == 'contacts') {
                $this->$state($this->view->user, $post);
            } else {
                if($form->isValid($post)) {
                    $data = $form->getValues();
                    
                    $this->$state($this->view->user, $data);
                } else {
                    $this->view->message('There are errors with your submission.', 'error');
                    $form->populate($post);
                }
            }
        }

        $this->view->form = $form;
        $this->view->state = $state;
        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
    }

    private function personal($user, $data)
    {
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->updated_at = date('Y-m-d H:i:s');
        $user->save();

        $userProfileTable = new Model_DbTable_UserProfile();
        $userProfile = $userProfileTable->find($user->id)->current();
        $userProfile->gender = $data['gender'];
        $userProfile->birthday = date('Y-m-d', strtotime($data['birthday']));
        $userProfile->phone = $data['phone'];
        $userProfile->nickname = (empty($data['nickname'])) ? null : $data['nickname'];
        $userProfile->height = $data['height'];
        $userProfile->level = $data['level'];
        $userProfile->experience = $data['experience'];
        $userProfile->save();

        $this->view->message('Personal profile updated.', 'success');
    }

    public function minorsaddAction()
    {
        $form = new Form_Profile($this->view->user, 'minors_add');
        
        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();
            
            if(isset($post['cancel'])) {
                $this->_redirect('profile/minors');
            }
            
            if($form->isValid($post)) {
                $data = $form->getValues();
                
                $userTable = new Model_DbTable_User();
                $userTable->createMinor($this->view->user->id, $data);
                
                $this->view->message('Created minor', 'success');
                $this->_redirect('profile/minors');
            }
        }

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
        $this->view->form = $form;
    }

    public function minorseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');

        $minorId = $this->getRequest()->getUserParam('minor_id');

        $userTable = new Model_DbTable_User();
        $minor = $userTable->find($minorId)->current();
        $this->view->data = $userTable->fetchProfile($minor);

        if(!isset($minor->parent) or $this->view->user->id != $minor->parent) {
            $this->_redirect('profile/minors');
        }

        $form = new Form_Profile($minor, 'minors_edit');
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('profile/minors');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                $minor->first_name = $data['first_name'];
                $minor->last_name = $data['last_name'];
                $minor->updated_at = date('Y-m-d H:i:s');
                $minor->save();

                $userProfileTable = new Model_DbTable_UserProfile();
                $userProfile = $userProfileTable->find($minor->id)->current();
                $userProfile->gender = $data['gender'];
                $userProfile->birthday = date('Y-m-d', strtotime($data['birthday']));
                $userProfile->nickname = (empty($data['nickname'])) ? null : $data['nickname'];
                $userProfile->height = $data['height'];
                $userProfile->level = $data['level'];
                $userProfile->experience = $data['experience'];
                $userProfile->save();

                $this->view->message('Minor information updated.', 'success');
                $this->_redirect('profile/minors');

            }
        }

        $this->view->headScript()->appendScript('$(".datepicker").datepicker();');
        $this->view->form = $form;
    }

    public function publicAction()
    {
        $session = new Zend_Session_Namespace('previous');
        $this->view->previousPage = $session->previousPage;

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/public.css');

        $userId = $this->getRequest()->getUserParam('user_id');
        $userTable = new Model_DbTable_User();
        $user = $userTable->find($userId)->current();

        if(!$user) {
            // throw a 404 error if the page cannot be found
            throw new Zend_Controller_Dispatcher_Exception('Page not found');
        }

        $this->view->data = $userTable->fetchProfile($user);
    }

    public function leagueeditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/league/register.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/league/register.js');

        $leagueId = $this->getRequest()->getUserParam('league_id');
        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($leagueId)->current();


        if(!$league or !Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('profile/leagues');
        }

        $form = new Form_Profile($this->view->user, 'league_edit', $leagueId);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('profile/leagues');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
                $leagueMemberTable = new Model_DbTable_LeagueMember();
                $leagueQuestionTable = new Model_DbTable_LeagueQuestion();

                $leagueMember = $leagueMemberTable->fetchMember($leagueId, $this->view->user->id);
                if($leagueMember) {
                    foreach($data as $key => $value) {
                        $leagueQuestion = $leagueQuestionTable->fetchQuestion($key);

                        if($leagueQuestion) {
                            $leagueAnswerTable->addAnswer($leagueMember->id, $leagueQuestion->id, $value);
                        }
                    }

                    $this->view->message('Updated league answers.', 'success');
                    $this->_redirect('profile/leagues');
                }
            } else {
                $this->view->message('There are errors with your submission.', 'error');
                $form->populate($post);
            }
        }

        $this->view->league = $league;
        $this->view->form = $form;
    }

    public function contactsaddAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userEmergencyTable = new Model_DbTable_UserEmergency();
        $contact = $userEmergencyTable->createBlankContact($this->view->user->id);
        if(!$contact) {
            $this->view->message('Could not create contact, please edit the previously created minor before trying to add another.', 'error');
        }

        $this->_redirect('profile/contacts');
    }
    public function contactsremoveAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $contactId = $this->getRequest()->getUserParam('contact_id');
        if(is_numeric($contactId)) {
            $userEmergencyTable = new Model_DbTable_UserEmergency();
            $contact = $userEmergencyTable->find($contactId)->current();
            if($contact) {
                $contact->delete();
                $this->view->message('User emergency contact removed', 'success');
            }

            $this->_redirect('/profile/contacts');
        }
    }

    public function contacts($user, $data)
    {
        $userEmergencyTable = new Model_DbTable_UserEmergency();
        $results = array();
        $weight = 0;
        foreach($data['name'] as $name) {
            $nameParts = explode(' ', $name);


            if($this->isValidContact($name, $data['phone'][$weight])) {
                $results[] = array(
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1],
                    'phone' => $data['phone'][$weight],
                    'weight' => $weight,
                );
                $weight++;
            } else {
                break;
            }
        }

        if($weight == count($data['name'])) {
            $userEmergencyTable->updateContacts($user->id, $data['name'], $data['phone']);
            $this->view->message('Emergency contacts updated', 'success');
            $this->_redirect('profile/contacts');
        }
    }

    private function isValidContact($name, $phone)
    {
        $ignoredPhones = array(
            '513-555-5555',
            '555-555-5555',
            '555-555-1234',
            '000-000-0000',
            '111-111-1111',
        );


        $nameParts = explode(' ', $name);
        if(count($nameParts) != 2) {
            $this->view->message('You must enter a first AND last name for each contact', 'error');
            return false;
        }

        if($nameParts[0] == 'Contact' or $nameParts[1] == 'Name' or empty($name)) {
            $this->view->message('You must enter a valid contact names.', 'error');
            return false;
        }

        if(in_array($phone, $ignoredPhones) or empty($phone) or $phone == 'phone') {
            $this->view->message('You must enter a valid phone numbers.', 'error');
            return false;
        }

        return true;
    }

    public function passwordAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/password.css');

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/profile');
        }

        $userTable = new Model_DbTable_User();
        $this->view->data = $userTable->fetchProfile($this->view->user);
        $form = new Form_Profile($this->view->user, 'password');

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('/profile');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // check current password
                $authentication = new Model_Authenticate($this->view->user);
                // try the password for authentication
                if(!$authentication->authenticate($data['current'])) {
                    $this->view->message('Current password entered wrong.', 'error');
                    $form->populate($post);
                } else {
                    // check that passwords match
                    if($data['password'] == $data['confirm']) {
                        $this->view->user->password = sha1($this->view->user->salt . $data['password']);
                        $this->view->user->save();
                        $this->view->message('Password updated.', 'success');
                        $this->_redirect('/profile');
                    } else {
                        $this->view->message('New passwords do not match.', 'error');
                        $form->populate($post);
                    }
                }
            } else {
                $form->populate($post);
            }
        }

        $this->view->form = $form;
    }
}
