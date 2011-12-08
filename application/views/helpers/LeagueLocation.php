<?php

class My_View_Helper_LeagueLocation extends Zend_View_Helper_Abstract
{
    
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function leagueLocation($location)
    {
        $string = '<a href="' . $location['map_link'] . '" target="_new">' . $location['location'] . '</a> ';
        $string .= '<div class="location-' . $location['type'] . '-address">' . $location['address_street'] . ', ' . $location['address_city'] . ', ' . $location['address_state'] . ' ' . $location['address_zip'] . '</div>';
     
        if($location['type'] != 'league') {
            $string .= '<div class="location-' . $location['type'] . '">' . date('D, F jS Y', strtotime($location['start'])) . ' ' . date('h:i A', strtotime($location['start'])) . ' - ' . date('h:i A', strtotime($location['end'])) . '</div>';
        }
        
        if(!empty($location['photo_link'])) {
            $string .= '<div class="location-' . $location['type'] . '-photo"><a href="' . $location['photo_link'] . '">' . ucwords($location['type']) . ' Photos</a></div>';
        }
        
        
        return $string;
    }
}