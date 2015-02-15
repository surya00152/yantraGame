<?php

namespace User;

class Module {
    
    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * View Helper Configration
     */
    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'userHelper' => function($sm) {
            return new View\Helper\UserHelper($sm);
        }
            )
        );
    }

}
