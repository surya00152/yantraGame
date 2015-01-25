<?php

/**
 * @author Jack
 */

namespace User\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $Id;

    /**
     * @ORM\Column(length=15);
     */
    protected $userRoll;

    /**
     * @ORM\Column(length=20);
     */
    protected $name;

    /**
     * @ORM\Column(length=11);
     */
    protected $phoneNo;

    /**
     * @ORM\Column(length=200);
     */
    protected $password;

    /**
     * @ORM\Column(length=10);
     */
    protected $verifyCode;
    
    /**
     * @ORM\Column(length=10);
     */
    protected $passVerifyCode;
    
    /**
     * @ORM\Column(length=20)
     */
    protected $accountStatus;

    /**
     * @ORM\Column(length=200)
     */
    protected $deviceId;

    /**
     * @ORM\Column(length=20);
     */
    protected $deviceType;

    /**
     * @ORM\Column(length=30);
     */
    protected $balReqCode;

    /**
     * @ORM\Column(length=50);
     */
    protected $avaiPurchaseBal;

    /**
     * @ORM\Column(length=50);
     */
    protected $totalPurchaseBal;

    /**
     * @ORM\Column(length=50);
     */
    protected $avaiWinBal;

    /**
     * @ORM\Column(length=50);
     */
    protected $totalWinBal;

    /**
     * @ORM\Column(length=50);
     */
    protected $avaiTransBal;

    /**
     * @ORM\Column(length=30);
     */
    protected $totalTranBal;

    /**
     * @ORM\Column(length=255);
     */
    protected $loginToken;

    /**
     * Magic getter to expose protected properties.
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Populate from an array.
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach ($data as $key => $value) {
            if (property_exists(__class__, $key))
                $this->$key = $value;
        }
    }

}