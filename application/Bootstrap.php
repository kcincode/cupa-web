<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
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

        // TODO: init the user data/object
        
        return $view;
    }
}

