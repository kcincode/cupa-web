<?php

class My_View_Helper_IsVolunteerAdmin extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isVolunteerAdmin($volunteerId)
    {
        if($this->view->hasRole('admin') or $this->view->hasRole('manager') or $this->view->hasRole('volunteer')) {
            return true;
        }

        $volunteerTable = new Model_DbTable_Volunteer();
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $volunteer = $volunteerTable->find($volunteerId)->current();
            if(Zend_Auth::getInstance()->getIdentity() == $volunteer->id) {
                return true;
            }
        }

        return false;
    }
}
