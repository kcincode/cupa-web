<?php
class Form_GenerateSchedule extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_league;
    protected $_teams;

    public function __construct($league)
    {
        $this->_league = $league;

        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $this->_teams = $leagueTeamTable->fetchAllTeams($this->_league->id);

        parent::__construct();
    }

    public function init()
    {
        $this->addElement('text', 'number_of_teams', array(
            'filter' => array('Digits'),
            'validators' => array(
                array('GreaterThan', false, array('min' => 2)),
            ),
            'required' => true,
            'label' => '# of teams:',
            'value' => count($this->_teams),
            'description' => 'Enter the number of teams you have.',
        ));

        $fields = array();
        for($i = 0; $i < ceil(count($this->_teams) / 2); $i++) {
            $fields[] = $i + 1;
        }

        $this->addElement('text', 'number_of_fields', array(
            'filter' => array('StringTrim'),
            'required' => true,
            'label' => 'Field Numbers:',
            'value' => implode(',', $fields),
            'description' => 'Enter the field numbers seperated by commas.',
        ));

        $teams = array();
        $teams[0] = "Select a Team";
        foreach($this->_teams as $team) {
            $teams[$team->id] = $team->name;
        }

        $this->addElement('select', 'home_advantage', array(
            'validators' => array(
                array('InArray', false, array(array_keys($teams))),
            ),
            'required' => false,
            'label' => 'Home Advantage:',
            'description' => 'Select the team to have home field advantage.',
            'multiOptions' => $teams,
        ));

        $this->addElement('text', 'home_field', array(
            'filter' => array('Digits'),
            'required' => true,
            'label' => 'Home Advantage Field:',
            'value' => 1,
            'description' => 'Enter the field number that is the home field for the selected team above.',
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Generate Schedule',
            'onclick' => "return confirm('Are you sure?  This will clear out any current games!');",
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
            array('number_of_teams', 'number_of_fields', 'home_advantage', 'home_field'),
            'schedule_edit_form',
            array(
                'legend' => 'Generate Schedule',
            )
        );
        $this->addDisplayGroup(
            array('save', 'cancel'),
            'schedule_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }

}
