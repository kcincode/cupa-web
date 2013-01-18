<?php

class Form_MinuteEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_minute;

    public function __construct($minute = null)
    {
        $this->_minute = $minute;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'when', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Date:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'value' => (empty($this->_minute->when)) ? null : date('m/d/Y H:i', strtotime($this->_minute->when)),
        ));

        $this->addElement('text', 'location', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
            'class' => 'span4',
            'value' => (empty($this->_minute->location)) ? null : $this->_minute->location,
        ));

        $this->addElement('file', 'pdf', array(
            'required' => false,
            'label' => 'PDF File:',
        ));

        $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
            'value' => (empty($this->_minute->is_visible)) ? null : $this->_minute->is_visible,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_minute)) ? 'Create' : 'Save',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'list',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('submit', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
            'escape' => false,
        ));


        $title = (empty($this->_minute)) ? 'Add Meeting Minutes' : 'Edit Meeting Minutes';
        $this->addDisplayGroup(
            array('when', 'location', 'pdf', 'is_visible'),
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

