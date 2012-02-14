<?php

class Model_DbTable_Form extends Zend_Db_Table
{
    protected $_name = 'form';
    protected $_primary = 'id';

    public function fetchForms($type, $year)
    {
        $select = $this->select()
                       ->order('year DESC')
                       ->order('name');
        
        if($type != 'all') {
            $select->where('name = ?', $type);
        }

        if($year != 0) {
            $select->where('year = ?', $year);
        }

        if($year != 0 and $type != 'all') {
            return $this->fetchRow($select);
        } else {
            $data = array();
            foreach($this->fetchAll($select) as $row) {
                $data[$row['year']][] = $row;
            }

            return $data;
        }
    }

}
