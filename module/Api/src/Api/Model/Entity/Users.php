<?php

/**
 * @author Samier Sompura <>
 */

namespace Api\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="users")	
 */

class Users
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer");
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(length=100)
	 */
	protected $user;
	
	/**
	 * @ORM\Column(length=32)
	 */
	protected $firstName;
	
	/**
	 * @ORM\Column(length=32)
	 */
	protected $lastName;
	
	/**
	 * @ORM\Column(length=50)
	 */
	protected $city;
		
	/**
	 * @ORM\Column(length=50)
	 */
	protected $state;
		
	/**
	 * @ORM\Column(type="text")
	 */
	protected $Country;
		
	/**
	 * @ORM\Column(length=50)
	 */
	protected $email;
	
	/**
	 * @ORM\Column(length=40)
	 */
	protected $password;
	
	/**
	 * @ORM\Column(length=25)
	 */
	protected $timeZone;
	
	/**
	 * @ORM\Column(length=20)
	 */
	protected $phoneSMS;
	
	/**
	 * @ORM\Column(length=30)
	 */
	protected $Image;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $dateCreated;
	
	/**
	 * @ORM\Column(length=50)
	 */
	protected $company;
	
	/**
	 * @ORM\Column(type="text")
	 */
	protected $title;
	
	
	
	/**
	 * @ORM\Column(length=10)
	 */
	protected $languageCode;
	
	/**
	 * @ORM\Column(length=1)
	 */
	protected $status;
		
	/**
	 * @ORM\Column(length=1)
	 */
	protected $preUserStatus;
	
	/**
	 * @ORM\Column(length=50)
	 */
	protected $random;
	
	/**
	 * @ORM\Column(length=11)
	 */
	protected $balance;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $lastLogin;
	
	/**
	 * @ORM\Column(length=100)
	 */
	protected $appName;
	
	/**
	 * @ORM\Column(length=8)
	 */
	protected $invitationCode;

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
		foreach ($data as $key => $value)
		{
			if (property_exists (__class__,$key)) 
				$this->$key  = $value;
		}	
	}
}