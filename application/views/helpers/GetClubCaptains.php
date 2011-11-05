<?php

class My_View_Helper_GetClubCaptains extends Zend_View_Helper_Abstract
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
    public function getClubCaptains($clubId)
    {
        $clubCaptainTable = new Cupa_Model_DbTable_ClubCaptain();
        $string = '';
        foreach($clubCaptainTable->fetchAllByClub($clubId) as $captain) {
            $string .= ', ' . $captain->name;
        }
        
        return substr($string, 3);
        
    }
}