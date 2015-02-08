<?php

/**
 * @author Jack
 */

namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\View\Model\ViewModel,
    User\Comman\Validation;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 *  User Plugin
 */
class UserPlugin extends AbstractPlugin
{     
    /*
     * Get User Model
     */

    public function getAppService()
    {
        return $this->getController()->getServiceLocator()->get('Application\Service\Service');
    }
    
    /*
     * Get User Model
     */

    public function getUserModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\User');
    }
    
    /*
     * Get User Model
     */

    public function getDrowModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\DrowYantra');
    }
    
    /*
     * Get Notification Model
     */

    public function getNotificationModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\Notification');
    }
    
     /*
     * Get Transaction Model
     */

    public function getTransactionModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\Transaction');
    }
    
    /*
     * Get Ticket Model
     */

    public function getTicketModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\Ticket');
    }
    
    /*
     * Get Ticket Date Model
     */

    public function getTicketDateModel()
    {
        return $this->getController()->getServiceLocator()->get('User\Model\TicketDate');
    }
    
    /*
     * Check is user Login
     */

    public function isLogin($post)
    {
        $response = array();

        //Validate the User Information 
        $inputFilter = Validation::loginCheckValidation();
        $inputFilter->setData($post);
        if ($inputFilter->isValid()) {
            //check user is login
            $userId = $this->getUserModel()->isLoggedUser($post);
            if ($userId > 0) {
                $response['status'] = 'success';
                $response['userId'] = $userId;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }
        } else {
            $response['status'] = 'error';
            //User Validation fails
            foreach ($inputFilter->getInvalidInput() as $error) :
                $response['message'] = current($error->getMessages());
            endforeach;
        }

        return $response;
    }

    /*
     * User signup
     */

    public function signup($post)
    {
        $response = array();

        //Validate the User Information 
        $inputFilter = Validation::signupValidation();
        $inputFilter->setData($post);
        if ($inputFilter->isValid()) {

            //check phone No exist or not
            $userData = $this->getUserModel()->getUserByPhoneNo($post['phoneNo']);

            if (count($userData) == 0) {
                //Generate Password for user
                $password = $post['phoneNo'];//rand('0000', '999999999');
                $userData['phoneNo'] = $post['phoneNo'];
                $userData['password'] = sha1($password);
                $userData['name'] = $post['name'];
                $userData['deviceId'] = $post['deviceId'];
                $userData['userRoll'] = 'local';
                $userData['deviceType'] = 'Android';
                $userData['accountStatus'] = 'Active';
                $userData['avaiPurchaseBal'] = '50000';
                $userData['totalPurchaseBal'] = '50000';
                $userData['avaiWinBal'] = '0';
                $userData['totalWinBal'] = '0';
                $userData['avaiTransBal'] = '0';
                $userData['totalTransBal'] = '0';

                $userEntity = $this->getUserModel()->createUser($userData);
                if ($userEntity->Id > 0) {

                    $message = "Welcome To Jackpot\r\n Hi,".$userData['name']."\r\nYour User Name is:".$userData['phoneNo']." \r\n Your Password is:".$password;
                    //send SMS to user
                    $this->getAppService()->sendSMS($userData['phoneNo'], $message);

                    $response['status'] = 'success';
                    $response['message'] = 'Registration success.Password has been send in SMS.';
                    /*$response['data'] = $this->getUserModel()->getUserById($userEntity->Id);*/
                    
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Registration Fail.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Phone-No already exist.';
            }
        } else {
            $response['status'] = 'error';
            //User Validation fails
            foreach ($inputFilter->getInvalidInput() as $error) :
                $response['message'] = current($error->getMessages());
            endforeach;
        }
        return $response;
    }

    /*
     * User login
     */

    public function login($post)
    {
        $response = array();

        //Validate the User Information 
        $inputFilter = Validation::loginValidation();
        $inputFilter->setData($post);
        if ($inputFilter->isValid()) {

            //check login is valid
            $userData = $this->getUserModel()->userLogin($post);
            if (count($userData) > 0) {
                //check account status
                if ($userData['accountStatus'] == 'Deactive') {
                    $response['status'] = 'error';
                    $response['message'] = 'Your account is deactivated By Admin. Please contact to Admin.';
                } else {
                    $userData['deviceId'] = $post['deviceId'];
                    $userData['loginToken'] = sha1(time());
                    $this->getUserModel()->updateUser($userData['Id'], $userData);

                    $response['status'] = 'success';
                    if($userData['accountStatus'] == 'Notverify') {
                        $response['message'] = 'Login success.Please verify your account.';
                    } else {
                        $response['message'] = 'Login success.';
                    }
                    $response['data'] = $userData;
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Invalid Login.';
            }
        } else {
            $response['status'] = 'error';
            //User Validation fails
            foreach ($inputFilter->getInvalidInput() as $error) :
                $response['message'] = current($error->getMessages());
            endforeach;
        }
        return $response;
    }
    
    /*
     * Send Forgot Password Verify Code
     */

    public function forgotPassword($post)
    {
        $response = array();

        //check phone No exist or not
        $userData = $this->getUserModel()->getLocalUserByPhoneNo($post['phoneNo']);

        if (count($userData) > 0) {
                     
            $verifyCodeMatch = true;
            while ($verifyCodeMatch == true) {
                
                $verifyCode = rand('111111', '999999');
                $getUserInfo = $this->getUserModel()->passwordVerifyCodeExist($verifyCode);
                if (count($getUserInfo) == 0) {
                    $verifyCodeMatch = false;
                }
            }
            
            $updateUserData['passVerifyCode'] = $verifyCode;

            $this->getUserModel()->updateUser($userData['Id'], $updateUserData);

            $message = "Welcome To Jackpot\r\n Hi,".$userData['name']."\r\nYour verify code is:".$verifyCode;
            //send SMS to user
            $this->getAppService()->sendSMS($userData['phoneNo'], $message);
            
            $response['status'] = 'success';
            $response['message'] = 'Password Reset code send success.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Phone-No does not exist.Please check your Phone No.';
        }

        return $response;
    }
    
    /*
     * Update Forgot Password
     */

    public function updateForgotPassword($post)
    {
        $response = array();

        //check Verify Code exist or not
        $userData = $this->getUserModel()->passwordVerifyCodeExist($post['passVerifyCode']);

        if (count($userData) > 0) {
            //Account Password change.
            $updateUserData['password'] = sha1($post['password']);
            $updateUserData['passVerifyCode'] = NULL;

            $this->getUserModel()->updateUser($userData['Id'], $updateUserData);

            $response['status'] = 'success';
            $response['message'] = 'Password changed success.';
            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Password verify code not match.';
        }
        return $response;
    }
    
    /*
     * Change changePassword
     */

    public function changePassword($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        
        if (count($userData) > 0) {
            //check User Roll.
            if (!empty($post['oldPassword']) && !empty($post['newPassword'])) {
                //check old password
                if (sha1($post['oldPassword']) == $userData['password']) {
                    try {
                        //Update Password
                        $data = array(
                            'password' => sha1($post['newPassword'])
                        );
                        $this->getUserModel()->updateUser($userData['Id'],$data);
                        
                        $response['status'] = 'success';
                        $response['message'] = 'Password has been changed successfully.';
                        
                    } catch (\Exception $e) {
                        $response['status'] = 'error';
                        $response['message'] = 'Internal Server Problem.';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Old password not match.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Please enter old and new Password.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Get User Dashboard
     */

    public function getUserDashboard($post)
    {
        $response = array();

        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
              
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                $dashTime = $this->getAppService()->getUserDashboardTime();
                $time = $this->getAppService()->getDrawTime($dashTime);
                if(empty($time)) {
                    $time = '00:00:00';
                }
                $currentDate = $this->getAppService()->getDate();
                                             
                //get local user dashboard
                $response['status'] = 'success';
                $response['data']['yantra'] = $this->getAllYantraForUser($userData);
                $response['data']['drawYantra'] = $this->getDrowModel()->getAllDrowYantra($currentDate,$time);
                $response['data']['dashboardCommon']['balance'] = $userData['avaiPurchaseBal'];
                $response['data']['dashboardCommon']['endTime'] = $time;
                $response['data']['dashboardCommon']['currentTime'] = $this->getAppService()->getUserDashboardTime();
                $response['data']['dashboardCommon']['currentDate'] = $currentDate;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
//    /*
//     * Create new ticket
//     */
//
//    public function createTicket($post)
//    {
//        $log = "\r\n=========================";
//        $response = array();
//        //Get user details By token
//        $userData = $this->getUserModel()->getUserByToken($post['token']);
//        if (count($userData) > 0) {
//$currentDate = $this->getAppService()->getDate();
//
//$currentTime = isset($post['time'])?$post['time']:'not';
//$currentTime1 = $this->getAppService()->getTime();
//$drowTime = $this->getAppService()->getDrawTime();
//
//$log .= "\r\n$currentDate|$currentTime1|$currentTime| = $drowTime user: $userData[phoneNo]\r\n";
//
//            if ($userData['ticketTime'] == $post['time']) {
//                $response['bal'] = $userData['avaiPurchaseBal'];
//                $response['yantra'] = $this->getAllYantraForUser($userData);
//                $response['status'] = 'success';
//                $response['message'] = 'Ticket created successfully.';
//                return $response;
//            }
//            
//$log .= "\r\nCURRENT BAL : $userData[avaiPurchaseBal] \r\n";
//            //check User Roll.
//            if ($userData['userRoll'] == 'local') {
//                $post['ticket'] = json_decode($post['ticket']);
//                $totalQuantity = 0;
//$log .= "INTPUT -> ";
//                //count total price of selected yantra
//                foreach ($post['ticket'] as $ticket) {
//$log .= "$ticket->id : $ticket->val|";
//                    $totalQuantity += $ticket->val;
//                }
//                $totalPrice = $totalQuantity * 11;
//                //START TRANSACTION
//                $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');                    
//                
//                if ($totalPrice <= $userData['avaiPurchaseBal']){
//                  try {
//                        $em->getConnection()->beginTransaction();
//
//                        $currentDate = $this->getAppService()->getDate();
//                        $drowTime = $this->getAppService()->getDrawTime();
//                        if (empty($$drowTime)) {
//                            $drowTime = '00:00:00';
//                        }
//                        //check date records exist if not then create new if Yes then use Id
//                        $getTicketDate = $this->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);
//
//                        if (count($getTicketDate) == 0) {
//                            //create Date Records
//                            $dateData  = array (
//                                'userId' => $userData['Id'],
//                                'drawDate' => $currentDate,
//                                'openingBal' => $userData['avaiPurchaseBal'],
//                            );
//
//                            $getDateEntity = $this->getTicketDateModel()->createTicketDate($dateData);
//                            $dateId = $getDateEntity->Id;
//                        } else {
//                            $dateId = $getTicketDate['Id'];
//                        }
//$log .= "\r\nTAKE -> \r\n";                                
//                        //Create Ticket for seperate yantra
//                        foreach ($post['ticket'] as $ticket) {
//                        if($ticket->val > 0) {
//                                //check ticket exist or not
//                                $ticketData = $this->getTicketModel()->getTicket($dateId,$ticket->id,$drowTime);
//                                if(count($ticketData) == 0) {
//                                   //create ticket
//                                    $newTicket = array (
//                                        'dateId' => $dateId,
//                                        'time' => $drowTime,
//                                        'yantraId' => $ticket->id,
//                                        'quantity' => $ticket->val,
//                                        'status' => 0,
//                                        'totalPrice' => $ticket->val * 11,
//                                        'totalWin' => 0
//                                    );
//$log .= "\r\n \tCREATE : Y-$newTicket[yantraId] Q-$newTicket[quantity] P-$newTicket[totalPrice]";
//                                    $ticketData = $this->getTicketModel()->createTicket($newTicket);
//                                } else {
//                                    //update ticket
//                                    $updateTicket = array (
//                                        'quantity' => $ticketData['quantity'] + $ticket->val,
//                                        'totalPrice' => ($ticket->val * 11) + $ticketData['totalPrice'],
//                                    );
//$log .= "\r\n \tUPDATE : Y-$ticketData[yantraId] OQ-$ticketData[quantity] NQ-$ticket->val P-$updateTicket[totalPrice]";
//
//                                    $ticketData = $this->getTicketModel()->updateTicket($ticketData['Id'],$updateTicket);
//                                }
//                            }
//                        }
//                        //Cut user Bal to Purchase TIcket
//                        $updateUser['avaiPurchaseBal'] = $userData['avaiPurchaseBal'] - $totalPrice;
//                        $updateUser['ticketTime'] = $currentTime;//$post['time'];
//$log .= "\r\nNOW BAL : $updateUser[avaiPurchaseBal] \r\n";                        
//                        $this->getUserModel()->updateUser($userData['Id'],$updateUser);
//                        
//                        $response['bal'] = $updateUser['avaiPurchaseBal'];
//                        $response['yantra'] = $this->getAllYantraForUser($userData);
//                        $response['status'] = 'success';
//                        $response['message'] = 'Ticket created successfully.';
//
//                        $em->getConnection()->commit();
//                    } catch (\Exception $e) {
//                        $em->getConnection()->rollback();
//                        $response['status'] = 'error';
//                        $response['message'] = 'Internal Error. Please try agaign';
//                    }
//                    
//                } else {
//                    $response['status'] = 'error';
//                    $response['message'] = 'You do not have efficiant balance.';
//                }
//            } else {
//                $response['status'] = 'error';
//                $response['message'] = 'Access Denied.';
//            }            
//        } else {
//            $response['status'] = 'error';
//            $response['message'] = 'Login Fail.';
//        }
//        error_log($log,3,'creteticket.log');
//        return $response;
//    }
    
    /*
     * Create new ticket
     */

    public function createTicket($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        if (count($userData) > 0) {
            $dashboardTime = $this->getAppService()->getUserDashboardTime();
            $currentDate = $this->getAppService()->getDate();
            $drowTime = $this->getAppService()->getDrawTime();
            if (empty($drowTime)) {
                $drowTime = '24:00:00';
            }
            
            $realDate = new \DateTime($currentDate.' '.$dashboardTime);
            $realDate->modify("+1 min");  
            $realDate = $realDate->format('Y-m-d H:i:s');
            
            $compareDrawDate = new \DateTime($currentDate.' '.$drowTime);
            $compareDrawDate = $compareDrawDate->format('Y-m-d H:i:s');            
            
            if (strtotime($realDate) > strtotime($compareDrawDate)) {
                $response['status'] = 'error';
                $response['message'] = 'Ticket time out.';
                return $response;
            }
            
            if ($drowTime == '24:00:00') {
                $drowTime = '00:00:00';
            }
            
            
            if ($userData['ticketTime'] == $post['time']) {
                $response['bal'] = $userData['avaiPurchaseBal'];
                $response['yantra'] = $this->getAllYantraForUser($userData);
                $response['status'] = 'success';
                $response['message'] = 'Ticket created successfully.';
                return $response;
            }
            

            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                $post['ticket'] = json_decode($post['ticket']);
                $totalQuantity = 0;

                //count total price of selected yantra
                foreach ($post['ticket'] as $ticket) {

                    $totalQuantity += $ticket->val;
                }
                $totalPrice = $totalQuantity * 11;
                //START TRANSACTION
                $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');                    
                
                if ($totalPrice <= $userData['avaiPurchaseBal']){
                  try {
                        $em->getConnection()->beginTransaction();

                        //check date records exist if not then create new if Yes then use Id
                        $getTicketDate = $this->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                        if (count($getTicketDate) == 0) {
                            //create Date Records
                            $dateData  = array (
                                'userId' => $userData['Id'],
                                'drawDate' => $currentDate,
                                'openingBal' => $userData['avaiPurchaseBal'],
                            );

                            $getDateEntity = $this->getTicketDateModel()->createTicketDate($dateData);
                            $dateId = $getDateEntity->Id;
                        } else {
                            $dateId = $getTicketDate['Id'];
                        }

                        //Create Ticket for seperate yantra
                        foreach ($post['ticket'] as $ticket) {
                        if($ticket->val > 0) {
                                //check ticket exist or not
                                $ticketData = $this->getTicketModel()->getTicket($dateId,$ticket->id,$drowTime);
                                if(count($ticketData) == 0) {
                                   //create ticket
                                    $newTicket = array (
                                        'dateId' => $dateId,
                                        'time' => $drowTime,
                                        'yantraId' => $ticket->id,
                                        'quantity' => $ticket->val,
                                        'status' => 0,
                                        'totalPrice' => $ticket->val * 11,
                                        'totalWin' => 0
                                    );
                                    $ticketData = $this->getTicketModel()->createTicket($newTicket);
                                } else {
                                    //update ticket
                                    $updateTicket = array (
                                        'quantity' => $ticketData['quantity'] + $ticket->val,
                                        'totalPrice' => ($ticket->val * 11) + $ticketData['totalPrice'],
                                    );
                                    $ticketData = $this->getTicketModel()->updateTicket($ticketData['Id'],$updateTicket);
                                }
                            }
                        }
                        $getTime = $this->getAppService()->getTime();                      
                        //Cut user Bal to Purchase TIcket
                        $updateUser['avaiPurchaseBal'] = $userData['avaiPurchaseBal'] - $totalPrice;
                        $updateUser['ticketTime'] = isset($post['time'])?$post['time']:$getTime;

                        $this->getUserModel()->updateUser($userData['Id'],$updateUser);
                        
                        $response['bal'] = $updateUser['avaiPurchaseBal'];
                        $response['yantra'] = $this->getAllYantraForUser($userData);
                        $response['status'] = 'success';
                        $response['message'] = 'Ticket created successfully.';

                        $em->getConnection()->commit();
                    } catch (\Exception $e) {
                        $em->getConnection()->rollback();
                        $response['status'] = 'error';
                        $response['message'] = 'Internal Error. Please try agaign';
                    }
                    
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'You do not have efficiant balance.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Get drow status
     */

    public function getDrowStatus($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                //Get User Ticket Status
                $date = $this->getAppService()->getDate();
                $time = $this->getAppService()->getDrawTime();
                
                $dates = new \DateTime(date($date." ".$time));
                $dates->modify("-15 min");
                $time = $dates->format("H:i:s");
                $date = $dates->format("d-m-Y");
                
                $getTicket = $this->getTicketModel()->getTicketByUserId($date,$time,$userData['Id']);
                
                $drowDone = false;
                //Get Win or Loss Bal
                if (count($getTicket) > 0) {
                    $winBal = 0; $lossBal = 0;
                    foreach ($getTicket as $userTicket) {
                        $winBal = $winBal + $userTicket['totalWin'];
                        $lossBal = $lossBal + $userTicket['totalPrice'];
                        if ($userTicket['status'] == '1') {
                            $drowDone = true;
                        }
                    }
                }
                //Check Drow is Complete
                if (count($getTicket) == 0) {
                    $response['status'] = 'none';
                } else if ($drowDone == true) {
                    $response['status'] = 'success';
                    if ($winBal > 0) {
                        $response['drawStatus'] = 'win';
                        $response['price'] = $winBal;
                    } else {
                        $response['drawStatus'] = 'loss';
                        $response['price'] = $lossBal;
                    }
                    
                } else {
                    $response['status'] = 'repeat';
                }
                
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Get Purchase Report
     */

    public function getPurchaseReport($post,$isAdmin = false)
    {
        $response = array();
        
        if ($isAdmin == true && isset($post['Id'])) {
            $userData = $post;
        } else {
            //Get user details By token
            $userData = $this->getUserModel()->getUserByToken($post['token']);
        }
        
        
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                if(isset($post['date'])) {
                    $timeArray = array();
                    $reportArray = array();
                    $ticketData = $this->getTicketModel()->getPurchaseTicketByDate($post['date'],$userData['Id']);
                    if(count($ticketData) > 0) {
                        foreach($ticketData as $ticket) {
                            if(in_array($ticket['time'], $timeArray)) {
                                //get Time array key
                                $key = array_search($ticket['time'], $timeArray);
                            } else {
                                $timeArray[] = $ticket['time'];
                                $key = array_search($ticket['time'], $timeArray);
                            }
                            $reportArray[$key]['time'] = $ticket['time'];                            
                            $reportArray[$key][$ticket['yantraId']] = $ticket['totalPrice'];                 
                        }
                        //get total
                        foreach ($reportArray as $rKey => $report) {
                            $total = 0;
                            foreach ($report as $key => $val) {
                                if ($key !== 'time' && $key !== 'total') {
                                    $total = $total + $val;
                                }
                                
                            }
                            $reportArray[$rKey]['total'] = $total;
                        }
                    }
                    
                    $response['data'] = $reportArray;
                    $response['status'] = 'success';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Please select date.';
                }
                
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Get Daywise Report
     */
    
    public function getDaywiseReport($post,$isAdmin = false)
    {
        $response = array();
        
        if ($isAdmin == true && isset($post['Id'])) {
            $userData = $post;
        } else {
            //Get user details By token
            $userData = $this->getUserModel()->getUserByToken($post['token']);
        }
        
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                //Get User Ticket Status
                $daywiseReport = array();
                //get Day wise report
                $currentDate = $this->getAppService()->getDate();
                $daywiseReport = $this->getTicketModel()->getDaywiseReport($userData['Id'],$post['startDate'],$post['endDate']);
                if(count($daywiseReport) > 0) {
                    foreach ($daywiseReport as $rKey => $report) {
                        $daywiseReport[$rKey]['creditBal'] = 0;
                        $daywiseReport[$rKey]['debitBal'] = 0;
                        
                        $trasactionData = $this->getTransactionModel()->getTransactionDataByUserIdAndDate($userData['Id'],$report['drawDate']);
                        if (count($trasactionData) > 0 ) {
                            foreach ($trasactionData as $key => $transaction) {
                                if ($transaction['transType'] == 'Credit') {
                                    $daywiseReport[$rKey]['creditBal'] = $transaction['bal'];
                                } else {
                                    $daywiseReport[$rKey]['debitBal'] = $transaction['bal'];
                                }                            
                            }                       
                        }
                        if ($report['drawDate'] == $currentDate) {
                            $daywiseReport[$rKey]['closeBal'] = $userData['avaiPurchaseBal'];
                        }
                    }
                }
                $response['status'] = 'success';
                $response['data'] = $daywiseReport;
                
            } else {
                //Get User Ticket Status
                $daywiseReport = array();
                //get Day wise report
                $currentDate = $this->getAppService()->getDate();
                $daywiseReport = $this->getTransactionModel()->getDaywiseReport($userData['Id'],$post['startDate'],$post['endDate']);
                
                if(count($daywiseReport) > 0) {
                    foreach ($daywiseReport as $rKey => $report) {
                        $daywiseReport[$rKey]['creditBal'] = 0;
                        $daywiseReport[$rKey]['debitBal'] = 0;
                        
                        if ($report['transDate'] == $currentDate) {
                            $daywiseReport[$rKey]['closeBal'] = $userData['avaiTransBal'];
                        }
                    }
                }
                $response['status'] = 'success';
                $response['data'] = $daywiseReport;
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Get Showwise Report
     */

    public function getShowwiseReport($post,$isAdmin = false)
    {
        $response = array();
        if ($isAdmin == true && isset($post['Id'])) {
            $userData = $post;
        } else {
            //Get user details By token
            $userData = $this->getUserModel()->getUserByToken($post['token']);
        }
        
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                if(isset($post['date'])) {
                    $ticketData = $this->getTicketModel()->getShowwiseReport($userData['Id'],$post['date']);
                    
                    if(count($ticketData) > 0) {
                        foreach($ticketData as $tKey => $ticket) {
                            
                            $drawYantra = $this->getDrowModel()->drowExistByDateTime($ticket['date'].' '.$ticket['showTime']);
                            if (count($drawYantra) > 0) {
                                $jackpot = ($drawYantra[0]['jackpot'] == 1)?0:$drawYantra[0]['jackpot'];
                                $symbol = $drawYantra[0]['yantraId']."-Jackpot:".$jackpot;
                            } else {
                                $symbol = 'Running';
                            }
                            $ticketData[$tKey]['symbol'] = $symbol;
                        }
                    }
                    $response['data'] = $ticketData;
                    $response['status'] = 'success';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Please select date.';
                }
                
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Access Denied.';
            }            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Login Fail.';
        }
        return $response;
    }
    
    /*
     * Request For Transfer Balance (Agent)
     */

    public function transferBalance($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        //Get Local user details By PhoneNo
        $localUserData = $this->getUserModel()->getLocalUserByPhoneNo($post['phoneNo']);
        
        //Check Request
        if ($post['type'] == 'debit') {
            if (count($localUserData)) {
                //check account status
                if ($localUserData['accountStatus'] == 'Active') {
                    //check request Bal
                    if ($localUserData['avaiPurchaseBal'] >= $post['balance']) {
                        //generate bal request code
                        $transferCodeMatch = true;
                        while ($transferCodeMatch == true) {
                            
                            $transferCode = rand('111111', '999999');
                            $getUserInfo = $this->getUserModel()->passwordVerifyCodeExist($transferCode);
                            if (count($getUserInfo) == 0) {
                                $transferCodeMatch = false;
                            }
                        }
                        try {
                            //set transfer code
                            $updateUserData = array (
                                'balReqCode' => $transferCode,
                                'balReq' => $post['balance']
                            );
                            $this->getUserModel()->updateUser($localUserData['Id'], $updateUserData);
                            
                            //set notification message
                            $notificationData = array (
                                'reqFrom' => $userData['Id'],
                                'reqTo' => $localUserData['Id'],
                                'requestedName' => $userData['name'],
                                'message' => 'Send request by Agent for debit your bal : '.$post['balance'].'. Your transfer Code is:'.$transferCode,
                                'date' => $this->getAppService()->getDateTime()
                            );
                            $this->getNotificationModel()->createNotification($notificationData);
                            
                            $response['status'] = 'success';
                            $response['message'] = 'Transfer code send successfully.';
                                                        
                        } catch (\Exception $e) {
                            $response['status'] = 'error';
                            $response['message'] = 'Something went wrong : Please try agaign.';
                        }
                        
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = $post['phoneNo'].': User does not have efficient balance.';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = $post['phoneNo'].': User account deactivated by Admin.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = $post['phoneNo'].': User not available.';
            }
        } else {
            //Transfer credit Balance
            if ($userData['avaiTransBal'] >= $post['balance']) {
                if (count($localUserData) > 0) {
                    if ($localUserData['accountStatus'] == 'Active') {
                        //START TRANSACTION
                        $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
                        try {
                            $em->getConnection()->beginTransaction();
                            
                            //check date records exist if not then create new if Yes then use Id
                            $getTicketDate = $this->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                            if (count($getTicketDate) == 0) {
                                //create Date Records
                                $dateData  = array (
                                    'userId' => $userData['Id'],
                                    'drawDate' => $currentDate,
                                    'openingBal' => $userData['avaiTransBal'],
                                );

                                $getDateEntity = $this->getTicketDateModel()->createTicketDate($dateData);
                                $dateId = $getDateEntity->Id;
                            } else {
                                $dateId = $getTicketDate['Id'];
                            }
                            
                            $localUser = array (
                                'avaiPurchaseBal' => $post['balance'],
                                'totalWinBal' => 0,
                            );
                            //Update Local user balance.
                            $this->getUserModel()->updateUserBal($localUserData['Id'],$localUser);
                            
                            
                            $agentUser = array (
                                'avaiTransBal' => $userData['avaiTransBal'] - $post['balance'],
                            );
                            //Update Agent user balance.
                            $this->getUserModel()->updateUser($userData['Id'],$agentUser);
                            
                            
                            $notificationData = array (
                                'reqFrom' => $userData['Id'],
                                'reqTo' => $localUserData['Id'],
                                'requestedName' => $userData['name'],
                                'message' => 'Your account credited by Agent. Transfer credit is: '.$post['balance'],
                                'date' => $this->getAppService()->getDateTime()
                            );                            
                            //set notification message
                            $this->getNotificationModel()->createNotification($notificationData);
                            
                            $transactionData = array (
                                'dateId' => $dateId,
                                'userId' => $userData['Id'],
                                'agentId' => $localUserData['Id'],
                                'transBalance' => $post['balance'],
                                'transType' => 'Credit',
                                'time' => $this->getAppService()->getTime()
                            );
                            //create Transaction report
                            $this->getTransactionModel()->createTransaction($transactionData);
                            
                            $response['status'] = 'success';
                            $response['message'] = 'Balance transfer successfully.';

                            $em->getConnection()->commit();
                        } catch (\Exception $e) {
                            $em->getConnection()->rollback();
                            $response['status'] = 'error';
                            $response['message'] = 'Internal Error. Please try agaign.';
                        }
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = $post['phoneNo'].': User account deactivated by Admin.';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = $post['phoneNo'].': User not available.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'you do have not efficient balance.';
            }
        }
        return $response;
    }
    
    /*
     * Request For Debit Balance By Transfer Code (Agent)
     */

    public function sendTransferCode($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        //Get Local user details By PhoneNo
        $localUserData = $this->getUserModel()->getLocalUserByTransferCode($post['code']);
        
        //Check Request Code
        if (count($localUserData)) {
            //check account status
            if ($localUserData['accountStatus'] == 'Active') {
                //check request Bal
                if ($localUserData['avaiPurchaseBal'] >= $localUserData['balReq']) {
                    
                    //START TRANSACTION
                    $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
                    try {
                        $em->getConnection()->beginTransaction();
                        
                        //check date records exist if not then create new if Yes then use Id
                        $getTicketDate = $this->getTicketDateModel()->getTicketDate($userData['Id'],$currentDate);

                        if (count($getTicketDate) == 0) {
                            //create Date Records
                            $dateData  = array (
                                'userId' => $userData['Id'],
                                'drawDate' => $currentDate,
                                'openingBal' => $userData['avaiTransBal'],
                            );

                            $getDateEntity = $this->getTicketDateModel()->createTicketDate($dateData);
                            $dateId = $getDateEntity->Id;
                        } else {
                            $dateId = $getTicketDate['Id'];
                        }
                            
                        $localUser = array (
                            'avaiPurchaseBal' => $localUserData['avaiPurchaseBal'] - $localUserData['balReq'],
                            'balReqCode' => 0,
                            'balReq' => 0
                        );
                        //cut balance from Local user account
                        $this->getUserModel()->updateUser($localUserData['Id'],$localUser);


                        $agentUser = array (
                            'avaiTransBal' => $userData['avaiTransBal'] + $localUserData['balReq'],
                        );
                        //Update Agent user balance.
                        $this->getUserModel()->updateUser($userData['Id'],$agentUser);


                        $notificationData = array (
                            'reqFrom' => $userData['Id'],
                            'reqTo' => $localUserData['Id'],
                            'requestedName' => $userData['name'],
                            'message' => 'Your balance will be transfer by Agent. Transfer credit is: '.$localUserData['balReq'],
                            'date' => $this->getAppService()->getDateTime()
                        );                            
                        //set notification message
                        $this->getNotificationModel()->createNotification($notificationData);

                        $transactionData = array (
                            'dateId' => $dateId,
                            'userId' => $userData['Id'],
                            'agentId' => $localUserData['Id'],
                            'transBalance' => $localUserData['balReq'],
                            'transType' => 'Debit',
                            'time' => $this->getAppService()->getTime()
                        );
                        //create Transaction report
                        $this->getTransactionModel()->createTransaction($transactionData);

                        $response['status'] = 'success';
                        $response['message'] = 'Balance Debit successfully From : '.$localUserData['phoneNo'];

                        $em->getConnection()->commit();
                    } catch (\Exception $e) {
                        $em->getConnection()->rollback();
                        $response['status'] = 'error';
                        $response['message'] = 'Internal Error. Please try agaign.';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = $post['phoneNo'].': User does not have efficient balance.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = $post['phoneNo'].': User account deactivated by Admin.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Request code not found. If you fail more time then your account will be Block.';
        }
        
        return $response;
    }
    
    /*
     * Get Notification 
     */

    public function getNotification($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        
        //Check Request Code
        if (count($userData)) {
            try {
                $response['status'] = 'success';
                $response['data'] = $this->getNotificationModel()->getNotification($userData['Id']);
            } catch(\Exception $e) {
                $response['status'] = 'error';
                $response['message'] = 'Something went wrong.Please try agaign.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Access denied.';
        }        
        return $response;
    }
    
    /*
     * Get Transaction 
     */

    public function transactionReport($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        
        //Check Request Code
        if (count($userData)) {
            try {
                $response['status'] = 'success';
                $response['data'] = $this->getTransactionModel()->getTransactionReport($userData['Id'],$post['date']);
            } catch(\Exception $e) {
                $response['status'] = 'error';
                $response['message'] = 'Something went wrong.Please try agaign.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Access denied.';
        }
        
        return $response;
    }
    
    
    /*
     * UDF FOR GET ALL USER'S YANTRA DATA
     */
    protected function getAllYantraForUser($userData) {
        /*$time = $this->getAppService()->getDrawTime();
        if(empty($time)) {
            $time = '00:00:00';
        }*/
        //$currentDate = $this->getAppService()->getDate();
        
        $allYantra = $this->getController()->adminPlugin()->getAllYantra();
        
        //Get current Drow user ticket
        /*$userTicketsData = $this->getTicketModel()->getTicketByUserId($currentDate,$time,$userData['Id']);
        
        foreach ($allYantra as $yKey => $yantra) {
            if (count($userTicketsData) > 0) {
                $inside = false;
                foreach ($userTicketsData as $tKey => $ticket) {
                    if($yantra['Id'] == $ticket['yantraId']) {
                        $allYantra[$yKey]['quantity'] = empty($ticket['quantity'])?'0':$ticket['quantity'];
                        $inside = true;
                        break;
                    }
                }
                if($inside == false) {
                    $allYantra[$yKey]['quantity'] = '0';
                }                
            } else {
                $allYantra[$yKey]['quantity'] = '0';
            }
        }*/
        return $allYantra;
    }
}

