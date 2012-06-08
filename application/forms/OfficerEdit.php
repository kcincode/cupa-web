<?php

class Form_OfficerEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $userTable = new Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $this->addElement('select', 'user_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'User:',
            'multiOptions' => $users,
            'description' => 'Select the user for the position.',

        ));


        $this->addElement('text', 'position', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Position:',
            'description' => 'Enter the date the position name.',

        ));

        $this->addElement('text', 'since', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Date',
            ),
            'required' => true,
            'label' => 'Since:',
            'description' => 'Enter the date the position was active.',
        ));

        $this->addElement('text', 'to', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Date',
            ),
            'required' => false,
            'label' => 'To:',
            'description' => 'Enter the date the position was revoked.',
        ));

        $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'description' => 'Lower numbers are shown first.',
        ));

        $this->addElement('textarea', 'description', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Position Description',
            'description' => 'Enter what this position is repsponsible for.',
        ));
    }

    public function loadFromOfficer($officer)
    {
        $this->getElement('user_id')->setValue($officer->user_id);
        $this->getElement('position')->setValue($officer->position);
        $this->getElement('since')->setValue($officer->since);
        $this->getElement('to')->setValue($officer->to);
        $this->getElement('weight')->setValue($officer->weight);
        $this->getElement('description')->setValue($officer->description);
    }

}

