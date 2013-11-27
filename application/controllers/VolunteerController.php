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
        $volunteerPoolTable = new Model_DbTable_VolunteerPool();

        if($this->view->user) {
            $volunteer = $volunteerPoolTable->fetchVolunteerFromId($this->view->user->id);
            if($volunteer) {
                $this->renderScript('volunteer/register_done.phtml');
                return;
            }
        }

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
        $request = $this->getRequest();
        $volunteerId = $request->getUserParam('volunteer_id');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/bootstrap-datetimepicker.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/bootstrap-datetimepicker.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/select2/select2.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/select2/select2.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/ckeditor/ckeditor.js');

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
        $request = $this->getRequest();
        $volunteerId = $request->getUserParam('volunteer_id');
        $volunteerTable = new Model_DbTable_Volunteer();
        $volunteer = $volunteerTable->find($volunteerId)->current();

        $form = new Form_Volunteer($this->view->user, 'signup', $volunteer);

        if($request->isPost()) {
            $post = $request->getPost();

            if(isset($post['cancel'])) {
                $this->_redirect('volunteer/list');
            }

            if($form->isValid($post)) {
                $data = $form->getValues();

                // fetch or create pool member
                $volunteerPoolTable = new Model_DbTable_VolunteerPool();
                $member = $volunteerPoolTable->fetchMember($data);

                // create the member of the volunteer opportunity
                $volunteerMemberTable = new Model_DbTable_VolunteerMember();
                $result = $volunteerMemberTable->addVolunteer($volunteerId, $member->id, $data);

                if(!$result) {
                    $this->view->message('You have already signed up for this opportunity', 'warning');
                } else {
                    $this->view->message('Signed up for opportunity, you will receive an email from the contact with more information.', 'success');
                }

                $this->_redirect('volunteer/list');
            }
        }

        $this->view->form = $form;
    }

    public function listmembersAction()
    {
        $request = $this->getRequest();
        $volunteerId = $request->getUserParam('volunteer_id');

        $volunteerTable = new Model_DbTable_Volunteer();
        $this->view->volunteer = $volunteerTable->find($volunteerId)->current();

        $volunteerMemberTable = new Model_DbTable_VolunteerMember();
        $this->view->members = $volunteerMemberTable->fetchVolunteers($volunteerId);

        if($request->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $title = str_replace(' ', '_', $this->view->volunteer->name);

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
            header('Content-Disposition: attachment; filename="' .  $title . '_members.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            $header = array('name', 'email', 'phone', 'involvement', 'primary_interest',' experience', 'comment');
            if(isset($this->view->members[0]['answers'])) {
                foreach(Zend_Json::decode($this->view->members[0]['answers']) as $key => $value) {
                    $header[] = $key;
                }
                echo "\"" . implode('","', $header) . "\"\n";

                foreach($this->view->members as $member) {
                    $name = (empty($member['vname'])) ? $member['first_name'] . ' ' . $member['last_name'] : $member['vname'];
                    $email = (empty($member['vemail'])) ? $member['email'] : $member['vemail'];
                    $phone = (empty($member['vphone'])) ? $member['phone'] : $member['vphone'];
                    echo "\"{$name}\",\"{$email}\",\"{$phone}\",\"{$member['involvement']}\",\"{$member['primary_interest']}\",\"{$member['experience']}\",\"" . addslashes($member['comment']) . "\"";
                    foreach(Zend_Json::decode($member['answers']) as $value) {
                        if(is_array($value)) {
                            echo ',"(' . implode(' | ', $value) . ')"';
                        } else {
                            echo ",\"{$value}\"";
                        }
                    }
                    echo "\n";
                }
            }
            exit();
        }
    }

    public function poolAction()
    {
        $page = $this->getRequest()->getUserParam('page');

        //$leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $volunteerPoolTable = new Model_DbTable_VolunteerPool();
        $volunteers = $volunteerPoolTable->fetchAllVolunteers();
        if($this->getRequest()->getParam('export')) {
            // disable the layout
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

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
            header('Content-Disposition: attachment; filename="CUPA-Volunteers.csv";');
            header('Content-Transfer-Encoding: binary');

            set_time_limit(0);

            echo "volunteer,email\n";
            foreach($volunteers as $volunteer) {
                $email = (empty($volunteer['email'])) ? $volunteer['parent_email'] : $volunteer['email'];
                echo "{$volunteer['volunteer_name']},{$email}\n";
            }
            exit();
        }

        $this->view->volunteers = Zend_Paginator::factory($volunteers);
        $this->view->volunteers->setCurrentPageNumber($page);
        $this->view->volunteers->setItemCountPerPage(25);
    }
}
