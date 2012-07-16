<?php

class ClubController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/smoothness/smoothness.css');
    	$this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/club/index.css');

    	$this->view->headScript()->appendFile($this->view->baseUrl() . '/js/club/index.js');

    	$clubId = $this->getRequest()->getUserParam('club_id');
    	$clubTable = new Model_DbTable_Club();
    	$this->view->club = $clubTable->find($clubId)->current();

    	$clubMemberTable = new Model_DbTable_ClubMember();
    	$this->view->years = $clubMemberTable->fetchAllMemberByYear($clubId);
    }

    public function editAction()
    {

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/chosen.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/club/edit.css');

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/chosen.jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/club/edit.js');

        $clubId = $this->getRequest()->getUserParam('club_id');
        $this->view->year = $this->getRequest()->getUserParam('year');

        if(!$this->view->isClubCaptain($clubId)) {
            $this->view->message('You must have access to edit the roster', 'error');
            $this->_redirect('club/' . $clubId . '#' . $this->view->year);
        }

        $clubMemberTable = new Model_DbTable_ClubMember();
        $form = new Form_ClubMember($clubId, $this->view->year);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $result = $clubMemberTable->addMember($post['club_id'], $post['user_id'], $post['year']);
                if($result) {
                    $this->view->message('User added.', 'success');
                } else {
                    $this->view->message('User not added or already a member.', 'error');
                }
            }
        }

        $this->view->form = $form;

        $clubTable = new Model_DbTable_Club();
        $this->view->club = $clubTable->find($clubId)->current();

        $this->view->members = $clubMemberTable->fetchMembers($clubId, $this->view->year);
    }

    public function removeAction()
    {
        $id = $this->getRequest()->getUserParam('id');

        if(is_numeric($id)) {
            $clubMemberTable = new Model_DbTable_ClubMember();
            $member = $clubMemberTable->find($id)->current();
            $url = 'club/' . $member->club_id . '/' . $member->year . '/edit';

            if(!$this->view->isClubCaptain($member->club_id)) {
                $this->view->message('You must have access to remove from the roster', 'error');
                $this->_redirect('club/' . $member->club_id . '#' . $member->year);
            }

            $member->delete();
            $this->view->message('Member removed.', 'success');
            $this->_redirect($url);
        }

        $this->_redirect('clubs');
    }
}