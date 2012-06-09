<?php

class Form_UserManage extends Zend_Form
{
    private $_user;

    public function __construct($userObject)
    {
        if(is_array($userObject)) {
            $this->_user = $userObject;
        } else if(is_object($userObject)) {
            $this->_user = $userObject->toArray();
        } else {
            $this->_user = array();
        }

        parent::__construct();
    }

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'username', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                 array('StringLength', true, array('min' => 4, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last username, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Username:',
            'value' => (empty($this->_user['username'])) ? null : $this->_user['username'],
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            ),
            'required' => true,
            'label' => 'Email Address:',
            'value' => (empty($this->_user['email'])) ? null : $this->_user['email'],
        ));

        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'value' => (empty($this->_user['first_name'])) ? null : $this->_user['first_name'],
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'value' => (empty($this->_user['last_name'])) ? null : $this->_user['last_name'],
        ));

        $this->addElement('checkbox', 'is_active', array(
            'label' => 'Is Active:',
            'value' => (empty($this->_user['is_active'])) ? null : $this->_user['is_active'],
        ));

        $this->addElement('hidden', 'user_id', array(
            'value' => (empty($this->_user['id'])) ? null : $this->_user['id'],
        ));


        $this->addElement('submit', 'edit', array(
            'label' => 'Update User Information',
            'class' => 'button',
        ));

    }
}
