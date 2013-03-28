<?php
class Plugin_Authorization extends Zend_Controller_Plugin_Abstract
{
    protected $_auth;
    protected $_userId;

    public function __construct()
    {
        $this->_auth = Zend_Auth::getInstance();
        if($this->_auth->hasIdentity()) {
            $this->_userId = $this->_auth->getIdentity();
        }
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        try {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $routeName = $router->getCurrentRouteName();

            $route = $router->getRoute($routeName);
            $data = array();
            if($route instanceof Zend_Controller_Router_Route) {
                foreach($route->getVariables() as $var) {
                    $data[$var] = $request->getParam($var);
                }
            }

            $authorizeTable = new Model_DbTable_Authorize($this->_userId);
            if(!$authorizeTable->isAuthorized($routeName, $data)) {
                $request->setControllerName('error')
                        ->setActionName('auth');
            }
        } catch (Exception $e) {
            // do nothing
        }
    }
}
