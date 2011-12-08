<?php

class Cupa_Form_LeagueSeasonEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'Enter the name of the season.',
        ));
        
        $when = $this->addElement('text', 'when', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'When:',
            'description' => 'Enter the months this season is (ie June - July)',
        ));
                
        $infomation = $this->addElement('textarea', 'information', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Information:',
            'description' => 'Enter the information to be displayed on the leagues page.',
        ));
    }
    
    public function loadFromSeason($season)
    {
        $this->getElement('name')->setValue($season->name);
        $this->getElement('when')->setValue($season->when);
        $this->getElement('information')->setValue($season->information);        
    }


}

