<?php

class Form_LeagueQuestion extends Zend_Form
{
    private $_question;
    private $_answer;
    private $_disabled;
    
    public function __construct($leagueId, $userId = null, $disabled = false)
    {
        $questionTable = new Model_DbTable_LeagueQuestion();
        $this->_question = $questionTable->find($questionId)->current();
        $this->_answer = $answer;
        $this->_disabled = ($disabled === true) ? 'disabled' : '';
        parent::__construct();
    }
    
    public function init()
    {
        
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        switch($this->_question->type) {
            case 'text':
                $this->addElement('text', $this->question->name, array(
                    'filters' => array('StringTrim'),
                    'required' => ($this->_question->required == 1) ? true : false,
                    'label' => $this->_question->title,
                    'value' => $this->_answer,
                    'disabled' => $this->_disabled,
                ));
                break;
            
        }
   /*     
        $email = $this->addElement('text', 'from', array(
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
        
        $to = $this->addElement('select', 'to', array(
            'validators' => array(
                array('InArray', false, array(array_keys($toSelection))),
            ),
            'required' => true,
            'label' => 'To:',
            'multiOptions' => $toSelection,
        ));
        
        $subject = $this->addElement('text', 'subject', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Subject:',
            'value' => '[CUPA Information] More Information',
        ));
        
        $body = $this->addElement('textarea', 'content', array(
           'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Message Content:',
        ));
        
        $send = $this->addElement('submit', 'send', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Send Email',
        ));
        */
    }
    
}
