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
        if (empty($drawTime)) {
            $drawTime = '00:00:00';
        }
        
        $drawDate = $this->userPlugin()->getAppService()->getDate();
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
            //add drow yantra deletails
            $newDrowData = array (
                'yantraId' => $yantraId,
                'drawTime' => $drawDate.' '.$drawTime,
                'jackpot' => $jackpot
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
                    $updateDateData = array ('closeBal' => $dateData['avaiPurchaseBal']);
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
}
