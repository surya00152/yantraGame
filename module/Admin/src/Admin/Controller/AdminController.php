<?php

/**
 * @author Jack
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

/**
 * Admin Controller
 */
class AdminController extends AbstractActionController
{
    /*
     * Admin Login
     */
    public function loginAction()
    {
        $res['loginError'] = false;
        if (!$this->adminPlugin()->isAdminLogin()) {
            if ($this->getRequest()->isPost()) {
                $res['loginError'] = true;
                $post = $this->getRequest()->getPost();
                
                $getAdmin = $this->adminPlugin()->getAdminModel()->checkAdminLogin($post);
                                    
                if (count($getAdmin) > 0) {
                    $container = new Container('admin');
                    $container->userName = $getAdmin['userName'];
                    $container->Id = $getAdmin['Id'];
                    return $this->redirect()->toUrl('/public/admin/home');
                }
            }
        } else {
            return $this->redirect()->toUrl('/public/admin/home');
        }
        return new ViewModel($res);
    }
    
    /*
     * Admin Home
     */
    public function homeAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        
    }
    
    /*
     * Admin dashboard
     */
    public function dashboardAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
    }
    
    
}
