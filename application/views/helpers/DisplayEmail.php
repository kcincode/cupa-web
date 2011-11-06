<?php

class My_View_Helper_DisplayEmail extends Zend_View_Helper_Abstract
{
    public function displayEmail($email)
    {
        return str_replace('.', ' DOT ', str_replace('@', ' AT ', $email));
    }
}