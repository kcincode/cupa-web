<?php

class My_View_Helper_GenerateVolunteerMap extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function generateVolunteerMap($data)
    {
        $url = 'http://maps.google.com/maps?q=';

        $url .= $data['address'];
        $url .= '+' . $data['city'];
        $url .= '+' . $data['state'];
        $url .= '+' . $data['zip'];
        $url .= '&t=h';

        return $url;
    }

}
