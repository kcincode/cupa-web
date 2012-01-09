<?php

class Cupa_Form_LeagueScheduleEdit extends Zend_Form
{
    private $_game;
    private $_gameData;

    public function __construct($gameId)
    {
        $gameTable = new Cupa_Model_DbTable_LeagueGame();
        $gameDataTable = new Cupa_Model_DbTable_LeagueGameData();

        $this->_game = $gameTable->find($gameId)->current();
        $this->_gameData = $gameDataTable->fetchGameData($gameId);

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $day = $this->addElement('text', 'day', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Day/Time:',
            'description' => 'The day and time of the game.',
            'value' => $this->_game->day,
        ));

        $week = $this->addElement('text', 'week', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Week:',
            'description' => 'The week number of the game.',
            'value' => $this->_game->week,
        ));

        $field = $this->addElement('text', 'field', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Field:',
            'description' => 'The field number of the game.',
            'value' => $this->_game->field,
        ));

        $leagueTeams = array();
        $leagueTeamsTable = new Cupa_Model_DbTable_LeagueTeam();
        foreach($leagueTeamsTable->fetchAllTeams($this->_game->league_id) as $row) {
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
            'value' => $this->_gameData[0]->league_team_id,
        ));

        $home_score = $this->addElement('text', 'home_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'description' => 'Home team score.',
            'value' => $this->_gameData[0]->score,
        ));

        $away_team = $this->addElement('select', 'away_team', array(
            'validators' => array(
                array('InArray', false, array(array_keys($leagueTeams))),
            ),
            'required' => true,
            'label' => 'Away Team:',
            'multiOptions' => $leagueTeams,
            'description' => 'Select the home team for the game.',
            'value' => $this->_gameData[1]->league_team_id,
        ));

        $away_score = $this->addElement('text', 'away_score', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Score:',
            'description' => 'Away team score.',
            'value' => $this->_gameData[1]->score,
        ));

    }

}

