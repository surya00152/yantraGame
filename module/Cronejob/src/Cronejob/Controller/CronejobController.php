<?php

/**
 * @author Jack
 */

namespace Cronejob\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * Cronejob Controller
 */
class CronejobController extends AbstractActionController
{
    /*
     * Admin Login
     */
    public function drawNowAction()
    {
        //get Draw Time & Date
        $drawTime = $this->userPlugin()->getAppService()->getDrawTime();
        $drawDate = $this->userPlugin()->getAppService()->getDate();
        //$drawTime = '01:15:00';
        if (empty($drawTime)) {
            $drawTime = '00:00:00';
            $drawDate = new \DateTime("$drawDate");//$this->userPlugin()->getAppService()->getDate(true);
            $drawDate->modify("-1 day");
            $drawDate = $drawDate->format("d-m-Y");
        }
                
        //Get Drow Mode
        $drawMode = $this->adminPlugin()->getAdminModel()->getDrawMode();
        $jackpot = 1;
        if($drawMode['drawMode'] == 1) {
            //minimum rate selected yantra
            $yantraId = $this->getMinModeYantraId($drawDate,$drawTime);
            
            if($drawMode['isJackpot'] == 1 && $drawMode['jackpotValue'] > 1) {
                $jackpot = $drawMode['jackpotValue'];
            }
            
        } else if($drawMode['drawMode'] == 2) {
            //percentage mode selected yanta
            $yantraId = $this->getPercentageModeYantraId($drawDate,$drawTime,$drawMode['percentage']);
            
        } else if($drawMode['drawMode'] == 3) {
            //Manual mode selected yantra
            $yantraId = $drawMode['manual'];
            
            if($drawMode['isJackpot'] == 1 && $drawMode['jackpotValue'] > 1) {
                $jackpot = $drawMode['jackpotValue'];
            }
        }
        //check drow entry is exist
        $drowExist = $this->userPlugin()->getDrowModel()->drowExistByDateTime($drawDate.' '.$drawTime);
        
        if(count($drowExist) == 0) {
            
            /******PL DETAILS********/
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
                    $getYantraRateList[$key]['jackpotWinPrice'] = $rate['winPrice'] * $jackpot;
                    $getYantraRateList[$key]['jackpotPL'] = $totalRate['totalPrice'] - $getYantraRateList[$key]['jackpotWinPrice'];
                }

                foreach ($getYantraRateList as $key => $rate) {
                    $newYantraRate[$rate['yantraId']] = $rate;
                } 
                
                if ($jackpot > 1) {
                    $pl = isset($newYantraRate[$yantraId]['jackpotPL'])?$newYantraRate[$yantraId]['jackpotPL']:$totalRate['totalPrice'];
                    $winPrice = isset($newYantraRate[$yantraId]['jackpotWinPrice'])?$newYantraRate[$yantraId]['jackpotWinPrice']:0;
                    $purchasePrice = isset($totalRate['totalPrice'])?$totalRate['totalPrice']:0;
                } else {
                    $pl = isset($newYantraRate[$yantraId]['PL'])?$newYantraRate[$yantraId]['PL']:$totalRate['totalPrice'];
                    $winPrice = isset($newYantraRate[$yantraId]['winPrice'])?$newYantraRate[$yantraId]['winPrice']:0;
                    $purchasePrice = isset($totalRate['totalPrice'])?$totalRate['totalPrice']:0;
                }                
            } else {
                $pl = 0;
                $winPrice = 0;
                $purchasePrice = 0;
            }        

            /************************/
            
            //add drow yantra deletails
            $newDrowData = array (
                'yantraId' => $yantraId,
                'drawTime' => $drawDate.' '.$drawTime,
                'jackpot' => $jackpot,
                'pl' => $pl,
                'winPrice' => $winPrice,
                'purchase' => $purchasePrice
            );
            
            $this->userPlugin()->getDrowModel()->insertDrawYantra($newDrowData);
            
            //Get Selected Yantra's User
            $getYantraRateList = $this->userPlugin()->getTicketModel()->getTicketByYantraId($drawDate,$drawTime,$yantraId);
            
            if(count($getYantraRateList) > 0) {
                //update user credit details (win user)
                foreach ($getYantraRateList as $detail) {
                    //update user details
                    $userUpdate = array (
                        'avaiPurchaseBal' => $detail['quantity'] * 100 * $jackpot,
                        'totalWinBal' => $detail['quantity'] * 100 * $jackpot,
                    );
                    
                    //update user profile balance
                    $this->userPlugin()->getUserModel()->updateUserBal($detail['userId'],$userUpdate);
                    //update ticket bal
                    $updateTicket = array (
                        'totalWin' => $detail['quantity'] * 100 * $jackpot,
                    );
                    $this->userPlugin()->getTicketModel()->updateTicket($detail['Id'],$updateTicket);
                }
            } 

