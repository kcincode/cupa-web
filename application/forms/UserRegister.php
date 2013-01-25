<?php

class Form_UserRegister extends Twitter_Bootstrap_Form_Horizontal
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First name:',
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last name:',
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(
                'EmailAddress',
                array('Db_NoRecordExists', false, array('table' => 'user', 'field' => 'email', 'messages' => array('recordFound' => 'Email is already in use.')))
            ),
            'required' => true,
            'label' => 'Email Address:',
        ));

        $this->addElement('button', 'create', array(
            'type' => 'submit',
            'label' => 'Register',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addDisplayGroup(
            array('first_name', 'last_name', 'email'),
            'register_edit_form',
            array(
                'legend' => 'Register for an Account',
            )
        );

        $this->addDisplayGroup(
            array('create'),
            'register_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
