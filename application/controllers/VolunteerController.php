<?php

class VolunteerController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page.css');
    }

    public function indexAction()
    {
    }

    public function registerAction()
    {
        $form = new Form_Volunteer($this->view->user);

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('volunteer');
            }

            foreach($post['primary_interest'] as $interest) {
                if($interest == 'Other') {
                    $form->getElement('other')
                         ->setRequired()
                         ->addErrorMessage('Please list the other activities you are interested in.');
                    break;
                }
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
                $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
                unset($data['first_name']);
                unset($data['last_name']);

                $volunteerPoolTable = new Model_DbTable_VolunteerPool();
                $volunteerPoolTable->addVolunteer($data);

                $this->view->message('You have successfully signed up to be a volunteer, you might be contacted for upcoming opportunities.');
                $this->_redirect('volunteer/list');
            }
        }

        $this->view->form = $form;
    }

    public function listAction()
    {
        $volunteerTable = new Model_DbTable_Volunteer();
        $this->view->volunteers = $volunteerTable->fetchUpcomingVolunteers();
    }

    public function listaddAction()
    {
        $form = new Form_VolunteerOpportunity();

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('volunteer/list');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();
            }
        }

        $this->view->form = $form;
    }

    public function signupAction()
    {
        $volunteerId = $this->getRequest()->getUserParam('volunteer');

    }
}
