<?php

class Cupa_Model_DbTable_LeagueQuestion extends Zend_Db_Table
{
    protected $_name = 'league_question';
    protected $_primary = 'id';
    
    public function fetchQuestion($name)
    {
        $select = $this->select()
                       ->where('name = ?', $name);
        
        return $this->fetchRow($select);
    }
    
    public function fetchAllQuestionsFromLeague($leagueId)
    {
        $leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();
        
        $select = $leagueQuestionListTable->select()
                                          ->where('league_id = ?', $leagueId)
                                          ->order('weight ASC');

        $data = array();
        foreach($leagueQuestionListTable->fetchAll($select) as $row) {
            $question = $this->find($row->league_question_id)->current();
            if($question) {
                $array = $question->toArray();
                $array['weight'] = $row->weight;
                $array['required'] = $row->required;
                $data[] = $array;
            }
        }
        
        return $data;
    }
    
    public function fetchAllRemainingQuestions($currentQuestions, $order = 'name')
    {
        $select = $this->select()
                       ->order($order);
        foreach($currentQuestions as $q) {
            $select->where('name <> ?', $q['name']);
        }

        return $this->fetchAll($select);
    }
    
    public function createQuestion($name, $title, $type, $answers = null)
    {
        $question = $this->fetchQuestion($name);
        
        if(!$question) {
            return $this->insert(array(
                'name' => $name,
                'title' => $title,
                'type' => $type,
                'answers' => (empty($answers)) ? null : $answers,
            ));
        }
        
        return null;
    }
}