<?php

class Form_LeagueSeasonEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_season;

    public function __construct($season = null)
    {
        $this->_season = $season;
        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'Enter the name of the season.',
            'value' => (empty($this->_season->name)) ? null : $this->_season->name,
        ));

        $this->addElement('text', 'when', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'When:',
            'description' => 'Enter the months this season is (ie June - July)',
            'value' => (empty($this->_season->when)) ? null : $this->_season->when,
        ));

        $this->addElement('textarea', 'information', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Information:',
            'class' => 'ckeditor',
            'description' => 'Enter the information to be displayed on the leagues page.',
            'value' => (empty($this->_season->information)) ? null : $this->_season->information,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_season)) ? 'Create' : 'Save',
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

        $title = (empty($this->_season)) ? 'Add Season' : 'Edit Season';
        $this->addDisplayGroup(
            array('name', 'when', 'information'),
            'season_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'season_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
