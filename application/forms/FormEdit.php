<?php

class Form_FormEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_form;

    public function __construct($form = null)
    {
        $this->_form = $form;

        parent::__construct();
    }

    public function init()
    {
        $years = array_combine(range(2010, date('Y') + 1), range(2010, date('Y') + 1));
        $this->addElement('select', 'year', array(
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => '2009', 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'multiOptions' => $years,
            'label' => 'Year:',
            'description' => 'Enter the year the form is for or the year uploaded.',
            'value' => (empty($this->_form->year)) ? null : $this->_form->year,
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'span4',
            'description' => 'Enter the name of the form, should be the same each year.',
            'value' => (empty($this->_form->name)) ? null : $this->_form->name,
        ));

        $this->addElement('file', 'file', array(
            'required' => false,
            'label' => 'Form:',
            'description' => 'Select a form to re-upload or blank to leave the form unchanged',
            'valueDisabled' => true,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_form)) ? 'Create' : 'Save',
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

        $title = (empty($this->_form)) ? 'Add a Form' : 'Edit Form';
        $this->addDisplayGroup(
            array('year', 'name', 'file'),
            'form_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'form_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
