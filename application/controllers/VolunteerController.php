<?php

class VolunteerController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/volunteer/index.css');
    }

    public function registerAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/volunteer/register.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/volunteer/register.js');
        $form = new Form_Volunteer($this->view->user);

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if($form->isValid($post)) {
                $data = $form->getValues();
                $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
                unset($data['first_name']);
                unset($data['last_name']);

                $volunteerPoolTable = new Model_DbTable_VolunteerPool();
                $volunteerPoolTable->addVolunteer($data);

                $this->view->message('You have successfully signed up to be a volunteer, you will be contacted for upcomming opportunities.');
                $this->_redirect('volunteer/list');
            }
        }

        $this->view->form = $form;
    }

    public function listAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/volunteer/list.css');
        $volunteerTable = new Model_DbTable_Volunteer();
        $this->view->volunteers = $volunteerTable->fetchUpcomingVolunteers();
    }
}
