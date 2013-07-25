<?php

class My_View_Helper_DisplayLeagueGameOutcome extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function displayLeagueGameOutcome($gameData, $type)
    {
        $data = array();
        if($type == 'home') {
            $data = array(
                0 => $gameData['home_score'],
                1 => $gameData['away_score'],
            );
        } else if($type == 'away') {
            $data = array(
                0 => $gameData['away_score'],
                1 => $gameData['home_score'],
            );
        }

        if($data[0] == $data[1]) {
            return '';
        }

        return ($data[0] > $data[1]) ? 'text-success' : 'text-error';
    }
}
