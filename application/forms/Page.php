<?php

class Form_Page extends Twitter_Bootstrap_Form_Horizontal
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $validParents = array('about', 'youth', 'leagues', 'pickup', 'clubs', 'links');
        
        $pageTable = new Model_DbTable_Page();
        $pages = array(0 => 'None');
        foreach($pageTable->fetchAllParentPages() as $page) {
            if(in_array($page->name, $validParents)) {
                $pages[$page->id] = $page->name;
            }
        }

        $this->addElement('select', 'parent', array(
            'validators' => array(
                array('InArray', false, array(array_keys($pages))),
            ),
            'required' => true,
            'label' => 'Parent:',
            'multiOptions' => $pages,
        ));

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Db_NoRecordExists', false, array('table' => 'page', 'field' => 'name', 'messages' => array('recordFound' => 'Page already exists'))),
            ),
            'label' => 'Name:',
        ));

        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
            'class' => 'span6',
        ));

        $this->addElement('text', 'url', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Url:',
            'class' => 'span6',
            'description' => 'If set it will ignore the page content.',
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
        ));

        $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'class' => 'span1',
            'description' => 'Lower numbers are shown first.',
        ));

        $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Content:',
            'class' => 'span6 ckeditor',
            'style' => 'height: 125px;',
        ));
        
        $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
            'value' => 1,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Create',
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
            array('parent', 'name', 'title', 'url', 'target', 'weight', 'content', 'is_visible'),
            'page_form',
            array(
                'legend' => 'Add Page',
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'page_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
