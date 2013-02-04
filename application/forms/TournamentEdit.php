<?php

class Form_TournamentEdit extends Twitter_Bootstrap_Form_Horizontal
{
    private $_state;
    private $_tournament;
    private $_tournamentInfo;
    private $_id;

    public function __construct($tournamentId, $state, $id = null)
    {

        $tournamentTable = new Model_DbTable_Tournament();
        $tournamentInformationTable = new Model_DbTable_TournamentInformation();

        $this->_tournament = $tournamentTable->find($tournamentId)->current();
        $this->_tournamentInfo = $tournamentInformationTable->fetchInfo($tournamentId);

        $this->_state = $state;
        $this->_id = $id;

        parent::__construct();
    }

    public function init()
    {
        $state = $this->_state;
        if($state && method_exists($this, $state)) {
            $this->$state();
        }

        if(in_array($state, array('update', 'home', 'admin', 'bid', 'team', 'schedule', 'location', 'lodging', 'contact'))) {
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
                'profile_actions',
                array(
                    'disableLoadDefaultDecorators' => true,
                    'decorators' => array('Actions'),
                )
            );
        } else if(in_array($state, array('bidsubmit'))) {
            $this->addElement('button', 'save', array(
                'type' => 'submit',
                'label' => 'Submit Bid',
                'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
                'escape' => false,
                'icon' => 'hdd',
                'whiteIcon' => true,
                'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
            ));

            $this->addDisplayGroup(
                array('save'),
                'profile_actions',
                array(
                    'disableLoadDefaultDecorators' => true,
                    'decorators' => array('Actions'),
                )
            );
        }
    }

    public function home()
    {
        $this->addElement('textarea', 'description', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'class' => 'ckeditor',
            'label' => 'Description:',
            'value' => $this->_tournamentInfo->description,
        ));
    }

    public function update()
    {
        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $tournamentUpdate = $tournamentUpdateTable->find($this->_id)->current();

        $this->addElement('text', 'title', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => 'Title:',
            'class' => 'span5',
            'value' => (isset($tournamentUpdate->title)) ? $tournamentUpdate->title : null,
        ));

        $this->addElement('textarea', 'content', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => 'Content:',
            'class' => 'ckeditor',
            'value' => (isset($tournamentUpdate->content)) ? $tournamentUpdate->content : null,
        ));

        $this->addDisplayGroup(
            array('title', 'content'),
            'tournament_update_form',
            array(
                'legend' => (isset($tournamentUpdate->title)) ? 'Update tournament update' : 'Add tournament update',
            )
        );
    }

    public function bidsubmit()
    {
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'class' => 'span4',
            'label' => 'Name:',
        ));

        $this->addElement('text', 'city', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'City:',
            'class' => 'span3',
        ));

        $states = array(
            '0' => 'Select a State',
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming");

        $stateValues = $states;
        unset($stateValues[0]);

        $this->addElement('select', 'state', array(
            'filters' => array('StringTrim'),
            'multiOptions' => $states,
            'validators' => array(
                array('InArray', false, array(array_keys($stateValues), 'messages' => array('notInArray' => 'Please select a state'))),
            ),
            'required' => true,
            'label' => 'State:',
        ));

        $tournamentDivisionTable = new Model_DbTable_TournamentDivision();
        $divisions = array('0' => 'Select a division');
        foreach($tournamentDivisionTable->fetchDivisions() as $division) {
            $divisions[$division->id] = ucwords($division->name);
        }

        $divisionValues = $divisions;
        unset($divisionValues[0]);

        $this->addElement('select', 'division', array(
            'filters' => array('StringTrim'),
            'multiOptions' => $divisions,
            'validators' => array(
                array('InArray', false, array(array_keys($divisionValues), 'messages' => array('notInArray' => 'Please select a division'))),
            ),
            'required' => true,
            'label' => 'Division:',
        ));

        $this->addElement('text', 'contact_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'class' => 'span5',
        ));

        $this->addElement('text', 'contact_phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/')),
            ),
            'label' => 'Phone:',
            'class' => 'span2',
            'style' => 'text-align: center',
        ));
        $this->getElement('contact_phone')->addErrorMessage('Invalid phone number ###-###-####.');

        $this->addElement('text', 'contact_email', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('EmailAddress'),
            ),
            'label' => 'Email:',
            'class' => 'span5',
        ));
        $this->getElement('contact_email')->addErrorMessage('Invalid email address.');

        $this->addElement('textarea', 'comments', array(
            'filters' => array('StringTrim'),
            'class' => 'span5',
            'style' => 'height: 125px;',
            'label' => 'Comments',
        ));

        $this->addDisplayGroup(
            array('name', 'city', 'state', 'division'),
            'tournament_bid_team_form',
            array(
                'legend' => 'Team Information',
            )
        );

        $this->addDisplayGroup(
            array('contact_name', 'contact_phone', 'contact_email'),
            'tournament_bid_contact_form',
            array(
                'legend' => 'Team Contact Information',
            )
        );

        $this->addDisplayGroup(
            array('comments'),
            'tournament_bid_other_form',
            array(
                'legend' => 'Other',
            )
        );
    }

    private function bid()
    {
        $this->addElement('text', 'cost', array(
            'required' => true,
            'label' => 'Cost:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'prepend' => '$',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->cost)) ? $this->_tournamentInfo->cost : null,
        ));

        $this->addElement('text', 'bid_due', array(
            'required' => true,
            'label' => 'Bid Due:',
            'class' => 'datetimepicker span3',
            'style' => 'text-align: center;',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->bid_due)) ? date('m/d/Y H:i', strtotime($this->_tournamentInfo->bid_due)) : null,
        ));

        $this->addElement('textarea', 'paypal', array(
            'required' => true,
            'label' => 'Paypal Button HTML:',
            'filters' => array('StringTrim'),
            'class' => 'span7',
            'style' => 'height: 200px;',
            'value' => (isset($this->_tournamentInfo->paypal)) ? $this->_tournamentInfo->paypal : null,
        ));

        $this->addElement('textarea', 'mail_payment', array(
            'required' => true,
            'label' => 'Mail Payments To:',
            'filters' => array('StringTrim'),
            'class' => 'span4',
            'style' => 'height: 100px;',
            'value' => (isset($this->_tournamentInfo->mail_payment)) ? $this->_tournamentInfo->mail_payment : null,
        ));

        $this->addDisplayGroup(
            array('cost', 'bid_due', 'paypal', 'mail_payment'),
            'tournament_bid_form',
            array(
                'legend' => 'Update bid information',
            )
        );
    }

    public function team()
    {
        $tournamentTeamTable = new Model_DbTable_TournamentTeam();

        $this->bidsubmit();

        $team = null;
        if($this->_id) {
            $team = $tournamentTeamTable->find($this->_id)->current();
            foreach($team as $key => $value) {
                $element = $this->getElement($key);
                if($element) {
                    $element->setValue($value);
                }
            }
        }

        $this->removeElement('bid');
        $this->removeElement('comments');

        $this->addElement('checkbox', 'paid', array(
            'required' => true,
            'label' => 'Paid?',
            'value' => (isset($team->paid)) ? $team->paid : null,
        ));

        $this->addElement('checkbox', 'accepted', array(
            'required' => true,
            'label' => 'Accepted?',
            'value' => (isset($team->accepted)) ? $team->accepted : null,
        ));
    }

    private function admin()
    {
        $this->addElement('text', 'display_name', array(
            'required' => true,
            'label' => 'Display name:',
            'filters' => array('StringTrim'),
            'class' => 'span4',
            'description' => 'Enter the name you would like displayed in the header',
            'value' => (isset($this->_tournament->display_name)) ? $this->_tournament->display_name : null,
        ));

        $this->addElement('text', 'start', array(
            'required' => true,
            'label' => 'Start Date:',
            'description' => 'Tournament start date',
            'class' => 'span2 datepicker',
            'style' => 'text-align: center',
            'value' => (isset($this->_tournamentInfo->start)) ? date('m/d/Y', strtotime($this->_tournamentInfo->start)) : null,
        ));

        $this->addElement('text', 'end', array(
            'required' => true,
            'label' => 'End Date:',
            'description' => 'Tournament End date',
            'class' => 'span2 datepicker',
            'style' => 'text-align: center',
            'value' => (isset($this->_tournamentInfo->end)) ? date('m/d/Y', strtotime($this->_tournamentInfo->end)) : null,
        ));

        $this->addElement('file', 'image', array(
            'label' => 'Header Image:',
            'required' => false,
            'validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'jpg,png,gif'),
            ),
            'valueDisabled' => true,
            'description' => '(Not Required)',
        ));

        $this->addElement('checkbox', 'use_bid', array(
            'required' => true,
            'label' => 'Use Bid System:',
            'description' => 'Check this box to use the build it bid system.',
            'value' => (isset($this->_tournament->use_bid)) ? $this->_tournament->use_bid : null,
        ));

        $this->addElement('checkbox', 'is_visible', array(
            'required' => true,
            'label' => 'Is Visible:',
            'description' => 'Check this box to make the tournament public.',
            'value' => (isset($this->_tournament->is_visible)) ? $this->_tournament->is_visible : null,
        ));

        $this->addDisplayGroup(
            array('display_name', 'start', 'end', 'image', 'use_bid', 'is_visible'),
            'tournament_update_form',
            array(
                'legend' => 'Update tournament information',
            )
        );
    }

    private function contact()
    {
        $tournamentMemberTable = new Model_DbTable_TournamentMember();
        $member = $tournamentMemberTable->find($this->_id)->current();

        $userTable = new Model_DbTable_User();
        $users = array('0' => 'Select User');
        foreach($userTable->fetchAllUsers() as $user) {
            $users[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $this->addElement('select', 'user_id', array(
            'filters' => array('StringTrim'),
            'multiOptions' => $users,
            'validators' => array(
                array('InArray', false, array(array_keys($users), 'messages' => array('notInArray' => 'Please select a user'))),
            ),
            'required' => false,
            'class' => 'select2 span3',
            'label' => 'Select a User:',
            'value' => (isset($member->user_id)) ? $member->user_id : 0,
        ));

        $this->addElement('text', 'name', array(
            'required' => false,
            'label' => 'Use Name:',
            'filters' => array('StringTrim'),
            'description' => 'Enter an alternate name to use as member.',
            'value' => (isset($member->name)) ? $member->name : null,
        ));

        $this->addElement('text', 'email', array(
            'required' => false,
            'label' => 'Use Email:',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress'),
            ),
            'description' => 'Enter an alternate email to use as member.',
            'value' => (isset($member->email)) ? $member->email : null,
        ));

        $this->addElement('text', 'type', array(
            'required' => true,
            'label' => 'Title:',
            'filters' => array('StringTrim'),
            'description' => 'Use `director` for admin access',
            'value' => (isset($member->type)) ? $member->type : null,
        ));


        $this->addElement('text', 'weight', array(
            'required' => true,
            'label' => 'Weight:',
            'class' => 'span1',
            'style' => 'text-align: center;',
            'filters' => array('StringTrim'),
            'description' => 'Lower numbers first',
            'value' => (isset($member->weight)) ? $member->weight : $tournamentMemberTable->getHighestWeight($this->_tournament->id),
        ));

        $this->addDisplayGroup(
            array('user_id', 'name', 'email', 'type', 'weight'),
            'tournament_contact_form',
            array(
                'legend' => 'Update tournament contact',
            )
        );
    }

    private function schedule()
    {
        $this->addElement('textarea', 'scorereporter_link', array(
            'required' => false,
            'description' => 'Leave blank to remove the link',
            'label' => 'Score Reporter Link:',
            'class' => 'span6',
            'style' => 'height: 50px;',
            'value' => (isset($this->_tournamentInfo->scorereporter_link)) ? $this->_tournamentInfo->scorereporter_link : null,
        ));

        $this->addElement('textarea', 'schedule_text', array(
            'required' => true,
            'description' => 'Enter the schedule information you want displayed on the page.',
            'label' => 'Schedule Information:',
            'class' => 'ckeditor',
            'value' => (isset($this->_tournamentInfo->schedule_text)) ? $this->_tournamentInfo->schedule_text : null,
        ));

        $this->addDisplayGroup(
            array('scorereporter_link', 'schedule_text'),
            'tournament_schedule_form',
            array(
                'legend' => 'Update tournament schedule',
            )
        );
    }

    public function location()
    {
        $this->addElement('text', 'location', array(
            'required' => true,
            'label' => 'Title:',
            'filters' => array('StringTrim'),
            'description' => 'Enter the name of the fields',
            'value' => (isset($this->_tournamentInfo->location)) ? $this->_tournamentInfo->location : null,
        ));

        $this->addElement('text', 'location_street', array(
            'required' => true,
            'label' => 'Street:',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->location_street)) ? $this->_tournamentInfo->location_street : null,
        ));

        $this->addElement('text', 'location_city', array(
            'required' => true,
            'label' => 'City:',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->location_city)) ? $this->_tournamentInfo->location_city : null,
        ));

        $this->addElement('text', 'location_state', array(
            'required' => true,
            'label' => 'State:',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->location_state)) ? $this->_tournamentInfo->location_state : null,
        ));

        $this->addElement('text', 'location_zip', array(
            'required' => true,
            'label' => 'Zipcode:',
            'filters' => array('StringTrim'),
            'value' => (isset($this->_tournamentInfo->location_zip)) ? $this->_tournamentInfo->location_zip : null,
        ));

        $this->addDisplayGroup(
            array('location', 'location_street', 'location_city', 'location_state', 'location_zip'),
            'tournament_location_form',
            array(
                'legend' => 'Update tournament location',
            )
        );
    }

    private function lodging()
    {
        $tournamentLodgingTable = new Model_DbTable_TournamentLodging();
        $lodging = $tournamentLodgingTable->find($this->_id)->current();

        $this->addElement('text', 'title', array(
            'required' => true,
            'label' => 'Title:',
            'filters' => array('StringTrim'),
            'description' => 'Enter the name of the lodging',
            'value' => (isset($lodging->title)) ? $lodging->title : null,
        ));

        $this->addElement('text', 'street', array(
            'required' => true,
            'label' => 'Street:',
            'filters' => array('StringTrim'),
            'value' => (isset($lodging->street)) ? $lodging->street : null,
        ));

        $this->addElement('text', 'city', array(
            'required' => true,
            'label' => 'City:',
            'filters' => array('StringTrim'),
            'value' => (isset($lodging->city)) ? $lodging->city : null,
        ));

        $states = array(
            '0' => 'Select a State',
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming");

        $stateValues = $states;
        unset($stateValues[0]);

        $this->addElement('select', 'state', array(
            'filters' => array('StringTrim'),
            'multiOptions' => $states,
            'validators' => array(
                array('InArray', false, array(array_keys($stateValues), 'messages' => array('notInArray' => 'Please select a state'))),
            ),
            'required' => true,
            'label' => 'State:',
            'value' => (isset($lodging->state)) ? $lodging->state : null,
        ));

        $this->addElement('text', 'zip', array(
            'required' => true,
            'label' => 'Zipcode:',
            'filters' => array('StringTrim'),
            'value' => (isset($lodging->zip)) ? $lodging->zip : null,
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/')),
            ),
            'label' => 'Phone:',
            'class' => 'span2',
            'style' => 'text-align: center',
            'value' => (isset($lodging->phone)) ? $lodging->phone : null,
        ));
        $this->getElement('phone')->addErrorMessage('Invalid phone number ###-###-####.');


        $this->addElement('textarea', 'link', array(
            'required' => false,
            'label' => 'URL Link:',
            'filters' => array('StringTrim'),
            'class' => 'span6',
            'style' => 'height: 125px;',
            'value' => (isset($lodging->link)) ? $lodging->link : null,
        ));
        $this->addElement('textarea', 'other', array(
            'required' => false,
            'label' => 'Other infomation:',
            'filters' => array('StringTrim'),
            'class' => 'span6',
            'style' => 'height: 125px;',
            'value' => (isset($lodging->other)) ? $lodging->other : null,
        ));

        $this->addDisplayGroup(
            array('title', 'street', 'city', 'state', 'zip', 'phone', 'link', 'other'),
            'tournament_lodging_form',
            array(
                'legend' => 'Update tournament lodging',
            )
        );
    }
}
