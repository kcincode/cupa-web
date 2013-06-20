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

        $categories = array(0 => 'Select a Category');
        $volunteerCategoryTable = new Model_DbTable_VolunteerCategory();
        foreach($volunteerCategoryTable->fetchAllCategories() as $category) {
            $categories[$category->id] = $category->category;
        }
        $this->addElement('select', 'volunteer_category_id', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0, 'messages' => array('notGreaterThan' => 'Please select a category.'))),
            ),
            'class' => 'select2 span4',
            'multiOptions' => $categories,
            'label' => 'Opportunity Category:',
            'value' => (empty($this->_volunteer['volunteer_category_id'])) ? null : $this->_volunteer['volunteer_category_id'],
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'span5',
            'value' => (empty($this->_volunteer['name'])) ? null : $this->_volunteer['name'],
        ));

        $this->addElement('text', 'start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'class' => 'span3 datetimepicker align-center',
            'value' => (empty($this->_volunteer['start'])) ? null : date('m/d/Y H:i', strtotime($this->_volunteer['start'])),
        ));

        $this->addElement('text', 'end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'class' => 'span3 datetimepicker align-center',
            'value' => (empty($this->_volunteer['end'])) ? null : date('m/d/Y H:i', strtotime($this->_volunteer['end'])),
        ));

        $contacts = array(0 => 'Primary Contact');
        $userTable = new Model_DbTable_User();
        foreach($userTable->fetchAllUsers(true, true) as $user) {
            $contacts[$user->id] = $user->first_name . ' ' . $user->last_name;
        }
        $this->addElement('select', 'contact_id', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0, 'messages' => array('notGreaterThan' => 'Please select a primary contact.'))),
            ),
            'class' => 'select2 span4',
            'multiOptions' => $contacts,
            'label' => 'Primary Contact:',
            'value' => (empty($this->_volunteer['contact_id'])) ? null : $this->_volunteer['contact_id'],
        ));

        $this->addElement('text', 'max_volunteers', array(
            'filters' => array('Digits'),
            'required' => true,
            'label' => 'Max Volunteers:',
            'class' => 'span1 align-center',
            'description' => 'Enter the max volunteers.',
            'value' => (empty($this->_volunteer['max_volunteers'])) ? null : $this->_volunteer['max_volunteers'],
        ));

        $this->addElement('textarea', 'information', array(
            'required' => true,
            'label' => 'Information:',
            'required' => true,
            'class' => 'ckeditor',
            'description' => 'Enter any information you would like to display to the user.',
            'value' => (empty($this->_volunteer['information'])) ? null : $this->_volunteer['information'],
        ));

        $this->addElement('text', 'location_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
            'class' => 'span6',
            'description' => 'Enter the location name.',
            'value' => (empty($this->_volunteer['location_name'])) ? null : $this->_volunteer['location_name'],
        ));

        $this->addElement('text', 'address', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Street:',
            'class' => 'span6',
            'description' => 'Enter the locations street address.',
            'value' => (empty($this->_volunteer['address'])) ? null : $this->_volunteer['address'],
        ));

        $this->addElement('text', 'city', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'City:',
            'class' => 'span4',
            'description' => 'Enter the locations city.',
            'value' => (empty($this->_volunteer['city'])) ? null : $this->_volunteer['city'],
        ));

        $this->addElement('text', 'state', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'State',
            'class' => 'span1 align-center',
            'description' => 'Enter the locations state.',
            'value' => (empty($this->_volunteer['state'])) ? null : $this->_volunteer['state'],
        ));

        $this->addElement('text', 'zip', array(
            'filters' => array('Digits'),
            'required' => true,
            'label' => 'Zip',
            'class' => 'span2 align-center',
            'description' => 'Enter the locations zipcode.',
            'value' => (empty($this->_volunteer['zip'])) ? null : $this->_volunteer['zip'],
        ));

        $this->addElement('button', 'create', array(
            'type' => 'submit',
            'label' => (empty($this->_volunteer)) ? 'Create' : 'Update',
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

        $title = (empty($this->_volunteer)) ? 'Create a Volunteer Opportunity' : 'Edit a Volunteer Opportunity';
        $this->addDisplayGroup(
            array('volunteer_category_id', 'name', 'start', 'end', 'contact_id', 'max_volunteers', 'information'),
            'volunteer_location_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('location_name', 'address', 'city', 'state', 'zip'),
            'volunteer_edit_form',
            array(
                'legend' => 'Volunteer Opportunities Location',
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
