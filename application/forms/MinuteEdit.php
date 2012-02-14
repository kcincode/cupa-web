<?php

class Form_MinuteEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $when = $this->addElement('text', 'when', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Date:',
        ));
        
        $location = $this->addElement('text', 'location', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Location:',
        ));

        $pdf = $this->addElement('file', 'pdf', array(
            'required' => false,
            'label' => 'PDF File:',
        ));
        
        $is_visible = $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
        ));        
    }
    
    public function loadFromMinute($minute)
    {
        $this->getElement('when')->setValue($minute->when);
        $this->getElement('location')->setValue($minute->location);
        $this->getElement('pdf')->setValue($minute->pdf);
        $this->getElement('is_visible')->setValue($minute->is_visible);
    }
}

