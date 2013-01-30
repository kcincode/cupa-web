<?php

class Form_UserManage extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_user;
    protected $_filter;
    protected $_page;

    public function __construct($userObject, $page = null, $filter = null)
    {
        if(is_array($userObject)) {
            $this->_user = $userObject;
        } else if(is_object($userObject)) {
            $this->_user = $userObject->toArray();
        } else {
            $this->_user = array();
        }
        
        $this->_page = (empty($page)) ? 1 : $page;
        $this->_filter = $filter;

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
                array('Db_NoRecordExists', false, array('table' => 'user', 'field' => 'email', 'exclude' => array('field' => 'id', 'value' => $this->_user['id']), 'messages' => array('recordFound' => 'Email address is already used.')))
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
        
        $this->addElement('hidden', 'filter', array(
            'value' => $this->_filter,
        ));
        
        $this->addElement('hidden', 'page', array(
            'value' => $this->_page,
        ));


        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Save',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('submit', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
            'escape' => false,
        ));

        $this->addDisplayGroup(
            array('username', 'email', 'first_name', 'last_name', 'is_active', 'usre_id', 'filter', 'page'),
            'user_edit_form',
            array(
                'legend' => 'Edit User',
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'user_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );

    }
}
