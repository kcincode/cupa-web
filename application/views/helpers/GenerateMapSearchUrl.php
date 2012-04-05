<?php

class My_View_Helper_GenerateMapSearchUrl extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function generateMapSearchUrl($tournamentInfo)
    {
        $url = 'http://maps.google.com/maps?q=';
        $url .= $tournamentInfo->location_street;
        $url .= '+' . $tournamentInfo->location_city;
        $url .= '+' . $tournamentInfo->location_state;
        $url .= '+' . $tournamentInfo->location_zip;
        $url .= '&t=h';

        return $url;
    }
}
