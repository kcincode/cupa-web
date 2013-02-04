<?php

class My_View_Helper_Slugify extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function slugify($string)
    {
        $string = strtolower($string);
        $string = html_entity_decode($string);
        $string = str_replace(array('ä','ü','ö','ß'),array('ae','ue','oe','ss'),$string);
        $string = preg_replace('#[^\w\säüöß]#',null,$string);
        $string = preg_replace('#[\s]{2,}#',' ',$string);
        $string = str_replace(array(' '),array('-'),$string);
        return $string;
    }
}
