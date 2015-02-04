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
        
        $currentTime = $this->userPlugin()->getAppService()->getTime();
        $drawTime = $this->userPlugin()->getAppService()->getDrawTime();
        $drawDate = $this->userPlugin()->getAppService()->getDate();
        if (empty($drawTime)) {
            $drawTime = '00:00:00';
        }
              
        $getYantraRateList = $this->userPlugin()->getTicketModel()->getYantraRate($drawDate,$drawTime);
        $newYantraRate = array();
        $totalRate = array(
            'totalQnt' => 0,
            'totalPrice' => 0
        );
        
        if (count($getYantraRateList) > 0) {
            foreach ($getYantraRateList as $key => $rate) {
                $getYantraRateList[$key]['price'] = $rate['quantity'] * 11;  
                $getYantraRateList[$key]['winPrice'] = $rate['quantity'] * 100;
            
                $totalRate['totalQnt'] = $totalRate['totalQnt'] + $rate['quantity'];
                $totalRate['totalPrice'] = $totalRate['totalPrice'] + $getYantraRateList[$key]['price'];
            }   
            
            foreach ($getYantraRateList as $key => $rate) {
                $getYantraRateList[$key]['PL'] = $totalRate['totalPrice'] - $rate['winPrice'];  
                $getYantraRateList[$key]['jackpotWinPrice'] = $rate['winPrice'] * 2;
                $getYantraRateList[$key]['jackpotPL'] = $totalRate['totalPrice'] - $getYantraRateList[$key]['jackpotWinPrice'];
            }
            
            foreach ($getYantraRateList as $key => $rate) {
                $newYantraRate[$rate['yantraId']] = $rate;
            }            
        }
        $drawYantra = $this->userPlugin()->getDrowModel()->getAllDrowYantra($drawDate);
        $drawMode = $this->adminPlugin()->getAdminModel()->getDrawMode();
        return new ViewModel(array(
            'rate' => $newYantraRate,
            'totalRate' => $totalRate,
            'drawYantra' => $drawYantra,
            'currentTime' => $currentTime,
            'drawMode' => $drawMode)
        );
    }
    
    /*
     * User local
     */
    public function localUsersAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        //Get local user Details
        $data['localUser'] = $this->userPlugin()->getUserModel()->getUserByRoll('local');
        
        return new ViewModel($data);
    }
    
    /*
     * User agent
     */
    public function agentUsersAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        //Get local user Details
        $data['agentUser'] = $this->userPlugin()->getUserModel()->getUserByRoll('agent');
        
        return new ViewModel($data);
    }
    
    /*
     * Local User Purchase report
     */
    public function purchaseReportAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $data = array();
        
        $localUserId = $this->params()->fromRoute('id', 0);
        
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
            
        $postData = array (
            'userRoll' => 'local',
            'Id' => $localUserId,
            'date' => !empty($post['date'])?$post['date']:$this->userPlugin()->getAppService()->getDate(),
        );
        //Get Purchase report By UserId
        $response = $this->userPlugin()->getPurchaseReport($postData,true);
        
        if ($response['status'] == 'success') {
            $data['purchaseReport'] = $response['data'];
        } else {
            $data['purchaseReport'] = array();
        }
        
        return new ViewModel($data);
    }
    
    /*
     * Local User Daywise report
     */
    public function daywiseReportAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $data = array();
        
        $localUserId = $this->params()->fromRoute('id', 0);
        
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
            
        $postData = array (
            'userRoll' => 'local',
            'Id' => $localUserId,
            'startDate' => !empty($post['startDate'])?$post['startDate']:$this->userPlugin()->getAppService()->getDate(),
            'endDate' => !empty($post['endDate'])?$post['endDate']:$this->userPlugin()->getAppService()->getDate(),
        );
        //Get Purchase report By UserId
        $response = $this->userPlugin()->getDaywiseReport($postData,true);
        
        if ($response['status'] == 'success') {
            $data['daywiseReport'] = $response['data'];
        } else {
            $data['daywiseReport'] = array();
        }
        
        return new ViewModel($data);
    }
    
    /*
     * Local User Show wise report
     */
    public function showwiseReportAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $data = array();
        
        $localUserId = $this->params()->fromRoute('id', 0);
        
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
            
        $postData = array (
            'userRoll' => 'local',
            'Id' => $localUserId,
            'date' => !empty($post['date'])?$post['date']:$this->userPlugin()->getAppService()->getDate(),
        );
        //Get Purchase report By UserId
        $response = $this->userPlugin()->getShowwiseReport($postData,true);
        
        if ($response['status'] == 'success') {
            $data['showwiseReport'] = $response['data'];
        } else {
            $data['daywiseReport'] = array();
        }
        
        return new ViewModel($data);
    }
    
    /*
     * Local User Show wise report
     */
    public function saveDrowModeAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
                
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        $isValid = false;    
        if ($post['drawMode'] >= 1 && $post['drawMode'] >= 3) {
            if (!isset($post['jackpotMode'])) {
                $post['jackpotMode'] = 'off';
            }
            if (isset($post['manual'])) {
                if ($post['manual'] >= 1 && $post['manual'] <= 10) {
                    $isValid = true;
                } else {
                    $error = 'Invalid Selected Yantra';
                }
            }
            if (isset($post['percentage'])) {
                if ($post['percentage'] >= 1 && $post['percentage'] <= 50) {
                    $post['jackpotMode'] = 'off';
                    $isValid = true;
                } else {
                    $error = 'Invalid Selected Persantage.';
                }
            }            
        } else {
            $error = 'Invalid Draw Mode';
        }
        
        if ($isValid == true) {
            unset($post['name']);
            unset($post['userName']);
            unset($post['password']);
            
            if ($post['jackpotMode'] == 'on') {
                $post['jackpotMode'] = 1;
            } else {
                $post['jackpotMode'] = 0;
            }
            //Update Yantra Mode Info
            $this->adminPlugin()->getAdminModel()->updateAdmin($post);
        }
        
        return $this->redirect()->toUrl('/public/admin/dashboard');
    }
    
    
    
}
