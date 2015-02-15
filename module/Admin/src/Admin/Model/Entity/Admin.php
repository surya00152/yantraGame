<?php

/**
 * @author Jack
 */

namespace Admin\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="admin")
 */
class Admin
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $Id;

    /**
     * @ORM\Column(length=50);
     */
    protected $name;

    /**
     * @ORM\Column(length=50);
     */
    protected $userName;
    
    /**
     * @ORM\Column(length=255);
     */
    protected $password;
    
    /**
     * @ORM\Column(length=1);
     */
    protected $drawMode;
    
    /**
     * @ORM\Column(length=5);
     */
    protected $percentage;
    
    /**
     * @ORM\Column(length=2);
     */
    protected $manual;
    
    /**
     * @ORM\Column(length=1);
     */
    protected $isJackpot;
    
    /**
     * @ORM\Column(length=1);
     */
    protected $jackpotValue;
    
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