<?php

/**
 * @author Jack
 */

namespace Admin\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\View\Model\ViewModel,
    Zend\Session\Container;

/**
 *  Admin Plugin
 */
class AdminPlugin extends AbstractPlugin {
    
    /*
     * Get Admin Model
     */

    public function getAdminModel()
    {
        return $this->getController()->getServiceLocator()->get('Admin\Model\Admin');
    }
    
    /*
     * Get Yantra Model
     */

    public function getYantraModel()
    {
        return $this->getController()->getServiceLocator()->get('Admin\Model\Yantra');
    }
    
    /*
     * Get All Yantra List
     */

    public function getAllYantra()
    {
        return $this->getYantraModel()->getAllYantra();
    }
    
    /**
     *  is Admin LoggedIn
     */
    public function isAdminLogin() {
        $container = new Container('admin');
        if ($container->Id > 0) {
            return true;
        } else {
            return false;
        }
    }
    
}
