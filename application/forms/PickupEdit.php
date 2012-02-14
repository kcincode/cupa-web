<?php

class Form_PickupEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $title = $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
        ));
        
        $day = $this->addElement('text', 'day', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Day:',
        ));
        
        $time = $this->addElement('text', 'time', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Time:',
        ));
        
        $info = $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Information:',
            'description' => 'Enter what you want to be on the page to describe the pickup.',
        ));


        $userTable = new Model_DbTable_User();
        $users = array('0' => 'Unknown');
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }
        
        $user_id = $this->addElement('select', 'user_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Contact:',
            'multiOptions' => $users,
            'description' => 'Select the contact person, or Unknown.',
        ));

        $email = $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Contact Email:',
            'description' => 'This will overwrite the contacts email if specified.',
        ));

        $location = $this->addElement('text', 'location', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
            'description' => 'Enter text for the location.',

        ));
        
        $map = $this->addElement('text', 'map', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map:',
            'description' => 'Enter the url of the map to the location.',
        ));
        
        $weight = $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'description' => 'Lower numbers are shown first.',
        ));
        
        $is_visible = $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
        ));
    }
    
    public function loadFromPickup($pickup)
    {
        $this->getElement('title')->setValue($pickup->title);
        $this->getElement('day')->setValue($pickup->day);
        $this->getElement('time')->setValue($pickup->time);
        $this->getElement('info')->setValue($pickup->info);
        $this->getElement('user_id')->setValue($pickup->user_id);
        $this->getElement('email')->setValue($pickup->email);
        $this->getElement('location')->setValue($pickup->location);
        $this->getElement('map')->setValue($pickup->map);
        $this->getElement('weight')->setValue($pickup->weight);
        $this->getElement('is_visible')->setValue($pickup->is_visible);
    }


}

