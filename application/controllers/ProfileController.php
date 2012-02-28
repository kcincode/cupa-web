<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/common.css');
    }

    public function indexAction()
    {
        $state = $this->getRequest()->getUserParam('state');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/' . $state . '.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/profile/' . $state . '.js');

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userTable = new Model_DbTable_User();
        $this->view->data = $userTable->fetchProfile($this->view->user);
        $form = new Form_Profile($this->view->user, $state);

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                $this->$state($this->view->user, $data);
            } else {
                $this->view->message('There are errors with your submission.', 'error');
                $form->populate($post);
            }
        }

        $this->view->form = $form;
        $this->view->state = $state;
        $this->renderScript('profile/' . $state . '.phtml');
    }

    private function personal($user, $data)
    {
        foreach($data as $key => $value) {
            $user->username = $data['username'];
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->updated_at = date('Y-m-d H:i:s');
            $user->save();

            $userProfileTable = new Model_DbTable_UserProfile();
            $userProfile = $userProfileTable->find($user->id)->current();
            $userProfile->gender = $data['gender'];
            $userProfile->birthday = $data['birthday'];
            $userProfile->phone = $data['phone'];
            $userProfile->nickname = (empty($data['nickname'])) ? null : $data['nickname'];
            $userProfile->height = $data['height'];
            $userProfile->level = $data['level'];
            $userProfile->experience = $data['experience'];
            $userProfile->save();

        }
        $this->view->message('Personal profile updated.', 'success');
    }

    public function minorsaddAction()
    {
        // disable the layout and view
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $userTable = new Model_DbTable_User();
        $minor = $userTable->createBlankMinor($this->view->user->id);
        if(!$minor) {
            $this->view->message('Could not create minor, please edit the previously created minor before trying to add another.', 'error');
        } else {
            $this->_redirect('profile/minors/' . $minor->id . '/edit');
        }
        $this->_redirect('profile/minors');
    }

    public function minorseditAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/profile/personal.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/profile/personal.js');

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

                foreach($data as $key => $value) {
                    $minor->first_name = $data['first_name'];
                    $minor->last_name = $data['last_name'];
                    $minor->updated_at = date('Y-m-d H:i:s');
                    $minor->save();

                    $userProfileTable = new Model_DbTable_UserProfile();
                    $userProfile = $userProfileTable->find($minor->id)->current();
                    $userProfile->gender = $data['gender'];
                    $userProfile->birthday = $data['birthday'];
                    $userProfile->nickname = (empty($data['nickname'])) ? null : $data['nickname'];
                    $userProfile->height = $data['height'];
                    $userProfile->level = $data['level'];
                    $userProfile->experience = $data['experience'];
                    $userProfile->save();

                }
                $this->view->message('Minor information updated.', 'success');
                $this->_redirect('profile/minors');

            } else {
                $this->view->message('There are errors with your submission.', 'error');
                $form->populate($post);
            }
        }

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

}
