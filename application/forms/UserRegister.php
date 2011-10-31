<?php

class Cupa_Form_UserRegister extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $first_name = $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First name:',
        ));
        
        $last_name = $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Last name:',
        ));
        
        $email = $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
        ));
    }
}
