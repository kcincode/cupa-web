<?php 


class Form_Element_Address extends Zend_Form_Element_Xhtml
{
    public $helper = 'formAddress';
    
    public $options;
    
    public function __construct($fieldName, $options = null)
    {
        $this->setLabel($options['label']);
        $this->setDescription($options['description']);
        $this->setValue($options['value']);
        
        parent::__construct($fieldName);
    }
    
    public function isValid($value, $context = null) 
    {
        $name = $this->getName();
        
        return parent::isValid($context[$name . '_street'] . ', ' .
                               $context[$name . '_city'] . ', ' .
                               $context[$name . '_state'] . ' ' .
                               $context[$name . '_zip']);
    }
}
