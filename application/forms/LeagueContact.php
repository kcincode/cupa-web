<?php

class Form_LeagueContact extends Zend_Form
{
    private $_leagueId;
    private $_user;
    private $_isLeagueDirector;

    public function __construct($leagueId, $user, $isLeagueDirector)
    {
        $this->_leagueId = $leagueId;
        $this->_user = $user;
        $this->_isLeagueDirector = $isLeagueDirector;

        parent::__construct();
    }

    public function init()
    {

        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $this->addElement('text', 'from', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress', true),
            ),
            'required' => true,
            'label' => 'From:',
        ));

        if($this->_user) {
            $this->getElement('from')->setValue($this->_user->email);
        }

        $toSelection = $this->getContacts();

        $this->addElement('multiCheckbox', 'to', array(
            'validators' => array(
                array('InArray', false, array(array_keys($toSelection))),
            ),
            'required' => true,
            'label' => 'To:',
            'multiOptions' => $toSelection,
            'separator' => '&nbsp;&nbsp;',
        ));

        $this->addElement('text', 'subject', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Subject:',
            'value' => '[CUPA Information] More Information',
        ));

        $this->addElement('textarea', 'content', array(
           'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Message Content:',
        ));

        $this->addElement('submit', 'send', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Send Email',
        ));

    }

    /**
     * Handle getting the contacts from the database
     *
     * @return array
     */
    private function getContacts()
    {
        $data = array();
        $leagueMemberTable = new Model_DbTable_LeagueMember();

        foreach($leagueMemberTable->fetchAllEmails($this->_leagueId, $this->_user, $this->_isLeagueDirector) as $key => $emails) {
            $data[$key] = ucwords(str_replace('-', ' ', $key));
        }

        return $data;
    }
}
