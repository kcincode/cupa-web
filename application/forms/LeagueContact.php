<?php

class Form_LeagueContact extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_leagueId;
    protected $_user;
    protected $_isLeagueDirector;

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
            'class' => 'span5',
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
        ));

        $this->addElement('text', 'subject', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Subject:',
            'value' => '[CUPA Information] More Information',
            'class' => 'span6',
        ));

        $this->addElement('textarea', 'content', array(
           'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Message Content:',
            'class' => 'span6 ckeditor',
            'style' => 'height: 250px;',
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Send Email',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addDisplayGroup(
            array('from', 'to', 'subject', 'content'),
            'contact_edit_form',
            array(
                'legend' => 'Send an email',
            )
        );

        $this->addDisplayGroup(
            array('save'),
            'contact_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
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
