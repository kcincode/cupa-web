<?php

class My_View_Helper_IsViewable extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function isViewable($routeName, $id = null)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $route = $router->getRoute($routeName);

        $data = array();
        if($route instanceof Zend_Controller_Router_Route) {
            foreach($route->getVariables() as $var) {
                $value = $request->getParam($var);
                $data[$var] = (empty($value)) ? $id : $value;
            }
        }

        $authorizeTable = new Model_DbTable_Authorize(Zend_Auth::getInstance()->getIdentity());
        return $authorizeTable->isAuthorized($routeName, $data);
    }
}
