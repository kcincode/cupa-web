<?php

class My_View_Helper_Message extends Zend_View_Helper_Abstract
{
    const SUCCESS = 'success';
    const INFO    = 'info';
    const WARNING = 'block';
    const ERROR   = 'error';

    const SESSION_NAMESPACE = "messages";

    public function message($message = null, $type = self::SUCCESS)
    {
        if($message){
            $this->add($message, $type);
        } else{
            return $this;
        }
    }

    public function add($msg, $severity)
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        $status = $session->messages;
        $status[] = array($msg, $severity);
        $session->messages = $status;
    }

    public function get()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);

        $buf = array();

        $messages = ($session->messages) ? $session->messages : array();

        foreach ($messages as $msg) {
            $buf[] = "<div class=\"row messages\"><div class=\"span12\"><div class=\"alert alert-" . $this->severity_class($msg[1]) . "\"><a class=\"close\" data-dismiss=\"alert\">×</a>" . $msg[0] . "</div></div></div>";
        }

        if ($buf) {
            $session->unsetAll();
            return implode("", $buf);
        }
    }

    public function __toString()
    {
        $ret = $this->get();
        if($ret)
            return $ret;
        else
            return "";
    }

    public function severity_class($severity)
    {
        switch($severity) {
            case self::SUCCESS:
                return "success";

            case self::INFO:
                return "info";

            case self::WARNING:
                return "block";

            case self::ERROR:
                return "error";
        }
    }
}
