<?php

class Form_LeagueMove extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_section;
    protected $_session;

    public function __construct($section)
    {
        $this->_section = $section;
        $this->_session = new Zend_Session_Namespace('admin_league_move');

        if($section == 'src_league') {
            $this->_session->unsetAll();
        }

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        $this->addPrefixPath('Form', APPLICATION_PATH . '/forms/');

        $section = $this->_section;
        if($section && method_exists($this, $section)) {
            $this->$section();
        }

        $buttons = array();
        if($section != 'src_league') {
            $this->addElement('button', 'back', array(
                'type' => 'submit',
                'label' => 'Back',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'arrow-left',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            ));
            $buttons[] = 'back';
        }

        if($section != 'done') {
            $this->addElement('button', 'next', array(
                'type' => 'submit',
                'label' => 'Next',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'arrow-right',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_RIGHT,
            ));
            $buttons[] = 'next';
        } else {
            $this->addElement('button', 'finish', array(
                'type' => 'submit',
                'label' => 'Move Player',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS,
                'escape' => false,
                'icon' => 'hdd',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_RIGHT,
            ));

            $buttons[] = 'finish';
        }

        $this->addDisplayGroup(
            $buttons,
            'league_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }

    private function src_league()
    {
        $leagues = array();
        $leagueTable = new Model_DbTable_League();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        foreach($leagueTable->fetchAllCurrentLeagues() as $league) {
            $season = $leagueSeasonTable->fetchName($league->season);
            $leagues[$league->id] = $league->year . ' ' . $league->day . ' ' . $season . ' ' . $league->name;
        }

        $this->addElement('select', 'league_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagues))),
            ),
            'required' => true,
            'label' => 'League:',
            'class' => 'span6',
            'multiOptions' => $leagues,
        ));

        $this->addDisplayGroup(
            array('league_id'),
            'league_move_edit_form',
            array(
                'legend' => 'Select Source League',
            )
        );
    }

    private function src_player()
    {
        $leagueMemberTable = new Model_DbTable_LeagueMember();
        $members = array();
        foreach($leagueMemberTable->fetchPlayersByLeague($this->_session->src_league['league_id']) as $member) {
            $members[$member['id']] = $member['first_name'] . ' ' . $member['last_name'];
        }

        $this->addElement('select', 'league_member_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($members))),
            ),
            'required' => true,
            'label' => 'League Member:',
            'class' => 'span6',
            'multiOptions' => $members,
        ));


    }

    private function target_league()
    {
        $leagues = array();
        $leagueTable = new Model_DbTable_League();
        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        foreach($leagueTable->fetchAllCurrentLeagues() as $league) {
            if($this->_session->src_league['league_id'] == $league['id']) {
                continue;
            }

            $season = $leagueSeasonTable->fetchName($league->season);
            $leagues[$league->id] = $league->year . ' ' . $league->day . ' ' . $season . ' ' . $league->name;
        }

        $this->addElement('select', 'league_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagues))),
            ),
            'required' => true,
            'label' => 'League:',
            'class' => 'span6',
            'multiOptions' => $leagues,
        ));

        $this->addDisplayGroup(
            array('league_id'),
            'league_move_edit_form',
            array(
                'legend' => 'Select Target League',
            )
        );
    }

    private function target_team()
    {
        $leagueTeamTable = new Model_DbTable_LeagueTeam();
        $teams = array();
        $teams[0] = 'No Team';
        foreach($leagueTeamTable->fetchAllTeams($this->_session->target_league['league_id']) as $team) {
            $teams[$team->id] = $team->name;
        }

        $this->addElement('select', 'league_team_id', array(
            'validators' => array(
                array('InArray', false, array(array_keys($teams))),
            ),
            'required' => true,
            'label' => 'Team:',
            'class' => 'span6',
            'multiOptions' => $teams,
        ));

        $this->addDisplayGroup(
            array('league_team_id'),
            'league_move_edit_form',
            array(
                'legend' => 'Select Target Team',
            )
        );
    }

}
