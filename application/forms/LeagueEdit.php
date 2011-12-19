<?php

class Cupa_Form_LeagueEdit extends Zend_Form
{
    private $_leagueData;
    

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        $this->addPrefixPath('Cupa_Form', APPLICATION_PATH . '/forms/');        
    }
    
    public function loadSection($leagueId, $section)
    {
        $leagueTable = new Cupa_Model_DbTable_League();
        $this->_leagueData = $leagueTable->fetchLeagueData($leagueId);
        
        if($section && method_exists($this, $section)) {
            $this->$section();
        }
    }
    
    private function league()
    {
        $year = $this->addElement('text', 'year', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Year:',
            'description' => 'The year the league takes place.',
            'value' => $this->_leagueData['year'],
        ));

        $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
        $seasons = array();
        foreach($leagueSeasonTable->fetchAllSeasons() as $season) {
            $seasons[$season->id] = $season->name;
        }
        
        $season = $this->addElement('select', 'season', array(
            'validators' => array(
                array('InArray', false, array(array_keys($seasons))),
            ),
            'required' => true,
            'label' => 'Season:',
            'multiOptions' => $seasons,
            'description' => 'Select the season the league is played.',
            'value' => $this->_leagueData['season'],
        ));

        
        $leagueTable = new Cupa_Model_DbTable_League();
        
        $info = $leagueTable->info();
        $tmp = array_values(explode(',', str_replace("'",'', substr($info['metadata']['day']['DATA_TYPE'], 6, -1))));
        $targets = array_combine($tmp, $tmp);

        $day = $this->addElement('select', 'day', array(
            'validators' => array(
                array('InArray', false, array(array_keys($targets))),
            ),
            'required' => true,
            'label' => 'Day:',
            'multiOptions' => $targets,
            'description' => 'Select the day the league is played.',
            'value' => $this->_leagueData['day'],
        ));

        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Name:',
            'description' => 'The name of the league (optional).',
            'value' => $this->_leagueData['name'],
        ));
        
    }
    
    private function information()
    {
        $userTable = new Cupa_Model_DbTable_User();
        $users = array();
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }
        
        
        $directors = array();
        foreach($this->_leagueData['directors'] as $member) {
            $user = $userTable->find($member['user_id'])->current();
            $directors[] = $user->id;
        }
        
        $directors = $this->addElement('multiselect', 'directors', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Directors:',
            'multiOptions' => $users,
            'description' => 'Select one or more users.',
            'value' => $directors,
            'data-placeholder' => 'Select one or more directors'
        ));

        $info = $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'League Information:',
            'description' => 'Enter a quick description of the league (optional)',
            'value' => $this->_leagueData['info'],
        ));

        $league_name = $this->addElement('text', 'league_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'The name of the location for league.',
            'value' => $this->_leagueData['locations']['league']['location'],
        ));
        
        
        $leagueAddress = $this->_leagueData['locations']['league']['address_street'] . ', '
                 . $this->_leagueData['locations']['league']['address_city'] . ', '
                 . $this->_leagueData['locations']['league']['address_state'] . ' '
                 . $this->_leagueData['locations']['league']['address_zip'];

        $league_address = $this->addElement('address', 'league_address', array(
            'required' => true,
            'label' => 'Address:',
            'description' => 'Enter in the Street, City, State, and Zipcode.',
            'value' => $leagueAddress,
        ));
        
        $league_map_link = $this->addElement('text', 'league_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'description' => 'A link to a map of where the fields are.',
            'value' => $this->_leagueData['locations']['league']['map_link'],
        ));

        $league_start = $this->addElement('text', 'league_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'description' => 'The name of the league (optional).',
            'value' => $this->_leagueData['locations']['league']['start'],
        ));
        
        $league_end = $this->addElement('text', 'league_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'description' => 'The name of the league (optional).',
            'value' => $this->_leagueData['locations']['league']['end'],
        ));
        
        $league_photo_link = $this->addElement('text', 'league_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'description' => 'A link to photos.',
            'value' => $this->_leagueData['locations']['league']['photo_link'],
        ));
        

        
        if(empty($this->_leagueData['locations']['tournament'])) {
            $this->_leagueData['locations']['tournament'] = array(
                'location' => null,
                'address_street' => null,
                'address_city' => null,
                'address_state' => null,
                'address_zip' => null,
                'map_link' => null,
                'photo_link' => null,
                'start' => null,
                'end' => null,
            );
        }

        $tournament_exists = $this->addElement('checkbox', 'tournament_ignore', array(
            'required' => false,
            'label' => 'Ignore this',
            'value' => ($this->_leagueData['locations']['tournament']['location'] === null) ? 1 : 0,
        ));        
        
        $tournament_name = $this->addElement('text', 'tournament_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'The name of the location for the tournament.',
            'value' => $this->_leagueData['locations']['tournament']['location'],
        ));
        
        $tournamentAddress = $this->_leagueData['locations']['tournament']['address_street'] . ', '
                 . $this->_leagueData['locations']['tournament']['address_city'] . ', '
                 . $this->_leagueData['locations']['tournament']['address_state'] . ' '
                 . $this->_leagueData['locations']['tournament']['address_zip'];

        $tournament_address = $this->addElement('address', 'tournament_address', array(
            'required' => true,
            'label' => 'Address:',
            'description' => 'Enter in the Street, City, State, and Zipcode.',
            'value' => $tournamentAddress,
        ));
        
        $tournament_map_link = $this->addElement('text', 'tournament_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'description' => 'A link to a map of where the fields are.',
            'value' => $this->_leagueData['locations']['tournament']['map_link'],
        ));

        $tournament_start = $this->addElement('text', 'tournament_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'description' => 'Enter the start date/time for the tournament.',
            'value' => $this->_leagueData['locations']['tournament']['start'],
        ));
        
        $tournament_end = $this->addElement('text', 'tournament_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'description' => 'Enter the end date/time for the tournament.',
            'value' => $this->_leagueData['locations']['tournament']['end'],
        ));
        
        $tournament_photo_link = $this->addElement('text', 'tournament_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'description' => 'A link to photos.',
            'value' => $this->_leagueData['locations']['tournament']['photo_link'],
        ));
        
        if(empty($this->_leagueData['locations']['draft'])) {
            $this->_leagueData['locations']['draft'] = array(
                'location' => null,
                'address_street' => null,
                'address_city' => null,
                'address_state' => null,
                'address_zip' => null,
                'map_link' => null,
                'photo_link' => null,
                'start' => null,
                'end' => null,
            );
        }

        $draft_exists = $this->addElement('checkbox', 'draft_ignore', array(
            'required' => false,
            'label' => 'Ignore this',
            'value' => ($this->_leagueData['locations']['draft']['location'] === null) ? 1 : 0,
        ));        

        $draft_name = $this->addElement('text', 'draft_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'The name of the location for the draft.',
            'value' => $this->_leagueData['locations']['draft']['location'],
        ));
        
        $draftAddress = $this->_leagueData['locations']['draft']['address_street'] . ', '
                 . $this->_leagueData['locations']['draft']['address_city'] . ', '
                 . $this->_leagueData['locations']['draft']['address_state'] . ' '
                 . $this->_leagueData['locations']['draft']['address_zip'];

        $draft_address = $this->addElement('address', 'draft_address', array(
            'required' => true,
            'label' => 'Address:',
            'description' => 'Enter in the Street, City, State, and Zipcode.',
            'value' => $draftAddress,
        ));
        
        $draft_map_link = $this->addElement('text', 'draft_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'description' => 'A link to a map of where the draft is held.',
            'value' => $this->_leagueData['locations']['draft']['map_link'],
        ));

        $draft_start = $this->addElement('text', 'draft_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'description' => 'Enter the start date/time for the draft.',
            'value' => $this->_leagueData['locations']['draft']['start'],
        ));
        
        $draft_end = $this->addElement('text', 'draft_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'description' => 'Enter the end date/time for the draft.',
            'value' => $this->_leagueData['locations']['draft']['end'],
        ));
        
        $draft_photo_link = $this->addElement('text', 'draft_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'description' => 'A link to photos.',
            'value' => $this->_leagueData['locations']['draft']['photo_link'],
        ));
        
    }
    
}