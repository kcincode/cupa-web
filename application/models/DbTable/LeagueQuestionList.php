<?php

class Model_DbTable_LeagueQuestionList extends Zend_Db_Table
{
    protected $_name = 'league_question_list';
    protected $_primary = 'id';

    public function updateQuestionList($leagueId, $questionId, $required, $weight)
    {

        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('league_question_id = ?', $questionId);

        $result = $this->fetchRow($select);
        if(!$result) {
            $this->insert(array(
                'league_id' => $leagueId,
                'league_question_id' => $questionId,
                'required' => $required,
                'weight' => $weight,
            ));
        } else {
            $result->required = $required;
            $result->weight = $weight;
            $result->save();
        }
    }


    public function addQuestionToLeague($leagueId, $questionId, $required, $weight = null)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('league_question_id = ?', $questionId);

        $result = $this->fetchRow($select);

        if($result) {
            $result->required = $required;
            $result->weight = (empty($weight) && !is_numeric($result->weight)) ? $this->getHighestWeight($leagueId) : $weight;
            $result->save();
        } else {
            $this->insert(array(
                'league_id' => $leagueId,
                'league_question_id' => $questionId,
                'required' => $required,
                'weight' => (empty($weight)) ? $this->getHighestWeight($leagueId) : $weight,
            ));
        }

    }

    public function getHighestWeight($leagueId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->order('weight DESC');

        $result = $this->fetchRow($select);

        if(count($result)) {
            return $result->weight + 1;
        } else {
            return 0;
        }

    }

    public function removeQuestionFromLeague($leagueId, $questionId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('league_question_id = ?', $questionId);

        $result = $this->fetchRow($select);
        if($result) {
            $result->delete();
        }
    }

    public function toggleRequired($leagueId, $questionId)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('league_question_id = ?', $questionId);

        $result = $this->fetchRow($select);
        if($result) {
            $result->required = ($result->required == 1) ? 0 : 1;
            $result->save();
        }
    }

    public function move($leagueId, $questionId, $direction)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->order('weight ASC');

        $results = $this->fetchAll($select);

        foreach($results as $i => $question) {
            if($question['league_question_id'] == $questionId) {
                $targetQuestion = ($direction == 'up') ? $results[$i - 1] : $results[$i + 1];
                //Zend_Debug::dump($targetQuestion->weight . ' - ' . $question->weight);

                $weight = $question->weight;
                $question->weight = $targetQuestion->weight;
                $targetQuestion->weight = $weight;

                $question->save();
                $targetQuestion->save();
                //Zend_Debug::dump($targetQuestion->weight . ' - ' . $question->weight);
                break;
            }

        }
    }
}
