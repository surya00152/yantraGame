<?php

/*
 * @author Jack
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Service implements ServiceManagerAwareInterface {

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     * @return Form
     */
    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        return $this;
    }

     /*
     * Send SMS to User
     * @param str $phoneNo
     * @param str $message
     */
    public function sendSMS($phoneNo, $message)
    {
        try {
            //Send the SMS
            if (!empty($phoneNo) && !empty($message)) {
                $account_sid = 'ACd46ef5e013af4482efe5e0910142d6a3'; 
                $auth_token = '7216bf1f3eee4ff95adb85376bd0f880'; 
                $client = new \Services_Twilio($account_sid, $auth_token); 

                $res = $client->account->messages->create(array(  
                    "From" => "+14692754089",
                    "To" => '+91'.$phoneNo,
                    "Body" => $message
                ));
            }
        } catch (\Exception $e) {
            //Get Error Message
            return false;
        }
    }
    
     /*
     * Get current Date in india timezone
     */
    public function getDate($modify = false)
    {
        $date = new \DateTIme('NOW');
        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
        
        if ($modify == true) {
            $date = $date->modify("-1 day");
        }
        
        return $date = $date->format("d-m-Y");
            
            
    }
    
     /*
     * Get current Time in india timezone
     */
    public function getTime()
    {
        $date = new \DateTIme('NOW');
        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
        $date = $date->modify("-55 second");
              
        return $date = $date->format("H:i:s");
    }
    
    /*
     * Get current Time in india timezone
     */
    public function getUserDashboardTime()
    {
        $date = new \DateTIme('NOW');
        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
        $date = $date->modify("-59 second");
              
        return $date = $date->format("H:i:s");
    }
    
    /*
     * Get current DateTime in india timezone
     */
    public function getDateTime()
    {
        $date = new \DateTIme('NOW');
        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
        return $date = $date->format("d-m-Y H:i:s");
    }
    
    /*
     * Get Current Draw DateTime in india timezone
     */
    public function getDrawTime($currentTime = null)
    {
        $date = new \DateTIme('NOW');
        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
        
        $currentDate = $this->getDate();
        if ($currentTime == null) {
            $currentTime = $this->getTime();
        }
        
        $minTimeArray =  $this->getTimesArray($currentDate);
        
        foreach ($minTimeArray as $time) {
            if (strtotime($time) > strtotime($currentTime)) {
                return $time;
            }
        }
    }
    
    /*
     * Get 15 min diff Time array of FULL DAY
     */
    public function getTimesArray($day)
    {
        $res = array();
        $startTime = date(strtotime($day . " 00:00"));
        $endTime = date(strtotime($day . " 23:45"));

        $timeDiff = round(($endTime - $startTime) / 60 / 60);

        $startHour = date("G", $startTime);
        $endHour = $startHour + $timeDiff;

        for ($i = $startHour; $i <= $endHour; $i++) {
            for ($j = 0; $j <= 45; $j+=15) {
                $time = $i . ":" . str_pad($j, 2, '0', STR_PAD_LEFT);
                $res[] = (date(strtotime($day . " " . $time)) <= $endTime) ? date("H:i:s", strtotime($day . " " . $time)) : "";
            }
        }
        return array_filter($res);
    }

}
