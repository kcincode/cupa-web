<?php

class Form_UserForgotPassword extends Twitter_Bootstrap_Form_Horizontal
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Enter Email Address:',
        ));

        $this->addElement('button', 'reset', array(
            'type' => 'submit',
            'label' => 'Reset Password',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addDisplayGroup(
            array('email'),
            'reset_edit_form',
            array(
                'legend' => 'Reset your password',
            )
        );

        $this->addDisplayGroup(
            array('reset'),
            'reset_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
