<?php

class Form_LeagueManage extends Twitter_Bootstrap_Form_Vertical
{
    protected $_leagueId;
    protected $_section;

    public function __construct($leagueId, $section)
    {
        $this->_leagueId = $leagueId;
        $this->_section = $section;

        parent::__construct();
    }


    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        $this->addPrefixPath('Form', APPLICATION_PATH . '/forms/');

        if($this->_section && method_exists($this, $this->_section)) {
            $this->{$this->_section}();
        }
    }

    private function add()
    {
        $userTable = new Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsersNotInLeague($this->_leagueId) as $row) {
            $users[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }

        $this->addElement('multiselect', 'user', array(
            'label' => 'Add Players (registration questions will NOT be answered)',
            'required' => true,
            'class' => 'span7 select2',
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'errorMessage' => 'Please select a vaild user.',
            'multiOptions' => $users,
            'placeholder' => 'Select players to add',
        ));

        $this->addElement('button', 'add', array(
            'type' => 'submit',
            'label' => 'Add Player',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'user',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            'style' => 'margin: -5px 0 0 5px',
        ));
    }

    private function remove()
    {
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $users = array();
        foreach($leagueMemberTable->fetchPlayersByLeague($this->_leagueId) as $row) {
            $users[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }

        $this->addElement('multiselect', 'user', array(
            'label' => 'Remove Players',
            'required' => true,
            'class' => 'span7 select2',
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'errorMessage' => 'Please select a vaild user.',
            'multiOptions' => $users,
            'placeholder' => 'Select players to remove',
        ));

        $this->addElement('button', 'remove', array(
            'type' => 'submit',
            'label' => 'Remove Player',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'user',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            'style' => 'margin: -5px 0 0 5px',
        ));
    }
}
