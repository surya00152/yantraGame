<?php

/**
 * @author Jack
 */

namespace User\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="transaction")
 */
class Transaction {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $Id;

    /**
     * @ORM\Column(length=11);
     */
    protected $agentId;

    /**
     * @ORM\Column(length=11);
     */
    protected $userId;

    /**
     * @ORM\Column(length=20);
     */
    protected $transBalance;

    /**
     * @ORM\Column(length=10);
     */
    protected $transType;

    /**
     * @ORM\Column(length=20);
     */
    protected $date;

    /**
     * Magic getter to expose protected properties.
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }

    /**
     * Populate from an array.
     * @param array $data
     */
    public function populate($data = array()) {
        foreach ($data as $key => $value) {
            if (property_exists(__class__, $key))
                $this->$key = $value;
        }
    }

}