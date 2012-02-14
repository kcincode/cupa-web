<?php

class Form_ClubEdit extends Zend_Form
{

    public function init()
    {
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $name = $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Name:',
        ));
        
        $typeArray = array(
            'Open' => 'Open',
            'Womens' => 'Womens',
            'Mixed' => 'Mixed',
            'Masters' => 'Masters',
            'Masters, Grand Masters' => 'Masters, Grand Masters',
        );
        
        $type = $this->addElement('select', 'type', array(
            'validators' => array(
                array('InArray', false, array(array_keys($typeArray))),
            ),
            'required' => true,
            'label' => 'Type:',
            'multiOptions' => $typeArray,
        ));

        $facebook = $this->addElement('text', 'facebook', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Facebook Link:',
            'description' => 'the part of the url after www.facebook.com excluding the first slash.',
        ));
        
        $twitter = $this->addElement('text', 'twitter', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Twitter Account:',
            'description' => 'Enter the @<name> twitter account.',
        ));
        
        $begin = $this->addElement('text', 'begin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'First Year:',
            'description' => 'Enter a year or leave blank if unknown.',
        ));
        
        $end = $this->addElement('text', 'end', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Last Year:',
            'description' => 'Enter a year or leave blank if current.',
        ));
        
        $email = $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',  
            ),
            'required' => false,
            'label' => 'Contact Email:',
            'description' => 'Enter the contact email address or leave blank.',
        ));
        
        $website = $this->addElement('text', 'website', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Website:',
            'description' => 'Enter the whole url or leave blank if none.',
        ));
        
        $content = $this->addElement('textarea', 'content', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Page Content:',
            'description' => 'Enter what you want to be on the page to describe the team.',
        ));
    }
    
    public function loadFromClub($club)
    {
        $this->getElement('name')->setValue($club->name);
        $this->getElement('type')->setValue($club->type);
        $this->getElement('facebook')->setValue($club->facebook);
        $this->getElement('twitter')->setValue($club->twitter);
        $this->getElement('begin')->setValue($club->begin);
        $this->getElement('end')->setValue($club->end);
        $this->getElement('email')->setValue($club->email);
        $this->getElement('website')->setValue($club->website);
        $this->getElement('content')->setValue($club->content);
        
    }


}

