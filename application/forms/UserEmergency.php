<?php

class Form_UserEmergency extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_contact;

    public function __construct($contact = null)
    {
        $this->_contact = $contact;

        parent::__construct();
    }


    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'class' => 'span3',
            'value' => (empty($this->_contact['first_name'])) ? null : $this->_contact['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'class' => 'span3',
            'value' => (empty($this->_contact['last_name'])) ? null : $this->_contact['last_name'],
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
            'value' => (empty($this->_contact['phone'])) ? null : $this->_contact['phone'],
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_contact)) ? 'Create' : 'Save',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('button', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
        ));

        $title = (empty($this->_contact)) ? 'Create Emergency Contact' : 'Edit Emergency Contact';
        $this->addDisplayGroup(
            array('first_name' , 'last_name', 'phone'),
            'emergency_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'emergency_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
