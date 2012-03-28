<?php

class Form_TournamentEdit extends Zend_Form
{
    private $_state;
    private $_tournament;
    private $_tournamentInfo;
    private $_id;

    public function __construct($tournamentId, $state, $id = null)
    {
        
        $tournamentTable = new Model_DbTable_Tournament();
        $tournamentInformationTable = new Model_DbTable_TournamentInformation();
        
        $this->_tournament = $tournamentTable->find($tournamentId)->current();
        $this->_tournamentInfo = $tournamentInformationTable->fetchInfo($tournamentId);
        
        $this->_state = $state;
        $this->_id = $id;
        
        parent::__construct();
    }

    public function init()
    {
        $state = $this->_state;
        if($state && method_exists($this, $state)) {
            $this->$state();
        }
    }
    
    public function home()
    {
        $this->addElement('textarea', 'description', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => 'Enter the tournament description:',
            'value' => $this->_tournamentInfo->description,
        ));
    }
    
    public function update()
    {
        $tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
        $tournamentUpdate = $tournamentUpdateTable->find($this->_id)->current();
        
        $this->addElement('text', 'title', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => 'Enter the update title:',
            'value' => (isset($tournamentUpdate->title)) ? $tournamentUpdate->title : null,
        ));

        $this->addElement('textarea', 'content', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'label' => 'Enter the update text:',
            'value' => (isset($tournamentUpdate->content)) ? $tournamentUpdate->content : null,
        ));
    }
    
}
