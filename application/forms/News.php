<?php

class Form_News extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_news;

    public function __construct($news = null)
    {
        $this->_news = $news;

        parent::__construct();
    }

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
            'value' => (empty($this->_news->is_visible)) ? null : $this->_news->is_visible,
        ));

        $this->addElement('select', 'category', array(
            'validators' => array(
                array('InArray', false, array(array_keys($categories))),
            ),
            'required' => true,
            'label' => 'Category:',
            'description' => 'This will be the box that it appears in',
            'multiOptions' => $categories,
            'value' => (empty($this->_news->category)) ? null : $this->_news->category,
        ));

        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Title:',
            'class' => 'span6',
            'value' => (empty($this->_news->title)) ? null : $this->_news->title,
        ));

        $this->addElement('text', 'url', array(
            'filters' => array('StringTrim'),
            'label' => 'Url:',
            'description' => 'Enter the url to the news link (optional)',
            'class' => 'span6',
            'value' => (empty($this->_news->url)) ? null : $this->_news->url,
        ));

        $this->addElement('textarea', 'info', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('StringLength', false, array(1, 255)),
            ),
            'label' => 'Short Description:',
            'class' => 'span6 ckeditor',
            'description' => 'This is what is displayed on the home page',
            'value' => (empty($this->_news->info)) ? null : $this->_news->info,
        ));

        $this->addElement('text', 'remove_at', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Remove At:',
            'class' => 'span3 datetimepicker',
            'style' => 'text-align: center',
            'description' => 'Date/Time that the news item will not be shown on the home page.',
            'value' => (empty($this->_news->remove_at)) ? null : date('m/d/Y H:i', strtotime($this->_news->remove_at)),
        ));

        $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'label' => 'Content:',
            'class' => 'span6 ckeditor',
            'description' => 'This is if there is no page and you just want to have a link to a page with text. (optional)',
            'value' => (empty($this->_news->content)) ? null : $this->_news->content,
        ));

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => (empty($this->_news)) ? 'Create' : 'Save',
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

        $title = (empty($this->_news)) ? 'Add News Story' : 'Edit News Story';
        $this->addDisplayGroup(
            array('category', 'title', 'url', 'info', 'content', 'remove_at', 'is_visible'),
            'news_edit_form',
            array(
                'legend' => $title,
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'news_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}

