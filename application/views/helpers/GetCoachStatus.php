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
        return 'Complete';
    }
}
