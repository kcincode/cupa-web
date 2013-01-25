<?php

class Form_TournamentCreate extends Twitter_Bootstrap_Form_Horizontal
{

    public function init()
    {
        $tournamentTable = new Model_DbTable_Tournament();
        $tournaments = array();
        $tournaments[0] = 'Select a tournament';
        $tournaments['new'] = 'Create new tournament';
        foreach($tournamentTable->fetchDistinctTournaments() as $tournament) {
            $tournaments[$tournament->id] = $tournament->name;
        }

        $this->addElement('select', 'name', array(
            'label' => 'Tournament',
            'required' => false,
            'multiOptions' => $tournaments,
            'validators' => array(
                array('GreaterThan', true, array('min' => '0', 'messages' => array('notGreaterThan' => 'Please select or create new .'))),
            ),
            'description' => 'Select the tournament to create',
        ));

        $this->addElement('text', 'new_name', array(
            'label' => 'New Name:',
            'required' => true,
            'description' => 'Only required if you selected "new tournament above"',
        ));

        $years = array_combine(range(date('Y') + 1, 2010), range(date('Y') + 1, 2010));
        $this->addElement('select', 'year', array(
            'required' => true,
            'validators' => array(
                array('GreaterThan', true, array('min' => '2009', 'messages' => array('notGreaterThan' => 'Please enter a valid year.'))),
            ),
            'multiOptions' => $years,
            'label' => 'Year:',
            'description' => 'Select the year.',
            'value' => date('Y'),
        ));

        $directors = array();
        $userTable = new Model_DbTable_User();
        foreach($userTable->fetchAllUsers() as $user) {
            $directors[$user->id] = $user->first_name . ' ' . $user->last_name;
        }

        $this->addElement('multiselect', 'directors', array(
            'validators' => array(
                array('InArray', false, array(array_keys($directors))),
            ),
            'required' => true,
            'label' => 'Directors:',
            'class' => 'select2 span6',
            'multiOptions' => $directors,
            'data-placeholder' => 'Select Directors',
        ));

        $this->addElement('button', 'create', array(
            'type' => 'submit',
            'label' => 'Create',
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

        $this->addDisplayGroup(
            array('name', 'new_name', 'year', 'directors'),
            'pickup_edit_form',
            array(
                'legend' => 'Create Tournament',
            )
        );

        $this->addDisplayGroup(
            array('create', 'cancel'),
            'pickup_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );

    }
}
