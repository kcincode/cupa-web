<?php

class Form_LeagueEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_leagueData;
    protected $_section;

    public function __construct($leagueId, $section)
    {
        $leagueTable = new Model_DbTable_League();
        $this->_leagueData = $leagueTable->fetchLeagueData($leagueId);
        $this->_section = $section;

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


        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Update',
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
            'value' => $this->_leagueData['year'],
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
            'value' => $this->_leagueData['season'],
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
            'value' => $this->_leagueData['day'],
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Name:',
            'description' => 'The name of the league (optional).',
            'value' => $this->_leagueData['name'],
        ));

        $radioSelect = array('1' => 'Yes', '0' => 'No');

        $this->addElement('radio', 'is_youth', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is the league a youth league?',
            'value' => $this->_leagueData['information']['is_youth'],
            'multiOptions' => $radioSelect,
        ));

        $this->addElement('radio', 'user_teams', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Are registrants able to create teams?',
            'value' => $this->_leagueData['information']['user_teams'],
            'multiOptions' => $radioSelect,
        ));

        $this->addElement('radio', 'is_pods', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Does the league have pods? (Not Implemented Yet)',
            'value' => $this->_leagueData['information']['is_pods'],
            'multiOptions' => $radioSelect,
        ));

        $this->addElement('radio', 'is_hat', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this a hat tournament?',
            'value' => $this->_leagueData['information']['is_hat'],
            'multiOptions' => $radioSelect,
        ));

        $this->addElement('radio', 'is_clinic', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this a clinic too?',
            'value' => $this->_leagueData['information']['is_clinic'],
            'multiOptions' => $radioSelect,
        ));

        $this->addElement('text', 'contact_email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress'),
            ),
            'required' => false,
            'class' => 'span5',
            'label' => 'Contact Email:',
            'description' => 'Enter the email address for league contact. (opional)',
            'value' => $this->_leagueData['information']['contact_email'],
        ));

        $this->addElement('text', 'visible_from', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Visible From:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'Enter the date/time when the league becomes visible to all.',
            'value' => date('m/d/Y H:i', strtotime($this->_leagueData['visible_from'])),
        ));

        $this->addElement('radio', 'is_archived', array(
            'validators' => array(
                array('InArray', false, array(array_keys($radioSelect))),
            ),
            'required' => true,
            'label' => 'Is this league archived (not viewable)?',
            'value' => $this->_leagueData['is_archived'],
            'multiOptions' => $radioSelect,
        ));

        $this->addDisplayGroup(
            array('year', 'season', 'day', 'name', 'is_youth', 'user_teams', 'is_pods', 'is_hat', 'is_clinic', 'contact_email', 'visible_from', 'is_archived'),
            'pickup_edit_form',
            array(
                'legend' => 'Update League Settings',
            )
        );
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

        $this->addElement('multiselect', 'directors', array(
            'validators' => array(
                array('InArray', false, array(array_keys($users))),
            ),
            'required' => true,
            'label' => 'Directors:',
            'multiOptions' => $users,
            'class' => 'select2 span7',
            'description' => 'Please select one or more directors.',
            'value' => $directors,
        ));

        $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'League Information:',
            'class' => 'ckeditor',
            'description' => 'Enter a quick description of the league (optional)',
            'value' => $this->_leagueData['info'],
        ));

        $this->addElement('text', 'league_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'The name of the location for league.',
            'value' => $this->_leagueData['locations']['league']['location'],
        ));

        $this->addElement('text', 'league_address_street', array(
            'required' => true,
            'label' => 'Street:',
            'class' => 'span5',
            'description' => 'Enter in the Street.',
            'value' => $this->_leagueData['locations']['league']['address_street'],
        ));

        $this->addElement('text', 'league_address_city', array(
            'required' => true,
            'label' => 'City:',
            'class' => 'span2',
            'description' => 'Enter in the City.',
            'value' => $this->_leagueData['locations']['league']['address_city'],
        ));

        $this->addElement('text', 'league_address_state', array(
            'required' => true,
            'label' => 'State:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Select the State',
            'value' => $this->_leagueData['locations']['league']['address_state'],
        ));

        $this->addElement('text', 'league_address_zip', array(
            'required' => true,
            'label' => 'Zipcode:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter in the Zipcode.',
            'value' => $this->_leagueData['locations']['league']['address_zip'],
        ));

        $this->addElement('textarea', 'league_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'class' => 'span6',
            'style' => 'height: 125px;',
            'description' => 'A link to a map of where the fields are.',
            'value' => $this->_leagueData['locations']['league']['map_link'],
        ));

        $this->addElement('text', 'league_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'The name of the league (optional).',
            'value' => (empty($this->_leagueData['locations']['league']['start'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['league']['start'])),
        ));

        $this->addElement('text', 'league_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'The name of the league (optional).',
            'value' => (empty($this->_leagueData['locations']['league']['end'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['league']['end'])),
        ));

        $this->addElement('textarea', 'league_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'class' => 'span6',
            'style' => 'height: 125px;',
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

        $this->addElement('checkbox', 'tournament_ignore', array(
            'required' => false,
            'label' => 'Ignore this',
            'value' => ($this->_leagueData['locations']['tournament']['location'] === null) ? 1 : 0,
        ));

        $this->addElement('text', 'tournament_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'tournament',
            'description' => 'The name of the location for the tournament.',
            'value' => $this->_leagueData['locations']['tournament']['location'],
        ));

        $this->addElement('text', 'tournament_address_street', array(
            'required' => true,
            'label' => 'Street:',
            'class' => 'span5 tournament',
            'description' => 'Enter in the Street.',
            'value' => $this->_leagueData['locations']['tournament']['address_street'],
        ));

        $this->addElement('text', 'tournament_address_city', array(
            'required' => true,
            'label' => 'City:',
            'class' => 'span2 tournament',
            'description' => 'Enter in the City.',
            'value' => $this->_leagueData['locations']['tournament']['address_city'],
        ));

        $this->addElement('text', 'tournament_address_state', array(
            'required' => true,
            'label' => 'State:',
            'class' => 'span1 tournament',
            'style' => 'text-align: center;',
            'description' => 'Select the State',
            'value' => $this->_leagueData['locations']['tournament']['address_state'],
        ));

        $this->addElement('text', 'tournament_address_zip', array(
            'required' => true,
            'label' => 'Zipcode:',
            'class' => 'span1 tournament',
            'style' => 'text-align: center;',
            'description' => 'Enter in the Zipcode.',
            'value' => $this->_leagueData['locations']['tournament']['address_zip'],
        ));

        $this->addElement('textarea', 'tournament_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'class' => 'span6 tournament',
            'style' => 'height: 125px;',
            'description' => 'A link to a map of where the fields are.',
            'value' => $this->_leagueData['locations']['tournament']['map_link'],
        ));

        $this->addElement('text', 'tournament_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'class' => 'span3 datetimepicker tournament',
            'style' => 'text-align: center;',
            'description' => 'Enter the start date/time for the tournament.',
            'value' => (empty($this->_leagueData['locations']['tournament']['start'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['tournament']['start'])),
        ));

        $this->addElement('text', 'tournament_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'class' => 'span3 datetimepicker tournament',
            'style' => 'text-align: center;',
            'description' => 'Enter the end date/time for the tournament.',
            'value' => (empty($this->_leagueData['locations']['tournament']['end'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['tournament']['end'])),
        ));

        $this->addElement('textarea', 'tournament_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'class' => 'span6 tournament',
            'style' => 'height: 125px;',
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

        $this->addElement('checkbox', 'draft_ignore', array(
            'required' => false,
            'label' => 'Ignore this',
            'value' => ($this->_leagueData['locations']['draft']['location'] === null) ? 1 : 0,
        ));

        $this->addElement('text', 'draft_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'draft',
            'description' => 'The name of the location for the draft.',
            'value' => $this->_leagueData['locations']['draft']['location'],
        ));

        $this->addElement('text', 'draft_address_street', array(
            'required' => true,
            'label' => 'Street:',
            'class' => 'span5 draft',
            'description' => 'Enter in the Street.',
            'value' => $this->_leagueData['locations']['draft']['address_street'],
        ));

        $this->addElement('text', 'draft_address_city', array(
            'required' => true,
            'label' => 'City:',
            'class' => 'span2 draft',
            'description' => 'Enter in the City.',
            'value' => $this->_leagueData['locations']['draft']['address_city'],
        ));

        $this->addElement('text', 'draft_address_state', array(
            'required' => true,
            'label' => 'State:',
            'class' => 'span1 draft',
            'style' => 'text-align: center;',
            'description' => 'Select the State',
            'value' => $this->_leagueData['locations']['draft']['address_state'],
        ));

        $this->addElement('text', 'draft_address_zip', array(
            'required' => true,
            'label' => 'Zipcode:',
            'class' => 'span1 draft',
            'style' => 'text-align: center;',
            'description' => 'Enter in the Zipcode.',
            'value' => $this->_leagueData['locations']['draft']['address_zip'],
        ));

        $this->addElement('textarea', 'draft_map_link', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Map Link:',
            'class' => 'span6 draft',
            'style' => 'height: 125px;',
            'description' => 'A link to a map of where the draft is held.',
            'value' => $this->_leagueData['locations']['draft']['map_link'],
        ));

        $this->addElement('text', 'draft_start', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Start:',
            'class' => 'span3 datetimepicker draft',
            'style' => 'text-align: center;',
            'description' => 'Enter the start date/time for the draft.',
            'value' => (empty($this->_leagueData['locations']['draft']['start'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['draft']['start'])),
        ));

        $this->addElement('text', 'draft_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'End:',
            'class' => 'span3 datetimepicker draft',
            'style' => 'text-align: center;',
            'description' => 'Enter the end date/time for the draft.',
            'value' => (empty($this->_leagueData['locations']['draft']['end'])) ? null : date('m/d/Y H:i', strtotime($this->_leagueData['locations']['draft']['end'])),
        ));

        $this->addElement('textarea', 'draft_photo_link', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Photo Link:',
            'class' => 'span6 draft',
            'style' => 'height: 125px;',
            'description' => 'A link to photos.',
            'value' => $this->_leagueData['locations']['draft']['photo_link'],
        ));

        $this->addDisplayGroup(
            array('directors', 'info'),
            'league_edit_form',
            array(
                'legend' => 'League Information',
            )
        );

        $this->addDisplayGroup(
            array('league_name', 'league_address_street', 'league_address_city', 'league_address_state', 'league_address_zip', 'league_map_link', 'league_start', 'league_end', 'league_photo_link'),
            'league_location_edit_form',
            array(
                'legend' => 'League Location',
            )
        );

        $this->addDisplayGroup(
            array('tournament_ignore', 'tournament_name', 'tournament_address_street', 'tournament_address_city', 'tournament_address_state', 'tournament_address_zip', 'tournament_map_link', 'tournament_start', 'tournament_end', 'tournament_photo_link'),
            'tournament_location_edit_form',
            array(
                'legend' => 'Tournament Location',
            )
        );

        $this->addDisplayGroup(
            array('draft_ignore', 'draft_name', 'draft_address_street', 'draft_address_city', 'draft_address_state', 'draft_address_zip', 'draft_map_link', 'draft_start', 'draft_end', 'draft_photo_link'),
            'draft_location_edit_form',
            array(
                'legend' => 'Draft Location',
            )
        );
    }

    private function registration()
    {
        $this->addElement('text', 'registration_begin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Registration Begin:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'Enter a date/time when registration should begin.',
            'value' => $this->_leagueData['registration_begin'],
        ));

        $this->addElement('text', 'registration_end', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Registration End:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center;',
            'description' => 'Enter a date/time when registration should end.',
            'value' => $this->_leagueData['registration_end'],
        ));

        $this->addElement('text', 'cost', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Cost:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'prepend' => '$',
            'description' => 'Enter the amount the league costs to register.',
            'value' => $this->_leagueData['information']['cost'],
        ));

        $this->addElement('textarea', 'paypal_code', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Paypal Button:',
            'class' => 'span7',
            'style' => 'height: 200px;',
            'description' => 'Enter the paypal button HTML code.',
            'value' => $this->_leagueData['information']['paypal_code'],
        ));

        $this->addElement('text', 'male_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Male Players:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter the max # of male players. (optional)',
            'value' => (empty($this->_leagueData['limits']['male_players'])) ? null : $this->_leagueData['limits']['male_players'],
        ));

        $this->addElement('text', 'female_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Female Players:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter the max # of female players. (optional)',
            'value' => (empty($this->_leagueData['limits']['female_players'])) ? null : $this->_leagueData['limits']['female_players'],
        ));

        $this->addElement('text', 'total_players', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Total Players:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter the max # of total players. (overwrites the gender counts)',
            'value' => (empty($this->_leagueData['limits']['total_players'])) ? 0 : $this->_leagueData['limits']['total_players'],
        ));

        $this->addElement('text', 'teams', array(
            'filters' => array('digits'),
            'required' => true,
            'label' => 'Max Teams:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'description' => 'Enter the max # of teams.',
            'value' => $this->_leagueData['limits']['teams'],
        ));

        $this->addDisplayGroup(
            array('registration_begin', 'registration_end', 'cost', 'paypal_code'),
            'registration_edit_form',
            array(
                'legend' => 'League Registration',
            )
        );

        $this->addDisplayGroup(
            array('male_players', 'female_players', 'total_players', 'teams'),
            'limits_edit_form',
            array(
                'legend' => 'League Limits',
            )
        );
    }

    private function description()
    {
        $this->addElement('textarea', 'description', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'League Description:',
            'description' => 'Enter the information you want displayed for the league here.',
            'value' => $this->_leagueData['information']['description'],
        ));

    }

}
