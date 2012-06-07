<?php

class Form_UserActivation extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
        ));

        $this->addElement('password', 'password', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Enter a Password:',
        ));

        $this->addElement('password', 'confirm', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,25)),
            ),
            'required' => true,
            'label' => 'Confirm Password:',
        ));
    }


}

