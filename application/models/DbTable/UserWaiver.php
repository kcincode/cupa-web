<?php

class Model_DbTable_UserWaiver extends Zend_Db_Table
{
    protected $_name = 'user_waiver';
    protected $_primary = 'id';
    
    public function updateWaiver($userId, $year, $checked, $loggedInUserId)
    {
        $select = $this->select()
                       ->where('user_id = ?', $userId)
                       ->where('year = ?', $year);
        
        $result = $this->fetchRow($select);
        
        if($result and $checked == 'false') {
            $result->delete();
        } else if(!$result) {
            $this->insert(array(
                'user_id' => $userId,
                'year' => $year,
                'modified_at' => date('Y-m-d H:i:s'),
                'modified_by' => $loggedInUserId,
            ));
        }
        
    }
    
    public function hasWaiver($userId, $year)
    {
        $select = $this->select()
                       ->where('user_id = ?', $userId)
                       ->where('year = ?', $year);
        
        $result = $this->fetchRow($select);
        if($result) {
            return true;
        }
        
        return false;
    }

}
