<?php

class Cupa_Form_PageEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $pageTable = new Cupa_Model_DbTable_Page();

        $title = $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
        ));
        
        $url = $this->addElement('text', 'url', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Url:',
            'description' => 'If set it will ignore the page content.',
        ));
        
        $info = $pageTable->info();
        $tmpTargets = array_values(explode(',', str_replace("'",'', substr($info['metadata']['target']['DATA_TYPE'], 6, -1))));
        $targets = array();
        foreach($tmpTargets as $target) {
            $targets[$target] = $target;
        }

        $target = $this->addElement('select', 'target', array(
            'validators' => array(
                array('InArray', false, array(array_keys($targets))),
            ),
            'required' => true,
            'label' => 'Target:',
            'multiOptions' => $targets,
            'description' => 'Only used if url is specified.',
        ));

        $weight = $this->addElement('text', 'weight', array(
            'filters' => array('Int'),
            'required' => true,
            'label' => 'Weight:',
            'description' => 'Lower numbers are shown first.',
        ));
        
        $content = $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Content:',
        ));
    }

    public function loadFromPage($page)
    {
        $this->getElement('title')->setValue($page->title);
        $this->getElement('url')->setValue($page->url);
        $this->getElement('target')->setValue($page->target);
        $this->getElement('weight')->setValue($page->weight);
        $this->getElement('content')->setValue($page->content);
    }
}

