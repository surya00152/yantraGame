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
     * Admin Logout
     */
    public function logoutAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $adminUser = new Container('admin');
        $adminUser->getManager()->getStorage()->clear();
            
       return $this->redirect()->toUrl('/public/admin/login');
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
            $myDrawTime = '24:00:00';
        } else {
            $myDrawTime = $drawTime;
        } 
        
        $currentDateTime  = strtotime($drawDate.' '.$currentTime);
        $drowDateTime = strtotime($drawDate.' '.$myDrawTime);
        $differenceInSeconds = $drowDateTime - $currentDateTime;
        
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
            'drawMode' => $drawMode,
            'remainigTime' => $differenceInSeconds)
        );
    }
    
    /*
     * Change Admin Password
     */
    public function changeAdminPasswordAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }                
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        
        if (count($post) > 0) {
            if (!empty($post['oldPassword']) && !empty($post['newPassword'])) {
                //Get user details By Id
                $adminData = $this->adminPlugin()->getAdminModel()->getAdminData();
                if (count($adminData) > 0) {
                    if ($adminData['password'] == $post['oldPassword']) {
                        $updateAdminData['password'] = $post['newPassword'];
                        $this->adminPlugin()->getAdminModel()->updateAdmin($updateAdminData);

                        $this->flashMessenger()->setNamespace('success');
                        $this->flashMessenger()->addMessage('Admin Password has been changed successfully.');
                    } else {
                        $this->flashMessenger()->setNamespace('error');
                        $this->flashMessenger()->addMessage('Old password does not match.');
                    }
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('Admin not found.');
                }
            } else {
                $this->flashMessenger()->setNamespace('error');
                $this->flashMessenger()->addMessage('Please enter Old and New Password.');
            }
        }
        if ($post['userRoll'] == 'local') {
            return $this->redirect()->toUrl('/public/admin/localUsers');
        } else {
            return $this->redirect()->toUrl('/public/admin/agentUsers');
        }
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
        $userData = $this->userPlugin()->getUserModel()->getUserById($localUserId);    
        
        if (count($userData) > 0) {
            $postData = array (
                'Id' => $localUserId,
                'userData' => $userData,
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
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('User not found.');
        }        
        
        return new ViewModel($data);
    }
    
    /*
     * Agent User Daywise report
     */
    public function agentDaywiseReportAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $data = array();
        
        $agentUserId = $this->params()->fromRoute('id', 0);
        
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        
        $userData = $this->userPlugin()->getUserModel()->getUserById($agentUserId);   
        
        if (count($userData) > 0) {
            $postData = array (
                'userRoll' => 'agent',
                'Id' => $agentUserId,
                'userData' => $userData,
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
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('User not found.');
        }        
        return new ViewModel($data);
    }
    
    /*
     * Local User Daywise report
     */
    public function transferReportAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }
        $data = array();
        
        $agentUserId = $this->params()->fromRoute('id', 0);
        
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        
        $userData = $this->userPlugin()->getUserModel()->getUserById($agentUserId);   
        
        if (count($userData) > 0) {
            $postData = array (
                'Id' => $agentUserId,
                'userData' => $userData,
                'date' => !empty($post['date'])?$post['date']:$this->userPlugin()->getAppService()->getDate(),
            );
            //Get Purchase report By UserId
            $response = $this->userPlugin()->transactionReport($postData,true);
            
            if ($response['status'] == 'success') {
                $data['transferReport'] = $response['data'];
            } else {
                $data['transferReport'] = array();
            }
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('User not found.');
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
        if ($post['drawMode'] >= 1 && $post['drawMode'] <= 3) {
            if (isset($post['manual'])) {
                if ($post['manual'] >= 1 && $post['manual'] <= 10) {
                    $isValid = true;
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('Invalid Selected Yantra.');
                }
            }
            if (isset($post['percentage'])) {
                if ($post['percentage'] >= 1 && $post['percentage'] <= 50) {
                    $isValid = true;
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('Invalid Selected Persantage.');
                }
            }            
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('Invalid Draw Mode.');
        }
        
        if ($isValid == true) {
            //echo '<pre>';print_r($post);exit;
            unset($post['name']);
            unset($post['userName']);
            unset($post['password']);
            
            //Update Yantra Mode Info
            $this->adminPlugin()->getAdminModel()->updateAdmin($post);
            $this->flashMessenger()->setNamespace('success');
            $this->flashMessenger()->addMessage('Draw Mode save successfully.');
        }
        
        return $this->redirect()->toUrl('/public/admin/dashboard');
    }
    
    public function changeUserCreditAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }                
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        
        if (count($post) > 0) {
            //Get user details By Id
            $userData = $this->userPlugin()->getUserModel()->getUserById($post['userId']);
            if (count($userData) > 0) {
                if (is_numeric($post['balVal']) && $post['balVal'] > 0) {                    
                    if ($post['type'] == 'credit') {
                 //CREDIT
                        if ($userData['userRoll'] == 'agent') {
                       //AGENT     
                            $currentDate = $this->userPlugin()->getAppService()->getDate();
                            
                            $updateUser['avaiTransBal'] = $userData['avaiTransBal'] + $post['balVal'];
                            $updateUser['totalTranBal'] = $userData['totalTranBal'] + $post['balVal'];
                            
                            $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                            
                            //check date records exist if not then create new if Yes then use Id
                            $getTicketDate = $this->userPlugin()->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                            if (count($getTicketDate) == 0) {
                                //create Date Records
                                $dateData  = array (
                                    'userId' => $userData['Id'],
                                    'drawDate' => $currentDate,
                                    'openingBal' => $userData['avaiTransBal'],
                                );

                                $getDateEntity = $this->userPlugin()->getTicketDateModel()->createTicketDate($dateData);
                                $dateId = $getDateEntity->Id;
                            } else {
                                $dateId = $getTicketDate['Id'];
                            }
                            
                            $transactionData = array (
                                'dateId' => $dateId,
                                'userId' => 1,
                                'agentId' => $userData['Id'],
                                'transBalance' => $post['balVal'],
                                'transType' => 'Debit',
                                'time' => $this->userPlugin()->getAppService()->getTime()
                            );
                            //create Transaction report
                            $this->userPlugin()->getTransactionModel()->createTransaction($transactionData);

                        } else {
                            $currentDate = $this->userPlugin()->getAppService()->getDate();
                            
                            $updateUser['avaiPurchaseBal'] = $userData['avaiPurchaseBal'] + $post['balVal'];
                            $updateUser['totalPurchaseBal'] = $userData['totalPurchaseBal'] + $post['balVal'];
                            
                            $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                            
                            //check date records exist if not then create new if Yes then use Id
                            $getTicketDate = $this->userPlugin()->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                            if (count($getTicketDate) == 0) {
                                //create Date Records
                                $dateData  = array (
                                    'userId' => $userData['Id'],
                                    'drawDate' => $currentDate,
                                    'openingBal' => $userData['avaiTransBal'],
                                );

                                $getDateEntity = $this->userPlugin()->getTicketDateModel()->createTicketDate($dateData);
                                $dateId = $getDateEntity->Id;
                            } else {
                                $dateId = $getTicketDate['Id'];
                            }
                            
                            $transactionData = array (
                                'dateId' => $dateId,
                                'userId' => $userData['Id'],
                                'agentId' => 1,
                                'transBalance' => $post['balVal'],
                                'transType' => 'Credit',
                                'time' => $this->userPlugin()->getAppService()->getTime()
                            );
                            //create Transaction report
                            $this->userPlugin()->getTransactionModel()->createTransaction($transactionData);
                            
                            //set notification message
                            $notificationData = array (
                                'reqFrom' => 1,
                                'reqTo' => $userData['Id'],
                                'requestedName' => 'Admin',
                                'message' => 'Your Account Credited by Admin. Added balance is : '.$post['balVal'],
                                'date' => $this->userPlugin()->getAppService()->getDateTime()
                            );
                            $this->userPlugin()->getNotificationModel()->createNotification($notificationData);
                        }                        
                        $this->flashMessenger()->setNamespace('success');
                        $this->flashMessenger()->addMessage('Account credited successfully.');
                    } else {
                 //DEBIT
                        if ($userData['userRoll'] == 'agent') {
                       //AGENT     
                            $currentDate = $this->userPlugin()->getAppService()->getDate();
                            
                            $updateUser['avaiTransBal'] = $userData['avaiTransBal'] + $post['balVal'];
                            $updateUser['totalTranBal'] = $userData['totalTranBal'] + $post['balVal'];
                            
                            $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                            
                            //check date records exist if not then create new if Yes then use Id
                            $getTicketDate = $this->userPlugin()->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                            if (count($getTicketDate) == 0) {
                                //create Date Records
                                $dateData  = array (
                                    'userId' => $userData['Id'],
                                    'drawDate' => $currentDate,
                                    'openingBal' => $userData['avaiTransBal'],
                                );

                                $getDateEntity = $this->userPlugin()->getTicketDateModel()->createTicketDate($dateData);
                                $dateId = $getDateEntity->Id;
                            } else {
                                $dateId = $getTicketDate['Id'];
                            }
                            
                            $updateUser['avaiTransBal'] = $userData['avaiTransBal'] - $post['balVal'];
                            $updateUser['totalTranBal'] = $userData['totalTranBal'] - $post['balVal'];
                            
                            $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                            
                            $transactionData = array (
                                'dateId' => $dateId,
                                'userId' => 1,
                                'agentId' => $userData['Id'],
                                'transBalance' => $post['balVal'],
                                'transType' => 'Credit',
                                'time' => $this->userPlugin()->getAppService()->getTime()
                            );
                            //create Transaction report
                            $this->userPlugin()->getTransactionModel()->createTransaction($transactionData);

                        } else {
                      //LOCAL      
                            $currentDate = $this->userPlugin()->getAppService()->getDate();
                            
                            $updateUser['avaiPurchaseBal'] = $userData['avaiPurchaseBal'] - $post['balVal'];
                            //change user balance
                            
                            $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                            
                            //check date records exist if not then create new if Yes then use Id
                            $getTicketDate = $this->userPlugin()->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                            if (count($getTicketDate) == 0) {
                                //create Date Records
                                $dateData  = array (
                                    'userId' => $userData['Id'],
                                    'drawDate' => $currentDate,
                                    'openingBal' => $userData['avaiTransBal'],
                                );

                                $getDateEntity = $this->userPlugin()->getTicketDateModel()->createTicketDate($dateData);
                                $dateId = $getDateEntity->Id;
                            } else {
                                $dateId = $getTicketDate['Id'];
                            }
                            
                            $transactionData = array (
                                'dateId' => $dateId,
                                'userId' => $userData['Id'],
                                'agentId' => 1,
                                'transBalance' => $post['balVal'],
                                'transType' => 'Debit',
                                'time' => $this->userPlugin()->getAppService()->getTime()
                            );
                            //create Transaction report
                            $this->userPlugin()->getTransactionModel()->createTransaction($transactionData);
                            
                            //set notification message
                            $notificationData = array (
                                'reqFrom' => 1,
                                'reqTo' => $userData['Id'],
                                'requestedName' => 'Admin',
                                'message' => 'Your Account Debited by Admin. Debit balance is : '.$post['balVal'],
                                'date' => $this->userPlugin()->getAppService()->getDateTime()
                            );
                            $this->userPlugin()->getNotificationModel()->createNotification($notificationData);
                        }
                        $this->flashMessenger()->setNamespace('success');
                        $this->flashMessenger()->addMessage('Account Debited successfully.');
                    }                   
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('Invalid input Balance.');
                }
            } else {
                $this->flashMessenger()->setNamespace('error');
                $this->flashMessenger()->addMessage('User not found.');
            }
        }
        if ($post['userRoll'] == 'local') {
            return $this->redirect()->toUrl('/public/admin/localUsers');
        } else {
            return $this->redirect()->toUrl('/public/admin/agentUsers');
        }
    }
    
    public function changeUserPasswordAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }                
        //Get post value from html form.
        $post = $this->getRequest()->getPost();
        
        if (count($post) > 0) {
            //Get user details By Id
            $userData = $this->userPlugin()->getUserModel()->getUserById($post['userId']);
            if (count($userData) > 0) {
                if (!empty($post['password'])) {
                    $updateUser['password'] = sha1($post['password']);
                    $this->userPlugin()->getUserModel()->updateUser($userData['Id'],$updateUser);
                    
                    $this->flashMessenger()->setNamespace('success');
                    $this->flashMessenger()->addMessage('User Password has been changed successfully.');
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('Please input password.');
                }
            } else {
                $this->flashMessenger()->setNamespace('error');
                $this->flashMessenger()->addMessage('User not found.');
            }
        }
        if ($post['userRoll'] == 'local') {
            return $this->redirect()->toUrl('/public/admin/localUsers');
        } else {
            return $this->redirect()->toUrl('/public/admin/agentUsers');
        }
    }
    
    public function changeStatusAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }                
        //Get post value from html form.
            
        $localUserId = $this->params()->fromRoute('id', 0);
        
        $localUserStaus = $this->params()->fromRoute('status', 0);
        
        //Get user details By Id
        $userData = $this->userPlugin()->getUserModel()->getUserById($localUserId);
        if (count($userData) > 0) {
            if ($localUserStaus == 0) {
                $updateUser['accountStatus'] = 'Deactive';
            } else {
                $updateUser['accountStatus'] = 'Active';
            }                
            $this->userPlugin()->getUserModel()->updateUser($localUserId,$updateUser);
            
            $this->flashMessenger()->setNamespace('success');
            $this->flashMessenger()->addMessage('User Account status has been changed successfully.');
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('User not found.');
        }
        
        if ($userData['userRoll'] == 'local') {
            return $this->redirect()->toUrl('/public/admin/localUsers');
        } else {
            return $this->redirect()->toUrl('/public/admin/agentUsers');
        }
    }
    
    public function createUserAction()
    {
        if (!$this->adminPlugin()->isAdminLogin()) {
            return $this->redirect()->toUrl('/public/admin/login');
        }                
        //Get post value from html form.
        $post = $this->getRequest()->getPost();    
                    
        if (count($post) > 0) {
            
            if (!empty($post['password'])) {
                
                $getUserData = $this->userPlugin()->getUserModel()->getUserByPhoneNo($post['phoneNo']);
                
                if (count($getUserData) == 0) {
                    $newUserData = array (
                        'userRoll' => $post['userRoll'],
                        'name' => $post['name'],
                        'phoneNo' => $post['phoneNo'],
                        'password' => sha1($post['password']),
                        'accountStatus' => 'Active',
                        'deviceType' => 'Andorid',
                        'avaiTransBal' => 0,
                        'totalTransBal' => 0,
                        'balReq' => 0,
                    );            

                    $userEntity = $this->userPlugin()->getUserModel()->createUser($newUserData);

                    if($userEntity->Id > 1) {
                        $this->flashMessenger()->setNamespace('success');
                        $this->flashMessenger()->addMessage('New agent user created successfully.');
                    } else {
                        $this->flashMessenger()->setNamespace('success');
                        $this->flashMessenger()->addMessage('Internal Error.');
                    }
                } else {
                    $this->flashMessenger()->setNamespace('error');
                    $this->flashMessenger()->addMessage('User already exist.');
                }                
            } else {
                $this->flashMessenger()->setNamespace('error');
                $this->flashMessenger()->addMessage('Please enter password.');
            }            
        } else {
            $this->flashMessenger()->setNamespace('error');
            $this->flashMessenger()->addMessage('Invalid data.');
        }        
        return $this->redirect()->toUrl('/public/admin/agentUsers');
    }
    
}
