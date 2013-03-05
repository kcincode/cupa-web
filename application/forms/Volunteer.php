<?php

class Form_Volunteer extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_user;
    protected $_profile;
    protected $_type;

    public function __construct($user, $type = 'volunteer')
    {
        $this->_user = $user;
        $this->_type = $type;

        if($user) {
            $userProfileTable = new Model_DbTable_UserProfile();
            $this->_profile = $userProfileTable->find($user->id)->current()->toArray();
        }

        parent::__construct();
    }

    public function init()
    {

        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
            'value' => (empty($this->_user['email'])) ? null : $this->_user['email'],
        ));

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'value' => (empty($this->_user['first_name'])) ? null : $this->_user['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'value' => (empty($this->_user['last_name'])) ? null : $this->_user['last_name'],
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/', 'messages' => array('regexNotMatch' => 'Invalid phone number ###-###-####'))),
            ),
            'label' => 'Phone:',
            'value' => (empty($this->_profile['phone'])) ? null : $this->_profile['phone'],
            'description' => 'Use format: ###-###-####',
        ));

        if($this->_type == 'volunteer') {
            $involvement = array('0-1 year' => '0-1 year', '2-4 years' => '2-4 years', '5-9 years' => '5-9 years', '10+ years' => '10+ years');
            $this->addElement('select', 'involvement', array(
                'validators' => array(
                    array('InArray', false, array($involvement)),
                ),
                'required' => true,
                'label' => 'Years Involved with CUPA:',
                'multiOptions' => $involvement,
                'description' => 'Select the amount of time you have been involved with CUPA.',
            ));

            $primary = array(
                'Youth Outreach Events' => 'Youth Outreach Events',
                'Running CUPA Leagues' => 'Running CUPA Leagues',
                'Running CUPA Sponsored Tournaments' => 'Running CUPA Sponsored Tournaments',
                'Helping with USA Ultimate Sponsord Tournaments' => 'Helping with USA Ultimate Sponsord Tournaments',
                'Helping with General Volunteer Tasks' => 'Helping with General Volunteer Tasks',
                'Large Event Help - Various Tasks' => 'Large Event Help - Various Tasks',
                'Off-Field Event Help' => 'Off-Field Event Help',
                'Public Relations Efforts' => 'Public Relations Efforts',
                'Other' => 'Other',
            );

            $this->addElement('multiCheckbox', 'primary_interest', array(
                'validators' => array(
                    array('InArray', false, array($primary)),
                ),
                'required' => true,
                'label' => 'Primary Interest for Volunteering:',
                'multiOptions' => $primary,
                'description' => 'Select the activities that you would be interested in.',
            ));

            $this->addElement('textarea', 'other', array(
                'filters' => array('StringTrim'),
                'required' => false,
                'label' => 'Other: Please Specify:',
                'description' => 'Specify what you are interested in with volunteering.',
            ));

            $this->addElement('textarea', 'experience', array(
                'filters' => array('StringTrim'),
                'required' => false,
                'label' => 'Please list all past CUPA volunteer experience:',
                'description' => 'List all your past volunteering experiences with CUPA.',
            ));

            $elements = array('email', 'first_name', 'last_name', 'phone', 'involvement', 'primary_interest', 'other', 'experience');
        } else {
            $this->addElement('textarea', 'comment', array(
                'filters' => array('StringTrim'),
                'required' => false,
                'label' => 'Enter any comments',
            ));

            $elements = array('email', 'first_name', 'last_name', 'phone', 'comment');
        }

        $this->addElement('button', 'register', array(
            'type' => 'submit',
            'label' => ($this->_type == 'volunteer') ? 'Register as a Volunteer' : 'Sign up for Opportunity',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'user',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('submit', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
            'escape' => false,
        ));

        $this->addDisplayGroup(
            $elements,
            'volunteer_edit_form',
            array(
                'legend' => ($this->_type == 'volunteer') ? 'Volunteer Registration' : 'Opportunity Sign up',
            )
        );

        $this->addDisplayGroup(
            array('register', 'cancel'),
            'volunteer_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );

    }
}
