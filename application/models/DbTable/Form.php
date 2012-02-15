<?php

class Model_DbTable_Form extends Zend_Db_Table
{
    protected $_name = 'form';
    protected $_primary = 'id';

    public function fetchForms($name, $year)
    {
        $select = $this->select()
                       ->order('year DESC')
                       ->order('name');
        
        if($name != 'all') {
            $select->where('name = ?', $name);
        }

        if($year != 0) {
            $select->where('year = ?', $year);
        }

        if($year != 0 and $name != 'all') {
            return $this->fetchRow($select);
        } else {
            $data = array();
            foreach($this->fetchAll($select) as $row) {
                $data[$row['year']][] = $row;
            }

            return $data;
        }
    }

    public function updateForm($formId, $year, $name)
    {
        $result = $this->fetchForms($name, $year);

        if(!$result) {
            $form = $this->find($formId)->current();
            $form->year = $year;
            $form->name = $name;
            $form->save();
            return true;
        }        

        return false;
    }

    public function isUnique($md5, $formId = null)
    {
        $select = $this->select()
                       ->where('md5 = ?', $md5);

        if($formId) {
            $select->where('id <> ?', $formId);
        }

        $result = $this->fetchRow($select);

        if($result) {
            return false;
        }

        return true;
    }

}
