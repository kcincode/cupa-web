<?php

class My_View_Helper_FormAddress extends Zend_View_Helper_FormElement
{
    public function formAddress($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);
        
        $disabled = '';
        if($disabled) {
            $disabled = ' disabled="disabled"';
        }
        
        $endTag = '/>';
        
        if(trim($value) != ', ,') {
            $matches = array();
            preg_match('/^(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)$/', $value, $matches);
            $street = $matches[1];
            $city = $matches[2];
            $state = $matches[3];
            $zip = $matches[4];
            
        } else {
            $street = null;
            $city = null;
            $state = null;
            $zip = null;
        }
        
        $attribs['class'] = 'street';
        $xhtml = '<input type="text"'
               . ' name="' . $this->view->escape($name) . '_street"'
               . ' id="' . $this->view->escape($id) . '_street"'
               . ' value="' . $street . '"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $endTag . '<br/>';
        
        $attribs['class'] = 'city';
        $xhtml .= '<input type="text"'
               . ' name="' . $this->view->escape($name) . '_city"'
               . ' id="' . $this->view->escape($id) . '_city"'
               . ' value="' . $city . '"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $endTag . ', ';

        $attribs['class'] = 'state';
        $xhtml .= '<input type="text"'
               . ' name="' . $this->view->escape($name) . '_state"'
               . ' id="' . $this->view->escape($id) . '_state"'
               . ' value="' . $state . '"'
               . ' size="2"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $endTag . ' ';
        
        $attribs['class'] = 'zip';
        $xhtml .= '<input type="text"'
               . ' name="' . $this->view->escape($name) . '_zip"'
               . ' id="' . $this->view->escape($id) . '_zip"'
               . ' value="' . $zip . '"'
               . ' size="5"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $endTag;

        return $xhtml;
    }
}