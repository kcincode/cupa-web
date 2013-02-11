<?php

class Form_LeagueWaiver extends Twitter_Bootstrap_Form_Vertical
{
    protected $_user;

    public function __construct($user = null)
    {
        $this->_user = $user;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'name', array(
            'filters' => array('StringToLower'),
            'required' => true,
            'validators' => array(
                array('Identical', false, array('token' => strtolower($this->_user->first_name . ' ' . $this->_user->last_name), 'messages' => array('notSame' => 'Please enter name as it appears in the waiver.'))),
            ),
            'label' => 'Enter name as it appears above:',
        ));

        $this->addElement('checkbox', 'agree', array(
            'label' => 'I have read and agree with this waiver',
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0, 'messages' => array('notGreaterThan' => 'Please read and agree to the waiver.'))),
            ),
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Sign Waiver',
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
            array('name', 'agree'),
            'pickup_edit_form',
            array(
                'legend' => 'Sign Waiver',
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'pickup_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
