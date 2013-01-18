<?php

class Form_PageEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_page;

    public function __construct($page)
    {
        $this->_page = $page;

        parent::__construct();
    }

    public function init()
    {
        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
            'class' => 'span6',
            'value' => $this->_page->title,
        ));

        $this->addElement('text', 'url', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Url:',
            'class' => 'span6',
            'description' => 'If set it will ignore the page content.',
            'value' => $this->_page->url,
        ));

        $pageTable = new Model_DbTable_Page();
        $info = $pageTable->info();
        $tmpTargets = array_values(explode(',', str_replace("'",'', substr($info['metadata']['target']['DATA_TYPE'], 6, -1))));
        $targets = array();
        foreach($tmpTargets as $target) {
            $targets[$target] = $target;
        }

        $this->addElement('select', 'target', array(
            'validators' => array(
                array('InArray', false, array(array_keys($targets))),
            ),
            'required' => true,
            'label' => 'Target:',
            'class' => 'span2',
            'multiOptions' => $targets,
            'description' => 'Only used if url is specified.',
            'value' => $this->_page->target,
        ));

        $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'class' => 'span1',
            'description' => 'Lower numbers are shown first.',
            'value' => $this->_page->weight,
        ));

        $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Content:',
            'class' => 'span6 ckeditor',
            'style' => 'height: 125px;',
            'value' => $this->_page->content,
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
            array('title', 'url', 'target', 'weight', 'content'),
            'page_edit_form',
            array(
                'legend' => 'Edit ' . $this->_page->title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'page_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}

