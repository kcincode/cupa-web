<?php
class Plugin_Authorization extends Zend_Controller_Plugin_Abstract
{
    protected $_auth;
    protected $_userId;
    protected $_url;
    protected $_params;

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
                if($this->_userId) {
                    $userTable =new Model_DbTable_User();
                    $user = $userTable->find($this->_userId)->current();
                }

                // log the permission error
                $log = new Zend_Log(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/logs/perms.log'));
                $log->log('***************************************************************', Zend_Log::INFO);
                $log->log('User Not Authorized', Zend_Log::INFO);
                $log->log('User: ', Zend_Log::INFO);
                if($this->_userId){
                    $log->log(print_r($user->toArray(), true), Zend_Log::INFO);
                } else {
                    $log->log('USER NOT LOGGED IN', Zend_Log::INFO);
                }
                $log->log('Route: ' . $routeName, Zend_Log::INFO);
                $log->log('Data: ', Zend_Log::INFO);
                $log->log(print_r($data, true), Zend_Log::INFO);
                $log->log('***************************************************************', Zend_Log::INFO);

                $request->setControllerName('error')
                        ->setActionName('auth');

            }
        } catch (Exception $e) {
            // do nothing
        }
    }
}
