<?php

class Form_UserActivation extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_type;

    public function __construct($type)
    {
        $this->_type = $type;

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
            'description' => 'Enter the email you used to request the account',
        ));

        $this->addElement('password', 'password', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Enter a Password:',
        ));

        $this->addElement('password', 'confirm', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Confirm Password:',
        ));

        $this->addElement('button', 'activate', array(
            'type' => 'submit',
            'label' => ($this->_type == 'activate') ? 'Activate' : 'Reset',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addDisplayGroup(
            array('email', 'password', 'confirm'),
            'activate_edit_form',
            array(
                'legend' => ($this->_type == 'activate') ? 'Activate your Account' : 'Reset your Password',
            )
        );

        $this->addDisplayGroup(
            array('activate'),
            'activate_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }


}

