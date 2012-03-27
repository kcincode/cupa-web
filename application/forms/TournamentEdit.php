<?php

class Form_TournamentEdit extends Zend_Form
{
    private $_state;
    private $_tournament;
    private $_tournamentInfo;

    public function __construct($tournamentId, $state)
    {
        
        $tournamentTable = new Model_DbTable_Tournament();
        $tournamentInformationTable = new Model_DbTable_TournamentInformation();
        
        $this->_tournament = $tournamentTable->find($tournamentId)->current();
        $this->_tournamentInfo = $tournamentInformationTable->fetchInfo($tournamentId);
        
        $this->_state = $state;
        
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
    
}
