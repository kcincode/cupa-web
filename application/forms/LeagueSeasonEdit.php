<?php

class Form_LeagueSeasonEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'Enter the name of the season.',
        ));
        
        $this->addElement('text', 'when', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'When:',
            'description' => 'Enter the months this season is (ie June - July)',
        ));
                
        $this->addElement('textarea', 'information', array(
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

