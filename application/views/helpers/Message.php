<?php 

class My_View_Helper_Message extends Zend_View_Helper_Abstract {
    const SUCCESS = 'success';
    const INFO    = 'info';
    const WARNING = 'warning';
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
            $buf[] = "<li class=\"" . $this->severity_class($msg[1]) . "\">" . $this->view->escape($msg[0]) . "</li>";
        }
        if ($buf) {
            $session->unsetAll();
            return "<ul class=\"message\">" . implode("", $buf) . "</ul>";
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
                return "warning";

            case self::ERROR:
                return "error";
        }
    }
}
