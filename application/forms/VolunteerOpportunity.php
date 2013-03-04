<?php

class Form_VolunteerOpportunity extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_volunteer;

    public function __construct($volunteer = null)
    {
        $this->_volunteer = $volunteer;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'value' => (empty($this->_volunteer['name'])) ? null : $this->_volunteer['name'],
        ));

        $this->addElement('text', 'start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'value' => (empty($this->_volunteer['start'])) ? null : date('m/d/Y H:i', $this->_volunteer['start']),
        ));

        $this->addElement('text', 'end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'value' => (empty($this->_volunteer['end'])) ? null : date('m/d/Y H:i', $this->_volunteer['end']),
        ));

        $contacts = array();
        $contacts[0] = 'Primary Contact';
        $this->addElement('select', 'contact_id', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0, 'messages' => array('notGreaterThan' => 'Please select a primary contact.'))),
            ),
            'multiOptions' => $contacts,
            'label' => 'Primary Contact:',
            'value' => (empty($this->_volunteer['contact_id'])) ? null : $this->_volunteer['contact_id'],
        ));

        $this->addElement('text', 'max_volunteers', array(
            'filters' => array('Digits'),
            'required' => true,
            'label' => 'Max Volunteers:',
            'description' => 'Enter the max volunteers.',
        ));

        $this->addElement('textarea', 'information', array(
            'required' => true,
            'label' => 'Information:',
            'required' => true,
            'description' => 'Enter any information you would like to display to the user.',
        ));

        $this->addElement('text', 'location_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
            'description' => 'Enter the location name.',
        ));

        $this->addElement('text', 'address', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Street:',
            'description' => 'Enter the locations street address.',
        ));

        $this->addElement('text', 'city', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'City:',
            'description' => 'Enter the locations city.',
        ));

        $this->addElement('text', 'state', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'State',
            'description' => 'Enter the locations state.',
        ));

        $this->addElement('text', 'zip', array(
            'filters' => array('Digits'),
            'required' => true,
            'label' => 'Zip',
            'description' => 'Enter the locations zipcode.',
        ));

        $this->addElement('button', 'create', array(
            'type' => 'submit',
            'label' => 'Create',
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
            array('name', 'start', 'end', 'contact_id', 'max_volunteers', 'information', 'location_name', 'address', 'city', 'state', 'zip'),
            'volunteer_edit_form',
            array(
                'legend' => 'Volunteer Registration'
            )
        );

        $this->addDisplayGroup(
            array('create', 'cancel'),
            'volunteer_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
