<?php

class Form_Profile extends Zend_Form
{
    private $_data;
    private $_state;

    public function __construct($user, $state)
    {
        $userTable = new Model_DbTable_User();
        $this->_data = $userTable->fetchProfile($user);
        //Zend_Debug::dump($this->_data);
        $this->_state = $state;

        parent::__construct();
    }

    public function init()
    {
        $state = $this->_state;
        if($state && method_exists($this, $state)) {
            $this->$state();
        }
    }

    private function personal()
    {

        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'username', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                 array('StringLength', true, array('min' => 4, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last username, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Username:',
            'value' => (empty($this->_data['username'])) ? null : $this->_data['username'],
        ));

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

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/', 'messages' => array('regexNotMatch' => 'Invalid phone number ###-###-####'))),
            ),
            'label' => 'Phone:',
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

}
