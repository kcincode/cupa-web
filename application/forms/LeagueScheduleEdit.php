<?php

class Form_LeagueScheduleEdit extends Zend_Form
{
    private $_game;
    private $_gameData;
    private $_leagueId;

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

        $day = $this->addElement('text', 'day', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Day/Time:',
            'description' => 'The day and time of the game.',
            'value' => (empty($this->_game->day)) ? null : $this->_game->day,
        ));

        $week = $this->addElement('text', 'week', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Week:',
            'description' => 'The week number of the game.',
            'value' => (empty($this->_game->week)) ? null : $this->_game->week,
        ));

        $field = $this->addElement('text', 'field', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Field:',
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

        $home_team = $this->addElement('select', 'home_team', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagueTeams))),
            ),
            'required' => true,
            'label' => 'Home Team:',
            'multiOptions' => $leagueTeams,
            'description' => 'Select the home team for the game.',
            'value' => (empty($this->_gameData[0])) ? null : $this->_gameData[0]->league_team_id,
        ));

        $home_score = $this->addElement('text', 'home_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'description' => 'Home team score.',
            'value' => (empty($this->_gameData[0])) ? null : $this->_gameData[0]->score,
        ));

        $away_team = $this->addElement('select', 'away_team', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagueTeams))),
            ),
            'required' => true,
            'label' => 'Away Team:',
            'multiOptions' => $leagueTeams,
            'description' => 'Select the home team for the game.',
            'value' => (empty($this->_gameData[1])) ? null : $this->_gameData[1]->league_team_id,
        ));

        $away_score = $this->addElement('text', 'away_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'description' => 'Away team score.',
            'value' => (empty($this->_gameData[1])) ? null : $this->_gameData[1]->score,
        ));

    }

}

