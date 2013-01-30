<?php

class Form_Filter extends Twitter_Bootstrap_Form_Inline
{
    protected $_string;

    public function __construct($string = null)
    {
        $this->_string = $string;

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'filter', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Filter',
            'class' => 'span3',
            'value' => (empty($this->_string)) ? null : $this->_string,
        ));

        $this->addElement('button', 'search', array(
            'type' => 'submit',
            'label' => 'Filter',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'search',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));
        $this->addElement('button', 'reset', array(
            'type' => 'submit',
            'label' => 'Reset',
            'escape' => false,
            'icon' => 'remove',
            'whiteIcon' => false,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));
    }
}
