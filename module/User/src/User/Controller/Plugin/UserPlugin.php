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
        $userData = $this->getUserModel()->getUserByPhoneNo($post['phoneNo']);

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
                $time = $this->getAppService()->getDrawTime();
                if(empty($time)) {
                    $time = '00:00:00';
                }
                $currentDate = $this->getAppService()->getDate();
                //get local user dashboard
                $response['status'] = 'success';
                $response['data']['yantra'] = $this->getAllYantraForUser($userData);
                $response['data']['drawYantra'] = $this->getDrowModel()->getAllDrowYantra($currentDate);
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
    
    /*
     * Create new ticket
     */

    public function createTicket($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        if (count($userData) > 0) {
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

                        $currentDate = $this->getAppService()->getDate();
                        $drowTime = $this->getAppService()->getDrawTime();
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
                        //Cut user Bal to Purchase TIcket
                        $updateUser['avaiPurchaseBal'] = $userData['avaiPurchaseBal'] - $totalPrice;
                        $this->getUserModel()->updateUser($userData['Id'],$updateUser);
                        
                        $response['bal'] = $updateUser['avaiPurchaseBal'];
                        
                        $response['yantra'] = $this->getAllYantraForUser($userData);
                        $response['status'] = 'success';
                        $response['message'] = 'Ticket created successfully.';

                        $em->getConnection()->commit();
                    } catch (\Exception $e) {
                        $em->getConnection()->rollback();
                        $response['status'] = 'error';
                        $response['message'] = 'Internal Server Error.';
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

    public function getPurchaseReport($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
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
    
    public function getDaywiseReport($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                //Get User Ticket Status
                $daywiseReport = array();
                //get Day wise report
                $daywiseReport = $this->getTicketModel()->getDaywiseReport($userData['Id'],$post['startDate'],$post['endDate']);
                
                if(count($daywiseReport) > 0) {
                    foreach ($daywiseReport as $rKey => $report) {
                        $daywiseReport[$rKey]['creditBal'] = 0;
                        $daywiseReport[$rKey]['debitBal'] = 0;
                    }
                }
                $response['status'] = 'success';
                $response['data'] = $daywiseReport;
                
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
     * Get Showwise Report
     */

    public function getShowwiseReport($post)
    {
        $response = array();
        //Get user details By token
        $userData = $this->getUserModel()->getUserByToken($post['token']);
        if (count($userData) > 0) {
            //check User Roll.
            if ($userData['userRoll'] == 'local') {
                if(isset($post['date'])) {
                    $ticketData = $this->getTicketModel()->getShowwiseReport($userData['Id'],$post['date']);
                    
                    if(count($ticketData) > 0) {
                        foreach($ticketData as $tKey => $ticket) {
                            $jackpot = ($ticket['jackpot'] == 1) ? 0 : $ticket['jackpot'];
                            $ticketData[$tKey]['symbol'] = $ticket['yantraId']."-Jackpot:".$jackpot;                            
                            unset($ticketData[$tKey]['yantraId']);
                            unset($ticketData[$tKey]['jackpot']);
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

