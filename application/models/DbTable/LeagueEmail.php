<?php

class Model_DbTable_LeagueEmail extends Zend_Db_Table
{
    protected $_name = 'league_email';
    protected $_primary = 'id';


    public function log($postData, $emails)
    {
        $recipients = array();
        foreach($postData['to'] as $to) {
            foreach($emails[$to] as $email) {
                $recipients[$email] = $email;
            }
        }

        $this->insert(array(
            'from' => $postData['from'],
            'subject' => $postData['subject'],
            'message' => $postData['content'],
            'recipients' => implode(',', $recipients),
            'time' => date('Y-m-d H:i:s'),
        ));

    }
}
