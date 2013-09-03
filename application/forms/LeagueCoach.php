<?php

class Form_LeagueCoach extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_coach;
    protected $_checks = array(
        'background_check' => 'Background Check',
        'bsa_safety' => 'BSA Safety',
        'concussion' => 'Concussion Training',
        'chaperon' => 'Chaperon Form',
        'manual' => 'Read Coaching Manual',
        'rules' => 'Read Rules',
        'usau' => 'USAU requirements',
    );

    public function __construct($coach)
    {
        $this->_coach = $coach;
        parent::__construct();
    }

    public function init()
    {
        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First Name:',
            'value' => $this->_coach['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last Name:',
            'value' => $this->_coach['last_name'],
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress'),
            ),
            'required' => true,
            'label' => 'Email:',
            'value' => $this->_coach['email'],
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('RegEx', false, array('pattern' => '\d\d\d-\d\d\d-\d\d\d\d')),
            ),
            'required' => true,
            'label' => 'Phone:',
            'value' => $this->_coach['phone'],
        ));

        foreach($this->_checks as $type => $label) {
            $this->addElement('radio', $type, array(
                'label' => $label,
                'multiOptions' => array(0 => 'Incomplete', 1 => 'Complete'),
                'value' => $this->_coach[$type],
            ));
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

        $this->addElement('button', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
        ));

        $this->addDisplayGroup(
            array('first_name' ,'last_name', 'email', 'phone'),
            'coach_edit_form',
            array(
                'legend' => 'Youth Coach Information',
            )
        );

        $this->addDisplayGroup(
            array_keys($this->_checks),
            'coach_edit_require',
            array(
                'legend' => 'Coaching Requirements',
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'coach_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
