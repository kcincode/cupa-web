<?php

class Model_DbTable_UserEmergency extends Zend_Db_Table
{
    protected $_name = 'user_emergency';
    protected $_primary = 'id';

    public function fetchContact($userId, $phone)
    {
    	$select = $this->select()
                       ->where('user_id = ?', $userId)
    	               ->where('phone = ?', $phone);

    	return $this->fetchRow($select);
    }

    public function fetchAllContacts($userId)
    {
    	$select = $this->select()
    	               ->where('user_id = ?', $userId)
    	               ->order('weight ASC');

    	return $this->fetchAll($select);
    }

    public function createBlankContact($userId)
    {
        $result = $this->fetchContact($userId, 'phone');

        if(count($result) == 0) {
            $id = $this->insert(array(
                'user_id' => $userId,
                'first_name' => 'Contact',
                'last_name' => 'Name',
                'phone' => 'phone',
                'weight' => count($this->fetchAllContacts($userId)) + 1,
            ));

            if($id) {
                return $this->find($id)->current();
            }
        }

        return null;
    }

    public function updateContacts($userId, $names, $phones)
    {
        foreach($this->fetchAllContacts($userId) as $contact) {
            if(!in_array($contact->phone, $phones)) {
                $contact->delete();
            }
        }

        $i = 0;
        foreach($phones as $phone) {
            $contact = $this->fetchContact($userId, $phone);
            list($first, $last) = explode(' ', $names[$i]);

            if(!$contact) {
                $contact = $this->createRow();
                $contact->user_id = $userId;
                $contact->phone = $phone;
            }

            $contact->first_name = $first;
            $contact->last_name = $last;
            $contact->weight = $i;
            $contact->save();

            $i++;
        }
    }
}
