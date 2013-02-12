<?php

class ClubController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
    }

    public function indexAction()
    {
        $clubId = $this->getRequest()->getUserParam('club_id');
        $this->view->year = $this->getRequest()->getUserParam('year');

        $clubTable = new Model_DbTable_Club();
        $this->view->club = $clubTable->find($clubId)->current();

        $clubMemberTable = new Model_DbTable_ClubMember();
        $this->view->years = $clubMemberTable->fetchAllMemberByYear($clubId);

        $session = new Zend_Session_Namespace('previous');
        $session->previousPage = str_replace($this->view->baseUrl() . '/', '', $_SERVER['REQUEST_URI']);
    }

    public function editAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');

        $clubId = $this->getRequest()->getUserParam('club_id');
        $this->view->year = $this->getRequest()->getUserParam('year');

        if(!$this->view->isClubCaptain($clubId)) {
            $this->view->message('You must have access to edit the roster', 'error');
            $this->_redirect('club/' . $clubId . '/' . $this->view->year);
        }

        $clubMemberTable = new Model_DbTable_ClubMember();
        $form = new Form_ClubMember($clubId, $this->view->year);
        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if($form->isValid($post)) {
                $data = $form->getValues();
                $result = $clubMemberTable->addMember($data['club_id'], $data['user_id'], $data['year']);
                if($result) {
                    $this->view->message('User added.', 'success');
                } else {
                    $this->view->message('User not added or already a member.', 'error');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".select2").select2();');
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
                $this->_redirect('club/' . $member->club_id . '/' . $member->year);
            }

            $member->delete();
            $this->view->message('Member removed.', 'success');
            $this->_redirect($url);
        }

        $this->_redirect('clubs');
    }
}
