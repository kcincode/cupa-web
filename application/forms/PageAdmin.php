<?php

class Cupa_Form_PageAdmin extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $pageTable = new Cupa_Model_DbTable_Page();
        $pages = array(0 => 'None');
        foreach($pageTable->fetchAllParentPages() as $page) {
            $pages[$page->id] = $page->name;
        }
        
        $parent = $this->addElement('select', 'parent', array(
            'validators' => array(
                array('InArray', false, array(array_keys($pages))),
            ),
            'required' => true,
            'label' => 'Parent:',
            'multiOptions' => $pages,
        ));
        
        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
        ));
        
        $is_visible = $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
        ));
        
    }
    
    public function loadFromPage($page)
    {
        $this->getElement('parent')->setValue($page->parent);
        $this->getElement('name')->setValue($page->name);
        $this->getElement('is_visible')->setValue($page->is_visible);
        
    }
}
