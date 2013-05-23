<?php

class Form_LeagueRegister extends Twitter_Bootstrap_Form_Vertical
{
    private $_session;
    private $_state;
    private $_userId;
    private $_leagueId;
    protected $_user;

    public function __construct($leagueId, $userId, $state)
    {
        $this->_session = new Zend_Session_Namespace('registration' . $leagueId);
        $this->_state = $state;
        $this->_userId = $userId;
        $usersTable = new Model_DbTable_User();
        $this->_user = $usersTable->find($userId)->current();
        $this->_leagueId = $leagueId;

        parent::__construct();
    }

    public function init()
    {
        $section = $this->_state;
        $this->$section();

        if(!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'profile/leagues') === false) {
            $this->addElement('button', 'next', array(
                'type' => 'submit',
                'label' => 'Next',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'arrow-right',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_RIGHT,
            ));

            $this->addElement('button', 'back', array(
                'type' => 'submit',
                'label' => 'Back',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'arrow-left',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            ));

            $this->addElement('button', 'cancel', array(
                'type' => 'submit',
                'label' => 'Cancel',
            ));

            if($section == 'user') {
                $this->removeElement('back');
                $actions = array('cancel', 'next');
            } else if($section == 'done') {
                $this->removeElement('next');
                $this->removeElement('cancel');

                $this->addElement('button', 'finish', array(
                    'type' => 'submit',
                    'label' => ($this->_session->waitlist) ? 'Waitlist' : 'Register',
                    'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS,
                    'escape' => false,
                    'icon' => 'hdd',
                    'whiteIcon' => true,
                    'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
                ));
                $actions = array('back', 'finish');
            } else {
                $this->removeElement('cancel');
                $actions = array('back', 'next');
            }

            $this->addDisplayGroup(
                $actions,
                'league_actions',
                array(
                    'disableLoadDefaultDecorators' => true,
                    'decorators' => array('Actions'),
                )
            );
        } else {
            $this->addElement('button', 'save', array(
                'type' => 'submit',
                'label' => 'Save',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'hdd',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            ));

            $this->addElement('submit', 'cancel', array(
                'type' => 'submit',
                'label' => 'Cancel',
                'escape' => false,
            ));

            $this->addDisplayGroup(
                array('save', 'cancel'),
                'league_actions',
                array(
                    'disableLoadDefaultDecorators' => true,
                    'decorators' => array('Actions'),
                )
            );
        }
    }

    private function user()
    {
        $usersTable = new Model_DbTable_User();
        $minors = $usersTable->fetchAllMinors($this->_userId);
        $users[$this->_userId] = 'Myself';
        foreach($minors as $id => $minor) {
            $users[$id] = $minor;
        }

        $this->addElement('radio', 'user', array(
            'required' => true,
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'label' => 'Register As:',
            'description' => 'Select who you would like to register as since you have minors.',
            'value' => $this->_userId,
            'multiOptions' => $users,
        ));

        $this->addDisplayGroup(
            array('user'),
            'register_user_edit_form',
            array(
                'legend' => 'Register As',
            )
        );
    }

    private function personal()
    {
        $userTable = new Model_DbTable_User();
        $userProfileTable = new Model_DbTable_UserProfile();

        $user = $userTable->find($this->_session->registrantId)->current();
        $userProfile = $userProfileTable->find($this->_session->registrantId)->current();
        if(!empty($user->parent)) {
            $parent = $userTable->find($user->parent)->current();
            $parentProfile = $userProfileTable->find($user->parent)->current();

            $user->email = $parent->email;
            $userProfile->phone = $parentProfile->phone;
        }

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First name:',
            'value' => $user->first_name,
            'description' => 'Enter/Check your first name.',
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last name:',
            'value' => $user->last_name,
            'description' => 'Enter/Check your last name.',
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
            'value' => $user->email,
            'errorMessage' => 'Invalid email address.',
            'description' => 'Enter/Check your email address.',
        ));

        if(!empty($user->parent)) {
            $this->getElement('email')->disabled = 'disabled';
            $this->getElement('email')->setRequired(false);
        }

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/')),
            ),
            'label' => 'Phone:',
            'value' => $userProfile->phone,
            'errorMessage' => 'Invalid phone number.',
            'description' => 'Enter/Check your phone number.',
        ));

        if(!empty($user->parent)) {
            $this->getElement('phone')->disabled = 'disabled';
            $this->getElement('phone')->setRequired(false);
        }

        $genders = array(
            'Male' => 'Male',
            'Female' => 'Female',
        );

        $this->addElement('radio', 'gender', array(
            'required' => true,
            'validators' => array(
                array('InArray', false, array(array_keys($genders))),
            ),
            'label' => 'Gender:',
            'description' => 'Select your gender.',
            'value' => $userProfile->gender,
            'multiOptions' => $genders,
            'description' => 'Select/Check your gender.',
        ));

        $this->addElement('text', 'birthday', array(
            'required' => true,
            'validators' => array(
                array('Date', false, array('format' => 'MM/dd/YYYY')),
            ),
            'label' => 'Birthday:',
            'description' => 'Enter/Check your birthday',
            'class' => 'datepicker',
            'errorMessage' => 'Invalid date.',
            'value' => (strtotime($userProfile->birthday) == 0) ? null : date('m/d/Y', strtotime($userProfile->birthday)),
        ));

        $this->addElement('text', 'nickname', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Nickname:',
            'value' => $userProfile->nickname,
            'description' => 'Enter your nickname (optional).',
        ));

        $this->addElement('text', 'height', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('Between', false, array('min' => '40', 'max' => '96', 'messages' => array('notBetween' => 'Height is too tall or short.')))
            ),
            'label' => 'Height:',
            'value' => $userProfile->height,
            'description' => 'Enter/Check your height in INCHES.',
        ));

        $userLevelTable = new Model_DbTable_UserLevel();
        $allLevels = $userLevelTable->fetchAllByWeight();
        $levels = array();
        foreach($allLevels as $level) {
            $levels[$level->id] = $level->name;
        }

        $this->addElement('select', 'level', array(
            'validators' => array(
                array('InArray', false, array(array_keys($levels))),
            ),
            'required' => true,
            'label' => 'Level of Experience:',
            'value' => $userProfile->level,
            'multiOptions' => $levels,
            'description' => 'Select/Check the level of experience you have in ultimate.',
        ));

        $this->addElement('text', 'experience', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => 1940, 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'label' => 'Ultimate Experience:',
            'value' => $userProfile->experience,
            'description' => 'Enter the YEAR you started playing ultimate.',
        ));

        $questions = array();
        $userEmergencyTable = new Model_DbTable_UserEmergency();
        $contacts = $userEmergencyTable->fetchAllContacts($userProfile->user_id);
        foreach(range(1,2) as $i) {
            $this->addElement('text', 'contactName' . $i, array(
                'filters' => array('StringTrim'),
                'required' => true,
                'label' => 'Name',
                'value' => (empty($contacts[$i - 1])) ? null : $contacts[$i - 1]->first_name . ' ' . $contacts[$i - 1]->last_name,
                'description' => 'Name of contact',
            ));

            $this->addElement('text', 'contactPhone' . $i, array(
                'filters' => array('StringTrim'),
                'required' => true,
                'validators' => array(
                    array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/')),
                ),
                'label' => 'Phone:',
                'value' => (empty($contacts[$i - 1])) ? null : $contacts[$i - 1]->phone,
                'errorMessage' => 'Invalid phone number.',
                'description' => 'Phone # of contact.',
            ));

            $questions[] = 'contactName' . $i;
            $questions[] = 'contactPhone' . $i;
        }

        $this->addDisplayGroup(
            array('first_name', 'last_name', 'email', 'phone', 'gender', 'birthday', 'nickname', 'height', 'experience', 'level'),
            'register_personal_edit_form',
            array(
                'legend' => 'Personal Information',
            )
        );

        $this->addDisplayGroup(
            $questions,
            'register_contact_edit_form',
            array(
                'legend' => 'Emergency Contacts',
            )
        );

    }

    private function league()
    {
        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        //$leagueInformationTable = new Model_DbTable_LeagueInformation();
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $leagueLimitTable = new Model_DbTable_LeagueLimit();
        $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $leagueMemberTable = new Model_DbTable_LeagueMember();

        if(!empty($this->_userId)) {
            $leagueMember = $leagueMemberTable->fetchMember($this->_leagueId, $this->_userId, null, 'player');
            if($leagueMember) {
                $answers = $leagueAnswerTable->fetchAllAnswers($leagueMember->id);
            }
        }

        $i = 1;
        $questionList = array();
        foreach($leagueQuestionTable->fetchAllQuestionsFromLeague($this->_leagueId) as $question) {
            if($question['name'] == 'user_teams') {
                //$leagueInformation = $leagueInformationTable->fetchInformation($this->_leagueId);

                $limits = $leagueLimitTable->fetchLimits($this->_leagueId);
                $teams = $leagueTeamTable->fetchAllTeams($this->_leagueId);
                $currentTeams = array('0' => 'Select a Team');
                $validTeams = array();
                foreach($teams as $team) {
                    $currentTeams[$team->id] = $team->name;
                    $validTeams[$team->id] = $team->name;
                }

                $this->addElement('radio', 'team_select', array(
                    'required' => true,
                    'label' => '1.) Do you want to create a team or join a team?',
                    'multiOptions' => array('0' => 'Create a Team', '1' => 'Join a Team'),
                ));

                $this->addElement('select', 'user_team_select', array(
                    'required' => true,
                    'validators' => array(
                        array('InArray', false, array(array_keys($validTeams), 'messages' => array('notInArray' => 'Please select a valid team.'))),
                    ),
                    'label' => 'a.) Select the team you would like to join:',
                    'description' => 'Select the team you would like to join, the captain will make a descision to allow you to join.',
                    'multiOptions' => $currentTeams,
                ));

                $this->addElement('text', 'user_team_new', array(
                    'required' => false,
                    'filters' => array('StringTrim'),
                    'label' => 'a.) Enter the team name you would like to create:',
                    'description' => 'Enter the team name you would like to create.  First come first serve.',
                ));

                if(count($teams) >= $limits['teams']) {
                    $this->removeElement('team_select');
                    $this->removeElement('user_team_new');
                    $this->getElement('user_team_select')->setLabel('1.) Select the team you would like to join:');
                    $questionList[] = 'user_team_select';
                } else if(count($teams) == 0) {
                    $this->removeElement('team_select');
                    $this->removeElement('user_team_select');
                    $this->getElement('user_team_new')->setLabel('1.) Enter the team name you would like to create:');
                    $questionList[] = 'user_team_new';
                } else {
                    $questionList[] = 'team_select';
                    $questionList[] = 'user_team_select';
                    $questionList[] = 'user_team_new';
                }
            } else {
                switch($question['type']) {
                    case 'boolean':
                        $selection = array('1' => 'Yes', '0' =>'No');

                        $element = $this->addElement('radio', $question['name'], array(
                            'validators' => array(
                                array('InArray', false, array(array_keys($selection))),
                            ),
                            'required' => ($question['required'] == 1) ? true : false,
                            'label' => $i . '.) ' . $question['title'],
                            'multiOptions' => $selection,
                            'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                        ));
                        break;
                    case 'text':
                        $element = $this->addElement('text', $question['name'], array(
                            'filters' => array('StringTrim'),
                            'required' => ($question['required'] == 1) ? true : false,
                            'label' => $i . '.) ' . $question['title'],
                            'description' => ($question['required'] == 0) ? '(optional)' : '',
                            'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                        ));
                        break;
                    case 'multiple':
                        $selection = Zend_Json::decode($question['answers']);

                        $element = $this->addElement('radio', $question['name'], array(
                            'validators' => array(
                                array('InArray', false, array(array_keys($selection))),
                            ),
                            'required' => ($question['required'] == 1) ? true : false,
                            'label' => $i . '.) ' . $question['title'],
                            'multiOptions' => $selection,
                            'description' => ($question['required'] == 0) ? '(optional)' : '',
                            'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                        ));
                        break;
                    case 'textarea':
                        $element = $this->addElement('textarea', $question['name'], array(
                            'filters' => array('StringTrim'),
                            'required' => ($question['required'] == 1) ? true : false,
                            'label' => $i . '.) ' . $question['title'],
                            'class' => 'span6',
                            'description' => ($question['required'] == 0) ? '(optional)' : '',
                            'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                        ));
                        break;
                }
                $questionList[] = $question['name'];
            }
            $i++;
        }

        $this->addDisplayGroup(
            $questionList,
            'league_register_form',
            array(
                'legend' => 'League Questions',
            )
        );

    }

    private function done()
    {
        $userTable = new Model_DbTable_User();
        $userProfileTable = new Model_DbTable_UserProfile();

        $user = $userTable->find($this->_session->registrantId)->current();
        $userProfile = $userProfileTable->find($this->_session->registrantId)->current();

        if(!empty($user->parent)) {
            $parent = $userTable->find($user->parent)->current();
            $parentProfile = $userProfileTable->find($user->parent)->current();

            $user->email = $parent->email;
            $userProfile->phone = $parentProfile->phone;
        }

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First name:',
            'value' => (empty($this->_session->personal['first_name'])) ? $user->first_name : $this->_session->personal['first_name'],
            'disabled' => true,
            'description' => 'Please check your first name.',
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last name:',
            'value' => (empty($this->_session->personal['last_name'])) ? $user->last_name : $this->_session->personal['last_name'],
            'disabled' => true,
            'description' => 'Please check your last name.',
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Email Address:',
            'value' => (empty($this->_session->personal['email'])) ? $user->email : $this->_session->personal['email'],
            'disabled' => true,
            'description' => 'Please check your email address.',
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Phone:',
            'value' => (empty($this->_session->personal['phone'])) ? $userProfile->phone : $this->_session->personal['phone'],
            'disabled' => true,
            'description' => 'Please check your phone number.',
        ));

        $this->addElement('text', 'birthday', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Birthday:',
            'value' => (empty($this->_session->personal['birthday'])) ? date('m/d/Y', strtotime($userProfile->birthday)) : date('m/d/Y', strtotime($this->_session->personal['birthday'])),
            'disabled' => true,
            'description' => 'Please check your birthday.',
        ));

        $this->addDisplayGroup(
            array('first_name', 'last_name', 'email', 'phone', 'birthday'),
            'league_register_confirm_form',
            array(
                'legend' => 'Confirm Registration',
            )
        );
    }

    private function leaguequestions()
    {
        $this->league();

        foreach($this->getElements() as $element) {
            $element->setAttrib('disabled', true);
            $element->setAttrib('class', 'leagues');
        }

    }

}
