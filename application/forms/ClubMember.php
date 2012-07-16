<?php

class Form_ClubMember extends Zend_Form
{
	protected $_clubId;
	protected $_year;

	public function __construct($clubId, $year)
	{
		$this->_clubId = $clubId;
		$this->_year = $year;

		parent::__construct();
	}

    public function init()
    {
    	$userTable = new Model_DbTable_User();
    	$users = array(0 => 'Select a User');
    	foreach($userTable->fetchAllUsers(true, true) as $user) {
    		$users[$user->id] = $user->first_name . ' ' . $user->last_name;
    	}

        $this->addElement('hidden', 'club_id', array(
            'value' => $this->_clubId,
            'required' => true,
        ));

        $this->addElement('hidden', 'year', array(
            'value' => $this->_year,
            'required' => true,
        ));

    	$this->addElement('select', 'user_id', array(
    		'label' => 'Select User:',
			'multiOptions' => $users,
            'validators' => array(
                array('GreaterThan', true, array('min' => 0))
            ),
			'class' => 'chosen',
            'required' => true,
    	));
        $this->getElement('user_id')->addErrorMessage('Please select a user.');

    	$this->addElement('submit', 'add', array(
    		'label' => 'Add Member',
    		'class' => 'button',
    	));
   	}
}
