<?php

class Form_News extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');

        $newsCategoryTable = new Model_DbTable_NewsCategory();
        $categories = array();
        foreach($newsCategoryTable->fetchAllCategories() as $category) {
            $categories[$category->id] = $category->name;
        }
        
        $this->addElement('checkbox', 'is_visible', array(
            'label' => 'Is Visible:',
            'description' => 'Make sure this is checked to see the news item.',
        ));
        
        $this->addElement('select', 'category', array(
            'validators' => array(
                array('InArray', false, array(array_keys($categories))),
            ),
            'required' => true,
            'label' => 'Category:',
            'description' => 'This will be the box that it appears in',
            'multiOptions' => $categories,
        ));
        
        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
        ));
        
        $this->addElement('text', 'url', array(
            'filters' => array('StringTrim'),
            'label' => 'Url:',
            'description' => 'Enter the url to the news link (optional)',
        ));
       
        $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('StringLength', false, array(1, 255)),
            ),
            'label' => 'Short Description:',
            'description' => 'This is what is displayed on the home page',
        ));
        
        $this->addElement('text', 'remove_at', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Remove At:',
            'description' => 'Date/Time that the news item will not be shown on the home page.',
        ));

        $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'label' => 'Content:',
            'description' => 'This is if there is no page and you just want to have a link to a page with text. (optional)',
        ));
    }

    public function loadFromNews($news)
    {
        $this->getElement('is_visible')->setValue($news->is_visible);
        $this->getElement('category')->setValue($news->category_id);
        $this->getElement('title')->setValue($news->title);
        $this->getElement('url')->setValue($news->url);
        $this->getElement('info')->setValue($news->info);
        $this->getElement('remove_at')->setValue($news->remove_at);
        $this->getElement('content')->setValue($news->content);
    }

}

