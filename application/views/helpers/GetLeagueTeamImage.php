<?php

class My_View_Helper_GetLeagueTeamImage extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }    
    
    public function getLeagueTeamImage($teamId)
    {
        if(is_numeric($teamId)) {
            if(file_exists(APPLICATION_PATH . '/../public/images/team_logos/' . $teamId . '.jpg')) {
                return $this->view->escape($teamId) . '.jpg';
            }
        }

        return 'default.png';
    }
}
