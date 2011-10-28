<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRoutes()
    {
        // remove the default routes
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->removeDefaultRoutes();
        
        return $router;
    }
        
}

