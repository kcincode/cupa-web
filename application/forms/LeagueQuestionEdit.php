<?php

class Form_LeagueQuestionEdit extends Zend_Form
{
    private $_question;
    
    public function __construct($question = null)
    {
        $this->_question = $question;
        parent::__construct();
    }
    
    public function init()
    {
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'description' => 'The unique name for the question (not shown).',
            'value' => (isset($this->_question)) ? $this->_question->name : null,
        ));
        
        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
            'description' => 'The question title, show to the user.',
            'value' => (isset($this->_question)) ? $this->_question->title : null,
        ));
        
        $types = array(
            'multiple' => 'Multiple Choice',
            'text' => 'Single Answer',
            'boolean' => 'Yes / No',
            'textarea' => 'Long Answer',
        );
        
        $this->addElement('select', 'type', array(
            'validators' => array(
                array('InArray', false, array(array_keys($types))),
            ),
            'required' => true,
            'label' => 'Type:',
            'description' => 'Select the type of question you would like this to be.',
            'multiOptions' => $types,
            'value' => (isset($this->_question)) ? $this->_question->type : null,
        ));
        
        $this->addElement('textarea', 'answers', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Answers',
            'description' => 'A list of the answers with the format <value>::<text> for each line.',
            'value' => (!empty($this->_question->answers)) ? $this->formatAnswers($this->_question->answers) : null,
        ));
        
    }
    
    private function formatAnswers($answers)
    {
        $data = Zend_Json::decode($answers);
        
        $string = '';
        foreach($data as $key => $value) {
            $string .= $key . '::' . $value . "\n";
        }
        
        return $string;
    }
}
