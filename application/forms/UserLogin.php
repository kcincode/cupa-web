<?php

class Form_UserLogin extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $username = $this->addElement('text', 'username', array(
           'filters' => array('StringTrim', 'StringToLower'),
            'required' => true,
            'label' => 'Username or Email:',
        ));
        
        $password = $this->addElement('password', 'password', array(
           'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(6,20)),
            ),
            'required' => true,
            'label' => 'Password:',
        ));
    }
}
