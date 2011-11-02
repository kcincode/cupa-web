<?php

class Cupa_Form_Contact extends Zend_Form
{
    
    public function init()
    {
        
        $this->addElementPrefixPath('Cupa_Validate', APPLICATION_PATH . '/models/Validate/', 'validate');
        
        $email = $this->addElement('text', 'from', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress', true),
            ),
            'required' => true,
            'label' => 'From:',
        ));
        
        $toSelection = $this->getContacts();
        
        $to = $this->addElement('select', 'to', array(
            'validators' => array(
                array('InArray', false, array(array_keys($toSelection))),
            ),
            'required' => true,
            'label' => 'To:',
            'multiOptions' => $toSelection,
        ));
        
        $subject = $this->addElement('text', 'subject', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Subject:',
            'value' => '[CUPA Information] More Information',
        ));
        
        $body = $this->addElement('textarea', 'content', array(
           'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Message Content:',
        ));
        
        $send = $this->addElement('submit', 'send', array(
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
        $contactTable = new Cupa_Model_DbTable_Contact();

        $data = array();
        foreach($contactTable->fetchAll() as $contact) {
            // for each contact add it to the data array
            $data[$contact->email] = $contact->name;
        }
        
        // return the final array
        return $data;
    }
}
