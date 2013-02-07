<?php

class Form_LeagueScheduleEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_game;
    protected $_gameData;
    protected $_leagueId;

    public function __construct($gameId = null, $leagueId = null)
    {
        $this->_leagueId = $leagueId;

        if(is_numeric($gameId)) {
            $gameTable = new Model_DbTable_LeagueGame();
            $gameDataTable = new Model_DbTable_LeagueGameData();

            $this->_game = $gameTable->find($gameId)->current();
            $this->_gameData = $gameDataTable->fetchGameData($gameId);
        }

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'day', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Day/Time:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'The day and time of the game.',
            'value' => (empty($this->_game->day)) ? null : date('m/d/Y H:i', strtotime($this->_game->day)),
        ));

        $weeks = array_merge(array('Select Week'), array_combine(range(1, 20), range(1, 20)));
        $this->addElement('select', 'week', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0)),
            ),
            'multiOptions' => $weeks,
            'label' => 'Week:',
            'class' => 'span2',
            'description' => 'The week number of the game.',
            'value' => (empty($this->_game->week)) ? null : $this->_game->week,
        ));

        $fields = array_merge(array('Select Field'), array_combine(range(1, 10), range(1, 10)));
        $this->addElement('select', 'field', array(
            'filters' => array('digits'),
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0)),
            ),
            'multiOptions' => $fields,
            'label' => 'Field:',
            'class' => 'span2',
            'description' => 'The field number of the game.',
            'value' => (empty($this->_game->field)) ? null : $this->_game->field,
        ));

        $leagueTeams = array();
        $leagueTeamsTable = new Model_DbTable_LeagueTeam();
        if(empty($this->_gameData[0])) {
            $leagueTeams[0] = 'Select a Team';
        }
        foreach($leagueTeamsTable->fetchAllTeams($this->_leagueId) as $row) {
            $leagueTeams[$row->id] = $row->name;
        }

        $this->addElement('select', 'home_team', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagueTeams))),
            ),
            'required' => true,
            'label' => 'Home Team:',
            'class' => 'span5',
            'multiOptions' => $leagueTeams,
            'description' => 'Select the home team for the game.',
            'value' => (empty($this->_gameData[0])) ? null : $this->_gameData[0]->league_team_id,
        ));

        $this->addElement('text', 'home_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'class' => 'span1 align-center',
            'description' => 'Home team score.',
            'value' => (empty($this->_gameData[0])) ? 0 : $this->_gameData[0]->score,
        ));

        $this->addElement('select', 'away_team', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagueTeams))),
            ),
            'required' => true,
            'label' => 'Away Team:',
            'class' => 'span5',
            'multiOptions' => $leagueTeams,
            'description' => 'Select the home team for the game.',
            'value' => (empty($this->_gameData[1])) ? null : $this->_gameData[1]->league_team_id,
        ));

        $this->addElement('text', 'away_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'class' => 'span1 align-center',
            'description' => 'Away team score.',
            'value' => (empty($this->_gameData[1])) ? 0 : $this->_gameData[1]->score,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_gameData)) ? 'Create' : 'Save',
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

        $title = (empty($this->_gameData)) ? 'Create Game' : 'Edit Game';
        $this->addDisplayGroup(
            array('day', 'week', 'field'),
            'schedule_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('home_team', 'home_score'),
            'schedule_home_edit_form',
            array(
                'legend' => 'Home Team',
            )
        );

        $this->addDisplayGroup(
            array('away_team', 'away_score'),
            'schedule_away_edit_form',
            array(
                'legend' => 'Away Team',
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
