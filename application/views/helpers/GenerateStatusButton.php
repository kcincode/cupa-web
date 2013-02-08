<?php

class My_View_Helper_GenerateStatusButton extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function generateStatusButton($type, $status, $league)
    {
        $html = '<button class="btn btn-mini btn-:btnStatus:" type="button" onclick="toggle(\'' . $type . '\', ' . $status['user_id'] . ', ' . $league->id . ');">';
        $html .= '<i class="icon-:icon: icon-white"></i>';
        $html .= '</button>';

        if($type != 'waiver') {
            $btnStatus = ($status[$type] == 1) ? 'success' : 'danger';
            $icon = ($status[$type] == 1) ? 'check' : 'remove';
        } else {
            $btnStatus = ($status['waiver'] == $league->year) ? 'success' : 'danger';
            $icon = ($status['waiver'] == $league->year) ? 'check' : 'remove';
        }

        return str_replace(':btnStatus:', $btnStatus, str_replace(':icon:', $icon, $html));
    }
}
