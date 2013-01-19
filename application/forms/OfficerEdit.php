<?php

class Form_OfficerEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_officer;

    public function __construct($officer = null)
    {
        $this->_officer = $officer;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $userTable = new Model_DbTable_User();
        $users = array();
        $users[0] = 'Select a User';
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $officersTable = new Model_DbTable_Officer();

        $this->addElement('select', 'user_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'User:',
            'class' => 'span3',
            'multiOptions' => $users,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0, 'messages' => array('notGreaterThan' => 'Please select a user'))),
            ),
            'description' => 'Select the user for the position.',
            'value' => (empty($this->_officer->user_id)) ? null : $this->_officer->user_id,
        ));

        $this->addElement('file', 'image', array(
            'label' => 'Picture',
            'required' => false,
            'validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'jpg,png,gif'),
            ),
            'valueDisabled' => true,
            'description' => '(Not Required)',
        ));

        $this->addElement('text', 'position', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Position:',
            'class' => 'span3',
            'description' => 'Enter the date the position name.',
            'value' => (empty($this->_officer->position)) ? null : $this->_officer->position,
        ));

        $this->addElement('text', 'since', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Since:',
            'class' => 'span2 datepicker',
            'data-date-format' => 'mm/dd/yyyy',
            'style' => 'text-align: center;',
            'description' => 'Enter the date the position was active.',
            'value' => (!empty($this->_officer->since)) ? date('m/d/Y', strtotime($this->_officer->since)) : date('Y-m-d'),
        ));

        $this->addElement('text', 'to', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'class' => 'span2 datepicker',
            'data-date-format' => 'mm/dd/yyyy',
            'style' => 'text-align: center;',
            'label' => 'To:',
            'append' => '<i onclick="$(\'#to\').val(\'\');" class="icon-remove"></i>',
            'description' => 'Enter the date the position was revoked.',
            'value' => (!empty($this->_officer->to)) ? date('m/d/Y', strtotime($this->_officer->to)) : null,
        ));

        $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Lower numbers are shown first.',
            'value' => (!empty($this->_officer->weight)) ? $this->_officer->weight : $officersTable->getNextWeight(),
        ));

        $this->addElement('textarea', 'description', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Position Description',
            'class' => 'ckeditor',
            'description' => 'Enter what this position is repsponsible for.',
            'value' => (empty($this->_officer->description)) ? null : $this->_officer->description,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_officer)) ? 'Create' : 'Save',
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

        $title = (empty($this->_officer)) ? 'Add an Officer' : 'Edit Officer';

        $this->addDisplayGroup(
            array('user_id', 'image', 'position', 'since', 'to', 'weight', 'description'),
            'officer_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'officer_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }

}

