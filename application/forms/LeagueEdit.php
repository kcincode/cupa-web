<?php

class Form_LeagueEdit extends Zend_Form
{
    private $_leagueData;
    

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        $this->addPrefixPath('Form', APPLICATION_PATH . '/forms/');        
    }
    
    public function loadSection($leagueId, $section)
    {
        $leagueTable = new Model_DbTable_League();
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

        $leagueSeasonTable = new Model_DbTable_LeagueSeason();
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

        
        $leagueTable = new Model_DbTable_League();
        
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
        
        $radioSelect = array('1' => 'Yes', '0' => 'No');
        
        $is_youth = $this->addElement('radio', 'is_youth', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is the league a youth league?',
            'value' => $this->_leagueData['information']['is_youth'],
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
        $user_teams = $this->addElement('radio', 'user_teams', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Are registrants able to create teams?',
            'value' => $this->_leagueData['information']['user_teams'], 
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
        $is_pods = $this->addElement('radio', 'is_pods', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Does the league have pods?',
            'value' => $this->_leagueData['information']['is_pods'], 
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
        $is_hat = $this->addElement('radio', 'is_hat', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this a hat tournament?',
            'value' => $this->_leagueData['information']['is_hat'], 
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
        $is_clinic = $this->addElement('radio', 'is_clinic', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this a clinic too?',
            'value' => $this->_leagueData['information']['is_clinic'], 
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
        $contact_email = $this->addElement('text', 'contact_email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress'),
            ),
            'required' => false,
            'label' => 'Contact Email:',
            'description' => 'Enter the email address for league contact. (opional)',
            'value' => $this->_leagueData['information']['contact_email'],
        ));
        
        $visible_from = $this->addElement('text', 'visible_from', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Visible From:',
            'description' => 'Enter the date/time when the league becomes visible to all.',
            'value' => $this->_leagueData['visible_from'],
        ));
        
        $is_archived = $this->addElement('radio', 'is_archived', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this league archived (not viewable)?',
            'value' => $this->_leagueData['is_archived'], 
            'multiOptions' => $radioSelect,
            'separator' => '&nbsp;&nbsp;'
        ));
        
    }
    
    private function information()
    {
        $userTable = new Model_DbTable_User();
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
    
    private function registration()
    {
        $registration_begin = $this->addElement('text', 'registration_begin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Registration Begin:',
            'description' => 'Enter a date/time when registration should begin.',
            'value' => $this->_leagueData['registration_begin'],
        ));
        
        $registration_end = $this->addElement('text', 'registration_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Registration End:',
            'description' => 'Enter a date/time when registration should end.',
            'value' => $this->_leagueData['registration_end'],
        ));
        
        $cost = $this->addElement('text', 'cost', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Cost:',
            'description' => 'Enter the amount the league costs to register.',
            'value' => $this->_leagueData['information']['cost'],
        ));

        $limitGenders = 0;
        if(!empty($this->_leagueData['limits']['male_players']) and 
           !empty($this->_leagueData['limits']['female_players']) and
           empty($this->_leagueData['limits']['total_players'])) {
           $limitGenders = 1;
        }
        
        $limit_select = $this->addElement('checkbox', 'limit_select', array(
            'required' => false,
            'label' => 'Enter specific gender limits',
            'value' => $limitGenders, 
        ));
        
        $male_players = $this->addElement('text', 'male_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Male Players:',
            'description' => 'Enter the max # of male players. (optional)',
            'value' => $this->_leagueData['limits']['male_players'],
        ));
        
        $female_players = $this->addElement('text', 'female_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Female Players:',
            'description' => 'Enter the max # of female players. (optional)',
            'value' => $this->_leagueData['limits']['female_players'],
        ));
        
        $total_players = $this->addElement('text', 'total_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Total Players:',
            'description' => 'Enter the max # of total players.',
            'value' => $this->_leagueData['limits']['total_players'],
        ));
        
        $teams = $this->addElement('text', 'teams', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Teams:',
            'description' => 'Enter the max # of teams.',
            'value' => $this->_leagueData['limits']['teams'],
        ));

        $paypal_code = $this->addElement('text', 'paypal_code', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Paypal Code:',
            'description' => 'Enter the paypal code for payment.',
            'value' => $this->_leagueData['information']['paypal_code'],
        ));
    }
    
    private function description()
    {
        $description = $this->addElement('textarea', 'description', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'League Description:',
            'description' => 'Enter the information you want displayed for the league here.',
            'value' => $this->_leagueData['information']['description'],
        ));
        
    }
    
}