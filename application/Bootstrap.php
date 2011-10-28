<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
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
        
}

