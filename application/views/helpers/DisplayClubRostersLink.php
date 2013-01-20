<?php

class My_View_Helper_DisplayClubRostersLink extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function displayClubRostersLink($clubId)
    {
        if(is_numeric($clubId)) {
            $clubMemberTable = new Model_DbTable_ClubMember();
            $members = $clubMemberTable->fetchAllMemberByYear($clubId);
            if(count($members)) {
                $keys = array_keys($members);
                $year = $keys[sizeof($keys) - 1];
                return '<a class="btn btn-mini" href="' . $this->view->url(array('club_id' => $clubId), 'club_home') . '#' . $year . '"><i class="icon-list"></i> View Rosters</a>';
            }
            return '';
        }

        return '';
    }
}
