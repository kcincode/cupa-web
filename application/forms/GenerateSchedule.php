<?php
class Cupa_Form_GenerateSchedule extends Zend_Form
{    
    private $_league;
    private $_teams;

    public function __construct($league)
    {
        $this->_league = $league;
        
        $leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
        $this->_teams = $leagueTeamTable->fetchAllTeams($this->_league->id);
        
        parent::__construct();
    }
    
    public function init()
    {
        $number_of_teams = $this->addElement('text', 'number_of_teams', array(
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

        $number_of_fields = $this->addElement('text', 'number_of_fields', array(
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
        
        $home_advantage = $this->addElement('select', 'home_advantage', array(
            'validators' => array(
                array('InArray', false, array(array_keys($teams))),
            ),
            'required' => false,
            'label' => 'Home Advantage:',
            'description' => 'Select the team to have home field advantage.',
            'multiOptions' => $teams,
        ));

        $home_field = $this->addElement('text', 'home_field', array(
            'filter' => array('Digits'),
            'required' => true,
            'label' => 'Home Advantage Field:',
            'value' => 1,
            'description' => 'Enter the field number that is the home field for the selected team above.',
        ));

    }
    
}
