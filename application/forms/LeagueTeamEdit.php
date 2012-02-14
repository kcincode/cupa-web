<?php

class Form_LeagueTeamEdit extends Zend_Form
{
    private $_team;
    
    public function __construct($team)
    {
        parent::__construct();
        $this->_team = $team;
        
        $this->index();
    }
    
    public function index()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'value' => $this->_team->name,
            'label' => 'Name:',
        ));

        $userTable = new Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }
        
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $captains = array();
        foreach($leagueMemberTable->fetchAllByType($this->_team->league_id, 'captain', $this->_team->id) as $member) {
            $user = $userTable->find($member['user_id'])->current();
            $captains[] = $user->id;
        }

        $captains = $this->addElement('multiselect', 'captains', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Captains:',
            'multiOptions' => $users,
            'description' => 'Select one or more users for captain.',
            'value' => $captains,
            'data-placeholder' => 'Select one or more captains'
        ));

        $color = $this->addElement('text', 'color', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Color:',
            'value' => $this->_team->color,
            'description' => 'Enter the color of the team.',
        ));
        
        $color_code = $this->addElement('text', 'color_code', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'value' => $this->_team->color_code,
            'label' => 'Select the color:',
        ));
        
        $final_rank = $this->addElement('text', 'final_rank', array(
            'filters' => array('digits'),
            'required' => false,
            'label' => 'Final Rank:',
            'value' => $this->_team->final_rank,
            'description' => 'Enter the ranking when the league is done.',
        ));
        
    }
    
    public function loadFromClub($club)
    {
        $this->getElement('name')->setValue($club->name);
        $this->getElement('type')->setValue($club->type);
        $this->getElement('facebook')->setValue($club->facebook);
        $this->getElement('twitter')->setValue($club->twitter);
        $this->getElement('begin')->setValue($club->begin);
        $this->getElement('end')->setValue($club->end);
        $this->getElement('email')->setValue($club->email);
        $this->getElement('website')->setValue($club->website);
        $this->getElement('content')->setValue($club->content);
        
    }


}

