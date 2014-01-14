<?php

class My_View_Helper_GetCoachStatus extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * This helper will return the page name of the parent page
     *
     * @return string
     */
    public function getCoachStatus($data)
    {
        // check each of the 7 points

        if($data['background'] == 0 ||
           //$data['bsa_safety'] == 0 ||
           $data['concussion'] == 0 ||
           //$data['chaperon'] == 0 ||
           $data['manual'] == 0 ||
           $data['rules'] == 0) {
           //$data['usau'] == 0) {
            return 'Incomplete';
        }

        $leagueTable = new Model_DbTable_League();
        $league = $leagueTable->find($data['league_id'])->current();

        $userWaiverTable = new Model_DbTable_UserWaiver();
        return ($userWaiverTable->hasWaiver($data['user_id'], $league->year) === true) ? 'Complete' : 'Incomplete';
    }
}
