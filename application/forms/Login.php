<?php

class Cupa_Form_UserLogin extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
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
        
        $login = $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Login',
        ));
    }


}
