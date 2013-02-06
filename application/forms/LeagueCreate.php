<?php

class Form_LeagueCreate extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_season;

    public function __construct($season)
    {
        $this->_season = $season;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        $this->addPrefixPath('Form', APPLICATION_PATH . '/forms/');

        $years = array_combine(range(date('Y') - 5, date('Y') + 1), range(date('Y') - 5, date('Y') + 1));
        $this->addElement('select', 'year', array(
            'required' => true,
            'label' => 'Year:',
            'multiOptions' => $years,
            'class' => 'span2',
            'validators' => array(
                array('InArray', false, array(array_keys($years))),
            ),
            'description' => 'The year the league takes place.',
            'value' => date('Y'),
        ));

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
        $seasons = array();
        foreach($leagueSeasonTable->fetchAllSeasons() as $season) {
            $seasons[$season->id] = $season->name;
        }

        $this->addElement('select', 'season', array(
            'validators' => array(
                array('InArray', false, array(array_keys($seasons))),
            ),
            'required' => true,
            'label' => 'Season:',
            'class' => 'span2',
            'multiOptions' => $seasons,
            'description' => 'Select the season the league is played.',
            'value' => $this->_season,
        ));

        $leagueTable = new Model_DbTable_League();
        $info = $leagueTable->info();
        $tmp = array_values(explode(',', str_replace("'",'', substr($info['metadata']['day']['DATA_TYPE'], 6, -1))));
        $targets = array_combine($tmp, $tmp);

        $this->addElement('select', 'day', array(
            'validators' => array(
                array('InArray', false, array(array_keys($targets))),
            ),
            'required' => true,
            'label' => 'Day:',
            'multiOptions' => $targets,
            'class' => 'span2',
            'description' => 'Select the day the league is played.',
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Name:',
            'description' => 'The name of the league (optional).',
        ));

        $userTable = new Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $this->addElement('multiselect', 'directors', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Directors:',
            'multiOptions' => $users,
            'class' => 'select2 span7',
            'description' => 'Please select one or more directors.',
        ));

        $this->addDisplayGroup(
            array('year', 'season', 'day', 'name', 'directors'),
            'pickup_edit_form',
            array(
                'legend' => 'Create a League',
            )
        );

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Create',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('button', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
        ));

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'league_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
