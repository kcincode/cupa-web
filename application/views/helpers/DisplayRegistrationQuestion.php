<?php

class My_View_Helper_DisplayRegistrationQuestion extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function DisplayRegistrationQuestion($questionId, $answer = null, $disabled = false)
    {
        $leagueQuestionTable = new Model_DbTable_LeagueQuestion();
        $question = $leagueQuestionTable->find($questionId)->current();
        
        $disabledText = '';
        if($disabled) {
            $disabledText = ' disabled="disabled"';
        }
        
        $output = '<p class="question">' . $question->title . "</p>\n";
        switch($question->type) {
            case 'text':
                $output .= '<div class="answer"><input type="hidden" name="question[]" value="' . $question->name . '"/><input type="' . $question->type . '" name="' . $question->name . '" value="' . $answer . '"' . $disabledText . '/></div>';
                $output .= '<p class="description">Enter text for the answer.</p>';
                break;
            case 'multiple':
                $output .= '<p class="description">Select ONE answer that BEST describes you.</p>';
                $output .= '<div class="answer"><input type="hidden" name="question[]" value="' . $question->name . '"/>';
                foreach(Zend_Json::decode($question->answers) as $key => $value) {
                    $dots = '';
                    if(strlen($value) > 100) {
                        $dots = '...';
                    }
                    $output .= '<input type="radio" name="' . $question->name . '[]"' . $disabledText . '/> &nbsp; ' . substr($value, 0, 100) . $dots . '<br/>';
                }
                $output .= "</div>\n";
                break;
            case 'boolean':
                $output .= '<p class="description">Select Yes or No.</p>';
                $output .= '<div class="answer"><input type="hidden" name="question[]" value="' . $question->name . '"/>';
                $output .= '<input type="radio" name="' . $question->name . '[]"' . $disabledText . '/> &nbsp; Yes &nbsp; &nbsp; &nbsp;';
                $output .= '<input type="radio" name="' . $question->name . '[]"' . $disabledText . '/> &nbsp; No';
                $output .= "</div>\n";
                break;
            case 'textarea':
                $output .= '<div class="answer"><input type="hidden" name="question[]" value="' . $question->name . '"/><textarea name="' . $question->name . '" value="' . $answer . '"' . $disabledText . '></textarea></div>';
                $output .= '<p class="description">Enter text for the answer.</p>';
                break;
        }
                
        return $output;
    }
}
