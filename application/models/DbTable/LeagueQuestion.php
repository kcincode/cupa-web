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
                $data[] = $question->toArray();
            }
        }
        
        return $data;
    }
}