<?php

class Form_Profile extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_data;
    protected $_state;
    protected $_leagueId;
    protected $_userId;
    
    protected $_questions = array();

    public function __construct($user, $state, $leagueId = null)
    {
        $userTable = new Model_DbTable_User();
        $this->_data = $userTable->fetchProfile($user);
        $this->_state = $state;
        $this->_leagueId = $leagueId;
        $this->_userId = $user->id;

        parent::__construct();
    }

    public function init()
    {
        $state = $this->_state;
        if($state && method_exists($this, $state)) {
            $this->$state();
        }
        
        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Update',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));
        
        $this->addDisplayGroup(
            array('save', 'cancel'),
            'profile_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }

    private function personal()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'username', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                 array('StringLength', true, array('min' => 4, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last username, max of 25 characters.'))),
            ),
            'required' => false,
            'disabled' => true,
            'class' => 'span2',
            'description' => 'This is being phased out, using email instead',
            'label' => 'Username:',
            'value' => (empty($this->_data['username'])) ? null : $this->_data['username'],
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
                array('Db_NoRecordExists', false, array('table' => 'user', 'field' => 'email', 'exclude' => array('field' => 'id', 'value' => $this->_data['id']), 'messages' => array('recordFound' => 'Email address is already used.'))),

            ),
            'class' => 'span5',
            'required' => true,
            'label' => 'Email Address:',
            'value' => (empty($this->_data['email'])) ? null : $this->_data['email'],
        ));

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'class' => 'span3',
            'value' => (empty($this->_data['first_name'])) ? null : $this->_data['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'class' => 'span3',
            'value' => (empty($this->_data['last_name'])) ? null : $this->_data['last_name'],
        ));

        $this->addElement('text', 'nickname', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid nickname, max of 25 characters.'))),
            ),
            'required' => false,
            'label' => 'Nickname:',
            'class' => 'span3',
            'value' => (empty($this->_data['profile']['nickname'])) ? null : $this->_data['profile']['nickname'],
            'description' => '(optional)',
        ));

        $genders = array('Male' => 'Male', 'Female' => 'Female');
        $this->addElement('radio', 'gender', array(
            'validators' => array(
                array('InArray', false, array(array_keys($genders))),
            ),
            'required' => true,
            'multiOptions' => $genders,
            'label' => 'Gender:',
            'value' => (empty($this->_data['profile']['gender'])) ? null : $this->_data['profile']['gender'],
        ));

        $this->addElement('text', 'birthday', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Birthday:',
            'class' => 'datepicker span2',
            'style' => 'text-align: center',
            'value' => (empty($this->_data['profile']['age'])) ? null : date('m/d/Y', strtotime($this->_data['profile']['age'])),
        ));

        $this->addElement('text', 'height', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('Between', false, array('min' => '40', 'max' => '96', 'messages' => array('notBetween' => 'Height is too tall or short.')))
            ),
            'label' => 'Height:',
            'class' => 'span1',
            'style' => 'text-align: center',
            'value' => (empty($this->_data['profile']['height'])) ? null : $this->_data['profile']['height'],
            'description' => 'Enter/Check your height in INCHES.',
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/', 'messages' => array('regexNotMatch' => 'Invalid phone number ###-###-####'))),
            ),
            'label' => 'Phone:',
            'class' => 'span2',
            'style' => 'text-align: center',
            'value' => (empty($this->_data['profile']['phone'])) ? null : $this->_data['profile']['phone'],
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
            'value' => (empty($this->_data['profile']['level_id'])) ? null : $this->_data['profile']['level_id'],
            'multiOptions' => $levels,
            'description' => 'Select the level of experience you have in ultimate.',
        ));

        $years = array_combine(range(date('Y'), date('Y') - 60), range(date('Y'), date('Y') - 60));
        $this->addElement('select', 'experience', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('InArray', false, array(array_keys($years))),
            ),
            'label' => 'Ultimate Experience:',
            'multiOptions' => $years,
            'class' => 'span2',
            'value' => (empty($this->_data['profile']['experience'])) ? null : $this->_data['profile']['experience'],
            'description' => 'Enter the YEAR you started playing ultimate.',
        ));
    }

    private function minors_edit()
    {
        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'value' => (empty($this->_data['first_name'])) ? null : $this->_data['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'value' => (empty($this->_data['last_name'])) ? null : $this->_data['last_name'],
        ));

        $this->addElement('text', 'nickname', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid nickname, max of 25 characters.'))),
            ),
            'required' => false,
            'label' => 'Nickname:',
            'value' => (empty($this->_data['profile']['nickname'])) ? null : $this->_data['profile']['nickname'],
            'description' => '(optional)',
        ));

        $genders = array('Male' => 'Male', 'Female' => 'Female');
        $this->addElement('radio', 'gender', array(
            'validators' => array(
                array('InArray', false, array(array_keys($genders))),
            ),
            'required' => true,
            'multiOptions' => $genders,
            'separator' => '&nbsp; &nbsp;',
            'label' => 'Gender:',
            'value' => (empty($this->_data['profile']['gender'])) ? null : $this->_data['profile']['gender'],
        ));

        $this->addElement('text', 'birthday', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Birthday:',
            'class' => 'datepicker',
            'value' => (empty($this->_data['profile']['age'])) ? null : $this->_data['profile']['age'],
        ));

        $this->addElement('text', 'height', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('Between', false, array('min' => '40', 'max' => '96', 'messages' => array('notBetween' => 'Height is too tall or short.')))
            ),
            'label' => 'Height:',
            'value' => (empty($this->_data['profile']['height'])) ? null : $this->_data['profile']['height'],
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
            'value' => (empty($this->_data['profile']['level'])) ? null : $this->_data['profile']['level'],
            'multiOptions' => $levels,
            'description' => 'Select the level of experience you have in ultimate.',
        ));

        $this->addElement('text', 'experience', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => 1940, 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'label' => 'Ultimate Experience:',
            'value' => (empty($this->_data['profile']['experience'])) ? null : $this->_data['profile']['experience'],
            'description' => 'Enter the YEAR you started playing ultimate.',
        ));

    }

    private function league_edit()
    {
        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $leagueAnswerTable = new Model_DbTable_LeagueAnswer();
        $leagueMemberTable = new Model_DbTable_LeagueMember();

        $i = 1;
        foreach($leagueQuestionTable->fetchAllQuestionsFromLeague($this->_leagueId) as $question) {
            $leagueMember = $leagueMemberTable->fetchMember($this->_leagueId, $this->_userId);
            $answers = $leagueAnswerTable->fetchAllAnswers($leagueMember->id);

            switch($question['type']) {
                case 'boolean':
                    $selection = array('1' => 'Yes', '0' =>'No');

                    $this->addElement('radio', $question['name'], array(
                        'validators' => array(
                            array('InArray', false, array(array_keys($selection))),
                        ),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
                        'multiOptions' => $selection,
                        'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : 0,
                    ));
                    break;
                case 'text':
                    $this->addElement('text', $question['name'], array(
                        'filters' => array('StringTrim'),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
                        'description' => ($question['required'] == 0) ? '(optional)' : '',
                        'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                    ));
                    break;
                case 'multiple':
                    $selection = Zend_Json::decode($question['answers']);

                    $this->addElement('radio', $question['name'], array(
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
                    $this->addElement('textarea', $question['name'], array(
                        'filters' => array('StringTrim'),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
                        'description' => ($question['required'] == 0) ? '(optional)' : '',
                        'value' => (isset($answers[$question['name']])) ? $answers[$question['name']] : null,
                    ));
                    break;
            }

            $i++;
        }
    }

    private function password()
    {
        $this->addElement('password', 'current', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Enter current password:',
        ));

        $this->addElement('password', 'password', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Enter a new password:',
        ));

        $this->addElement('password', 'confirm', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Confirm new password:',
        ));
    }

}
