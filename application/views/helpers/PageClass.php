<?php

class My_View_Helper_PageClass extends Zend_View_Helper_Abstract
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
    public function pageClass()
    {
        if(empty($this->view->page->parent)) {
            return $this->view->escape($this->view->page->name);
        } else {
            $pageTable = new Model_DbTable_Page();
            $parent = $pageTable->find($this->view->page->parent)->current();
            return $this->view->escape($parent->name);
        }
        
        return null;
    }
}
