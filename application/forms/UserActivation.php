<?php

class Cupa_Form_UserActivation extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $email = $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
        ));

        $password = $this->addElement('password', 'password', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,20)),
            ),
            'required' => true,
            'label' => 'Enter a Password:',
        ));
        
        $confirm = $this->addElement('password', 'confirm', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,20)),
            ),
            'required' => true,
            'label' => 'Confirm Password:',
        ));
    }


}

