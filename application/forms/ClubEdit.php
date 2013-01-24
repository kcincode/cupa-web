<?php

class Form_ClubEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_club;

    public function __construct($club = null)
    {
        $this->_club = $club;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'span5',
            'value' => (empty($this->_club->name)) ? null : $this->_club->name,
        ));

        $typeArray = array(
            'Open' => 'Open',
            'Womens' => 'Womens',
            'Mixed' => 'Mixed',
            'Masters' => 'Masters',
            'Masters, Grand Masters' => 'Masters, Grand Masters',
        );

        $this->addElement('select', 'type', array(
            'validators' => array(
                array('InArray', false, array(array_keys($typeArray))),
            ),
            'required' => true,
            'label' => 'Type:',
            'multiOptions' => $typeArray,
            'value' => (empty($this->_club->type)) ? null : $this->_club->type,
        ));

        $userTable = new Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsers(true) as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $captains = array();
        if(!empty($this->_club)) {
            $clubCaptainTable = new Model_DbTable_ClubCaptain();
            foreach($clubCaptainTable->fetchAllByClub($this->_club->id) as $person) {
                $captains[] = $person['user_id'];
            }
        }

        $this->addElement('multiselect', 'captains', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Captains:',
            'class' => 'select2 span6',
            'multiOptions' => $users,
            'data-placeholder' => 'Select Captains',
            'value' => $captains,
        ));

        $this->addElement('text', 'facebook', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Facebook Link:',
            'class' => 'span5',
            'description' => 'the part of the url after www.facebook.com excluding the first slash.',
            'value' => (empty($this->_club->facebook)) ? null : $this->_club->facebook,
        ));

        $this->addElement('text', 'twitter', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Twitter Account:',
            'class' => 'span3',
            'description' => 'Enter the @<name> twitter account.',
            'value' => (empty($this->_club->twitter)) ? null : $this->_club->twitter,
        ));

        $this->addElement('text', 'begin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First Year:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter a year or leave blank if unknown.',
            'value' => (empty($this->_club->begin)) ? null : $this->_club->begin,
        ));

        $this->addElement('text', 'end', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Last Year:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter a year or leave blank if current.',
            'value' => (empty($this->_club->end)) ? null : $this->_club->end,
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => false,
            'label' => 'Contact Email:',
            'class' => 'span4',
            'description' => 'Enter the contact email address or leave blank.',
            'value' => (empty($this->_club->email)) ? null : $this->_club->email,
        ));

        $this->addElement('text', 'website', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Website:',
            'class' => 'span6',
            'description' => 'Enter the whole url or leave blank if none.',
            'value' => (empty($this->_club->website)) ? null : $this->_club->website,
        ));

        $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Page Content:',
            'class' => 'span5 ckeditor',
            'description' => 'Enter what you want to be on the page to describe the team.',
            'value' => (empty($this->_club->content)) ? null : $this->_club->content,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_club)) ? 'Create' : 'Save',
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

        $title = (empty($this->_club)) ? 'Add Club Team' : 'Edit Club Team';
        $this->addDisplayGroup(
            array('name', 'type', 'captains', 'email', 'facebook', 'twitter', 'begin', 'end', 'website', 'content'),
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
