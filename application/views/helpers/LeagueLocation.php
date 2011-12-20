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
        $string = '<a href="' . $this->view->escape($location['map_link']) . '" target="_new">' . $this->view->escape($location['location']) . '</a> ';
        $string .= '<div class="location-' . $this->view->escape($location['type']) . '-address">' . $this->view->escape($location['address_street']) . ', ' . $this->view->escape($location['address_city']) . ', ' . $this->view->escape($location['address_state']) . ' ' . $this->view->escape($location['address_zip']) . '</div>';
     
        if($location['type'] != 'league') {
            $string .= '<div class="location-' . $this->view->escape($location['type']) . '">' . date('D, F jS Y', strtotime($location['start'])) . ' ' . date('h:i A', strtotime($location['start'])) . ' - ' . date('h:i A', strtotime($location['end'])) . '</div>';
        }
        
        if(!empty($location['photo_link'])) {
            $string .= '<div class="location-' . $this->view->escape($location['type']) . '-photo"><a href="' . $this->view->escape($location['photo_link']) . '">' . $this->view->escape(ucwords($location['type'])) . ' Photos</a></div>';
        }
        
        
        return $string;
    }
}