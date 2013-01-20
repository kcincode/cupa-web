<?php

class Form_PickupEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_pickup;

    public function __construct($pickup = null)
    {
        $this->_pickup = $pickup;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'span5',
            'value' => (empty($this->_pickup->title)) ? null : $this->_pickup->title,
        ));

        $this->addElement('text', 'day', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Day:',
            'class' => 'span4',
            'value' => (empty($this->_pickup->day)) ? null : $this->_pickup->day,
        ));

        $this->addElement('text', 'time', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Time:',
            'class' => 'span3',
            'value' => (empty($this->_pickup->time)) ? null : $this->_pickup->time,
        ));

        $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Information:',
            'class' => 'span6 ckeditor',
            'style' => 'height: 125px;',
            'description' => 'Enter what you want to be on the page to describe the pickup.',
            'value' => (empty($this->_pickup->info)) ? null : $this->_pickup->info,
        ));


        $userTable = new Model_DbTable_User();
        $users = array('0' => 'Unknown');
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $this->addElement('select', 'user_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => false,
            'label' => 'Contact:',
            'multiOptions' => $users,
            'description' => 'Select the contact person, or Unknown.',
            'value' => (empty($this->_pickup->user_id)) ? null : $this->_pickup->user_id,
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => false,
            'class' => 'span5',
            'label' => 'Contact Email:',
            'description' => 'This will overwrite the contacts email if specified.',
            'value' => (empty($this->_pickup->email)) ? null : $this->_pickup->email,
        ));

        $this->addElement('text', 'location', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
            'class' => 'span6',
            'description' => 'Enter text for the location.',
            'value' => (empty($this->_pickup->location)) ? null : $this->_pickup->location,
        ));

        $this->addElement('text', 'map', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Map:',
            'class' => 'span7',
            'description' => 'Enter the url of the map to the location.',
            'value' => (empty($this->_pickup->map)) ? null : $this->_pickup->map,
        ));

        $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Lower numbers are shown first.',
            'value' => (empty($this->_pickup->weight)) ? null : $this->_pickup->weight,
        ));

        $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
            'value' => (empty($this->_pickup->is_visible)) ? null : $this->_pickup->is_visible,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_pickup)) ? 'Create' : 'Save',
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

        $title = (empty($this->_pickup)) ? 'Add Pickup' : 'Edit Pickup';
        $this->addDisplayGroup(
            array('title', 'day', 'time', 'info', 'user_id', 'email', 'location', 'map', 'weight', 'is_visible'),
            'pickup_edit_form',
            array(
                'legend' => $title,
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