            $getDateDetails = $this->userPlugin()->getTicketDateModel()->getDateTicketByDate($drawDate);
            if(count($getDateDetails) > 0) {
                foreach ($getDateDetails as $dateDrawData) {
                    //Update user's ticket status to 1 (complete drow)
                    $this->userPlugin()->getTicketModel()->updateTicketStatus($drawTime,$dateDrawData['Id'],'1');
                    
                }    
            }
        }
        //update all user's Closing bal
        if ($drawTime == '00:00:00') {
            //get all user tickets by date
            $drowDateData = $this->userPlugin()->getTicketDateModel()->getUserDataByTicketDate($drawDate);
            if (count($drowDateData) > 0) {
                foreach ($drowDateData as $key => $dateData) {
                    if ($dateData['userRoll'] == 'local') {
                        $updateDateData = array ('closeBal' => $dateData['avaiPurchaseBal']);
                    } else {
                        $updateDateData = array ('closeBal' => $dateData['avaiTransBal']);
                    }
                    $this->userPlugin()->getTicketDateModel()->updateDateTicket($dateData['Id'],$updateDateData);
                }
            }
        }
        exit('END CRONE-JOB');
    }
    
    /*
     * Get min mode yantra Id
     */
    public function getMinModeYantraId($drawDate,$drawTime)
    {
        $yantraId = 0;
        $getYantraRateList = $this->userPlugin()->getTicketModel()->getYantraRate($drawDate,$drawTime);
        
        if(count($getYantraRateList) == 10) {
            /* If All Selected */
            //Find Min Value From All Records
            $minQuantity = min($this->array_column($getYantraRateList, 'quantity'));
            //get yantra Id by min quantity
            foreach ($getYantraRateList as $details) {
                if ($details['quantity'] == $minQuantity) {
                    $yantraId = $details['yantraId'];
                    break;
                }
            }
        } else if (count($getYantraRateList) < 10 &&  count($getYantraRateList) > 0) {
            /* If any One selected */
            //Get selected Yantra Id
            $selectedYantraId = $this->array_column($getYantraRateList, 'yantraId');
            
            //Get all Yantra Ids
            $allYantra = $this->adminPlugin()->getYantraModel()->getAllYantraIds();
            //Convert Single Array
            $allYantraIds = $this->array_column($allYantra, 'Id');
            //get unselected Ids
            $unselectedIds = array_diff($allYantraIds,$selectedYantraId);
            //Get random yantraId from Unselected yantra Ids
            $key = array_rand($unselectedIds);
            
            $yantraId = $unselectedIds[$key];
            
        } else {
            /* If not selected any */
            //Get all Yantra Ids
            $allYantra = $this->adminPlugin()->getYantraModel()->getAllYantraIds();
            //Convert Single Array
            $allYantraIds = $this->array_column($allYantra, 'Id');
            
            //Get random yantraId from All yantra Ids
            $key = array_rand($allYantraIds);
            
            $yantraId = $allYantraIds[$key];
        }
        return $yantraId;
    }
    
    /*
     * Get min mode yantra Id
     */
    public function getPercentageModeYantraId($drawDate,$drawTime,$percentage)
    {
        $yantraId = 0;
        $getYantraRateList = $this->userPlugin()->getTicketModel()->getYantraRate($drawDate,$drawTime);
        
        if(count($getYantraRateList) > 0) {
            /* If Selected any */
            $totalQuantity = 0;
            //count all yantra total Quantity
            foreach ($getYantraRateList as $details) {
                $totalQuantity += (int) $details['quantity'];
            }
            //total purchase rate
            $recieveAmout = $totalQuantity * 11;
            /* Percentage Wise Profit */
            $profit = $totalQuantity * 11 * $percentage / 100;
            $found = false;
            foreach ($getYantraRateList as $details) {
                $compareBal = $recieveAmout - ($details['quantity'] * 100);
                if ($compareBal >= $profit) {
                    $found = true;
                    $yantraId = $details['yantraId'];
                    break;
                }
            }
            /* Near Wise Profit */
            $total = 0;
            if ($found == false) {
               foreach ($getYantraRateList as $details) {
                    $compareBal = $recieveAmout - ($details['quantity'] * 100);
                    if ($compareBal > $total) {
                        $found = true;
                        $total = $compareBal;
                        $yantraId = $details['yantraId'];
                    }                    
                }
            }
            
            /* Automaitcally  Wise Profit */
            if($found == false) {
                //Get selected Yantra Id
                $selectedYantraId = $this->array_column($getYantraRateList, 'yantraId');

                //Get all Yantra Ids
                $allYantra = $this->adminPlugin()->getYantraModel()->getAllYantraIds();
                //Convert Single Array
                $allYantraIds = $this->array_column($allYantra, 'Id');
                //get unselected Ids
                $unselectedIds = array_diff($allYantraIds,$selectedYantraId);
                //Get random yantraId from Unselected yantra Ids
                $key = array_rand($unselectedIds);

                $yantraId = $unselectedIds[$key];
            }
        } else {
            /* If not selected any */
            //Get all Yantra Ids
            $allYantra = $this->adminPlugin()->getYantraModel()->getAllYantraIds();
            //Convert Single Array
            $allYantraIds = $this->array_column($allYantra, 'Id');
            
            //Get random yantraId from All yantra Ids
            $key = array_rand($allYantraIds);
            
            $yantraId = $allYantraIds[$key];
        }
        return $yantraId;
    }
    
    public function array_column($array,$key){
        $res = array();
        foreach($array as $val){
            if(isset($val[$key])) {
                $res[] = $val[$key];
            }
        }
        return $res;
    }
    
    public function removeDataAction() {
        $date = $this->userPlugin()->getAppService()->getDate();
        
        //-7 Days
        $currentDate = new \DateTime(date($date));
        $currentDate->modify("-7 day");
        $sortTermDate = $currentDate->format('d-m-Y');
        
        //delete transaction AND data AND ticket DATA
        $this->userPlugin()->getTicketDateModel()->deleteDataByLowerDate($sortTermDate);
        
        //delete notification data
        $this->userPlugin()->getNotificationModel()->deleteNotificationByLowerDate($sortTermDate);
        
        
        // -1 Month
        $currentDate = new \DateTime(date($date));
        $currentDate->modify("-30 day");
        $sortTermDate = $currentDate->format('d-m-Y');
        
        //draw data removed
        $this->userPlugin()->getDrowModel()->deleteDrawByLowerDate($sortTermDate);
        
        exit('Data Removed');
    }
}
