<?php

class Form_FormEdit extends Zend_Form
{
    private $_formId;

    public function __construct($formId)
    {
        $this->_formId = $formId;
        parent::__construct();
    }

    public function init()
    {
        $this->addElement('text', 'year', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => '2010', 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'label' => 'Year:',
            'description' => 'Enter the year the form is for or the year uploaded.',
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'Enter the name of the form, should be the same each year.',
        ));

        $this->addElement('file', 'file', array(
            'required' => false,
            'label' => 'Form:',
            'description' => 'Select a form to re-upload or blank to leave the form unchanged',
            'valueDisabled' => true,
        ));

        $this->addElement('submit', 'upload', array(
            'label' => 'Upload',
            'class' => 'button',
        ));

        $this->addElement('submit', 'cancel', array(
            'label' => 'Cancel',
            'class' => 'button',
        ));

        if(is_numeric($this->_formId)) {
            $this->addElement('hidden', 'form_id', array(
                'value' => $this->_formId,
            ));

            $formTable = new Model_DbTable_Form();
            $form = $formTable->find($this->_formId)->current();
            $this->getElement('year')->setValue($form->year);
            $this->getElement('name')->setValue($form->name);
        }


    }
}
