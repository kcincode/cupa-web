<?php

class Form_PageAdmin extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_page;

    public function __construct($page)
    {
        $this->_page = $page;

        parent::__construct();
    }


    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $pageTable = new Model_DbTable_Page();
        $pages = array(0 => 'None');
        foreach($pageTable->fetchAllParentPages() as $page) {
            $pages[$page->id] = $page->name;
        }

        $this->addElement('select', 'parent', array(
            'validators' => array(
                array('InArray', false, array(array_keys($pages))),
            ),
            'required' => true,
            'label' => 'Parent:',
            'multiOptions' => $pages,
            'value' => $this->_page->parent,
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
            'value' => $this->_page->name,
        ));

        $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
            'value' => $this->_page->is_visible,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Save',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('submit', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
            'escape' => false,
        ));

        $this->addDisplayGroup(
            array('parent', 'name', 'is_visible'),
            'page_admin_form',
            array(
                'legend' => 'Administer ' . $this->_page->title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'page_admin_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
