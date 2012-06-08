<?php

class Form_Contact extends Zend_Form
{
    
    public function init()
    {
        
        $this->addElementPrefixPath('Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $this->addElement('text', 'from', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress', true),
            ),
            'required' => true,
            'label' => 'From:',
        ));
        
        $toSelection = $this->getContacts();
        
        $this->addElement('select', 'to', array(
            'validators' => array(
                array('InArray', false, array(array_keys($toSelection))),
            ),
            'required' => true,
            'label' => 'To:',
            'multiOptions' => $toSelection,
        ));
        
        $this->addElement('text', 'subject', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Subject:',
            'value' => '[CUPA Information] More Information',
        ));
        
        $this->addElement('textarea', 'content', array(
           'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Message Content:',
        ));
        
        $this->addElement('submit', 'send', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Send Email',
        ));
        
    }
    
    /**
     * Handle getting the contacts from the database
     * 
     * @return array
     */
    private function getContacts()
    {
        // link to the contact table
        $contactTable = new Model_DbTable_Contact();

        $data = array();
        foreach($contactTable->fetchAll() as $contact) {
            // for each contact add it to the data array
            $data[$contact->email] = $contact->name;
        }
        
        // return the final array
        return $data;
    }
}
