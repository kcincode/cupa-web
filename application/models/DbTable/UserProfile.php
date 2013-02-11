<?php

class Model_DbTable_UserProfile extends Zend_Db_Table
{
    protected $_name = 'user_profile';
    protected $_primary = 'user_id';
    protected $_completenessColumns = array('gender', 'birthday', 'phone', 'height', 'level', 'experience');

    public function isEighteenOrOver($userId, $date = null)
    {
        $userProfile = $this->find($userId)->current();

        if(!$date) {
            $date = date('Y-m-d H:i:s');
        }

        if(!empty($userProfile->birthday)) {
            list($year, $month, $day) = explode("-", $userProfile->birthday);
            $age = (date("md", strtotime($date)) < $month.$day) ? date("Y", strtotime($date)) - $year - 1 : date("Y", strtotime($date)) - $year;
            return ($age >= 18) ? true : false;
        }

        return false;
    }

    public function mergeUsers($ids, $userId)
    {
        $profile = $this->find($userId)->current();
        foreach(explode(',', $ids) as $id) {
            $newProfile = $this->find($id)->current();
            foreach($newProfile as $key => $value) {
                if(empty($profile->$key) and !empty($value)) {
                    $profile->$key = $value;
                }
            }
        }
        $profile->save();
    }

    public function isComplete($userId)
    {
        $data = $this->find($userId)->current();

        foreach($this->_completenessColumns as $column) {
            if(empty($data->$column)) {
                return false;
            }
        }

        return true;
    }
}
