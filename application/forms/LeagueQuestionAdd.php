<?php

class Form_LeagueQuestionAdd extends Twitter_Bootstrap_Form_Inline
{
    protected $_leagueId;
    protected $_questions;

    public function __construct($leagueId)
    {
        $this->_leagueId = $leagueId;

        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $this->_questions = $leagueQuestionTable->fetchAllRemainingQuestionsFromLeague($leagueId);

        parent::__construct();
    }

    public function init()
    {
        $questionList = array();
        $questionList[0] = 'Add Question';
        foreach($this->_questions as $question) {
            $questionList[$question['id']] = $question['name'];
        }

        $this->addElement('select' , 'question', array(
            'required' => true,
            'validators' => array(
                array('GreaterThan', false, array('min' => 0)),
            ),
            'multiOptions' => $questionList,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Add',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'plus',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));
    }
}
