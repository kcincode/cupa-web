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
        if(!$this->view->isVolunteerAdmin()) {
            $this->view->message('You do not have access to this page', 'error');
            $this->_redirect('volunteer/list');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $form = new Form_VolunteerOpportunity();

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('volunteer/list');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // convert the dates
                $data['start'] = date('Y-m-d H:i:s', strtotime($data['start']));
                $data['end'] = date('Y-m-d H:i:s', strtotime($data['end']));

                $volunteerTable = new Model_DbTable_Volunteer();
                $id = $volunteerTable->insert($data);
                if(is_numeric($id)) {
                    $this->view->message('Volunteer opportunity created', 'success');
                    $this->_redirect('volunteer/list');
                } else {
                    $this->view->message('Error creating opportunity', 'error');
                }
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function listeditAction()
    {
        if(!$this->view->isVolunteerAdmin()) {
            $this->view->message('You do not have access to this page', 'error');
            $this->_redirect('volunteer/list');
        }


        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

        $request = $this->getRequest();
        $volunteerId = $request->getUserParam('volunteer_id');
        $volunteerTable = new Model_DbTable_Volunteer();
        $volunteer = $volunteerTable->find($volunteerId)->current();
        $form = new Form_VolunteerOpportunity($volunteer);

        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('volunteer/list');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // convert the dates
                $data['start'] = date('Y-m-d H:i:s', strtotime($data['start']));
                $data['end'] = date('Y-m-d H:i:s', strtotime($data['end']));

                foreach($data as $key => $value) {
                    if(isset($volunteer->$key)) {
                        $volunteer->$key = $value;
                    }
                }
                $volunteer->save();

                $this->view->message('Volunteer opportunity updated.', 'success');
                $this->_redirect('volunteer/list');
            }
        }

        $this->view->headScript()->appendScript('$(".datetimepicker").datetimepicker({ autoclose: true, minuteStep: 30, format: \'mm/dd/yyyy hh:ii\' });');
        $this->view->headScript()->appendScript('$(".select2").select2();');
        $this->view->form = $form;
    }

    public function listremoveAction()
    {
        $request = $this->getRequest();
        $volunteerId = $request->getUserParam('volunteer_id');
        $volunteerTable = new Model_DbTable_Volunteer();
        $volunteer = $volunteerTable->find($volunteerId)->current();

        if($volunteer) {
            $volunteer->delete();
        }

        $this->view->message('Volunteer opportunity removed', 'success');
        $this->_redirect('volunteer/list');
    }

    public function signupAction()
    {
        $volunteerId = $this->getRequest()->getUserParam('volunteer');

    }
}
