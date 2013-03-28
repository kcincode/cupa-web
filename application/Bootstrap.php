<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    private $_acl = null;
    private $_auth = null;

    /**
     * getAcl will just return the acl object
     *
     * @return Zend_Acl
     */
    public function getAcl()
    {
        // return he acl object
        return $this->_acl;
    }

    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH,
        ));

        // setup the users role in the system
        $this->bootstrap('db');

        $fc = Zend_Controller_Front::getInstance();
        $fc->registerPlugin(new Plugin_Authorization());

        return $moduleLoader;
    }

    /**
     * Initializes the routes from the routes.ini config file
     * @return Zend_Controller_Router_Rewrite
     */
    protected function _initRoutes()
    {
        // remove the default routes
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->removeDefaultRoutes();

        // load the routes from the config file
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
        $router->addConfig($config , 'routes');

        return $router;
    }

    /**
     * Initializes the view with default data and the user object
     * @return Zend_View
     */
    protected function _initPage()
    {
        $this->bootstrap('View');
        $view = $this->getResource('View');

        $view->title = 'CUPA - Cincinnati Ultimate Players Association';
        $view->headTitle($view->title);

        // make sure that the db is loaded
        $this->bootstrap('db');

        // load the user object from the session storage if a user is logged in
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $userTable = new Model_DbTable_User();
            $view->user = $userTable->find(Zend_Auth::getInstance()->getIdentity())->current();

            $session = new Zend_Session_Namespace('adminuser');
            if($session->oldUser) {
                $view->isImpersonated = true;
            } else {
                $view->isImpersonated = false;
            }
        }

        //$view->acl = $this->_acl;

        return $view;
    }
}

