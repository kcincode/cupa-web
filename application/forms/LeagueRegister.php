<?php

class Cupa_Form_LeagueRegister extends Zend_Form
{
    private $_session;
    private $_state;
    private $_userId;
    private $_leagueId;

    public function __construct($leagueId, $userId, $state)
    {
        $this->_session = new Zend_Session_Namespace('registration' . $leagueId);
        $this->_state = $state;
        $this->_userId = $userId;
        $this->_leagueId = $leagueId;
        parent::__construct();
    }
    
    public function init()
    {
        $section = $this->_state;
        $this->$section();
    }

    private function user()
    {
        $usersTable = new Cupa_Model_DbTable_User();
        $minors = $usersTable->fetchAllMinors($this->_userId);
        $users[$this->_userId] = 'Myself';
        foreach($minors as $id => $minor) {
            $users[$id] = $minor;
        }

        $user = $this->addElement('radio', 'user', array(
            'required' => true,
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'label' => 'Register As:',
            'description' => 'Select who you would like to register as since you have minors.',
            'value' => $this->_userId,
            'multiOptions' => $users,
        ));
    }

    private function personal()
    {
        $userTable = new Cupa_Model_DbTable_User();
        $userProfileTable = new Cupa_Model_DbTable_UserProfile();

        $user = $userTable->find($this->_session->registrantId)->current();
        $userProfile = $userProfileTable->find($this->_session->registrantId)->current();
        if(!empty($user->parent)) {
            $parent = $userTable->find($user->parent)->current();
            $parentProfile = $userProfileTable->find($user->parent)->current();

            $user->email = $parent->email;
            $userProfile->phone = $parentProfile->phone;
        }

        $first_name = $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First name:',
            'value' => $user->first_name,
            'description' => 'Enter/Check your first name.',
        ));
        
        $last_name = $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last name:',
            'value' => $user->last_name,
            'description' => 'Enter/Check your last name.',
        ));

        $email = $this->addElement('text', 'email', array(
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

        $phone = $this->addElement('text', 'phone', array(
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

        $gender = $this->addElement('radio', 'gender', array(
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

        $birthday = $this->addElement('text', 'birthday', array(
            'required' => true,
            'validators' => array(
                array('Date'),
            ),
            'label' => 'Birthday:',
            'description' => 'Enter/Check your birthday',
            'class' => 'datepicker',
            'errorMessage' => 'Invalid date.',
            'value' => $userProfile->birthday,
        ));
 
        $nickname = $this->addElement('text', 'nickname', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Nickname:',
            'value' => $userProfile->nickname,
            'description' => 'Enter your nickname (optional).',
        ));

        $height = $this->addElement('text', 'height', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('Between', false, array('min' => '40', 'max' => '96', 'messages' => array('notBetween' => 'Height is too tall or short.')))
            ),
            'label' => 'Height:',
            'value' => $userProfile->height,
            'description' => 'Enter/Check your height in INCHES.',
        ));

        $userLevelTable = new Cupa_Model_DbTable_UserLevel();
        $allLevels = $userLevelTable->fetchAllByWeight();
        $levels = array();
        foreach($allLevels as $level) {
            $levels[$level->id] = $level->name;
        }

        $level = $this->addElement('select', 'level', array(
            'validators' => array(
                array('InArray', false, array(array_keys($levels))),
            ),
            'required' => true,
            'label' => 'Level of Experience:',
            'value' => $userProfile->level,
            'multiOptions' => $levels,
            'description' => 'Select/Check the level of experience you have in ultimate.',
        ));

        $experience = $this->addElement('text', 'experience', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => 1940, 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'label' => 'Ultimate Experience:',
            'value' => $userProfile->experience,
            'description' => 'Enter the YEAR you started playing ultimate.',
        ));        

    }

    private function league()
    {
        $leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
        $i = 1;
        foreach($leagueQuestionTable->fetchAllQuestionsFromLeague($this->_leagueId) as $question) {
            switch($question['type']) {
                case 'boolean':
                    $selection = array('1' => 'Yes', '0' => 'No');
                                        
                    $element = $this->addElement('radio', $question['name'], array(
                        'validators' => array(
                            array('InArray', false, array(array_keys($selection))),
                        ),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
                        'multiOptions' => $selection,
                    ));
                    break;
                case 'text':
                    $element = $this->addElement('text', $question['name'], array(
                        'filters' => array('StringTrim'),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
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
                    ));
                    break;
                case 'textarea':
                    $element = $this->addElement('textarea', $question['name'], array(
                        'filters' => array('StringTrim'),
                        'required' => ($question['required'] == 1) ? true : false,
                        'label' => $i . '.) ' . $question['title'],
                    ));
                    break;
            }
            $i++;
        }

    }

}