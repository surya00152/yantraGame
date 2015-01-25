<?php

/**
 * @author Samier Sompura <>
 */

namespace Api\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Msgrecipient Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="msgrecipient")	
 */

class Msgrecipient
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer");
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
 	 * @ORM\Column(length=11);
	 */
	protected $messageId;
	
	/**
	 * @ORM\Column(length=11);
	 */
	protected $receiverId;
	
	/**
	 * @ORM\Column(length=1)
	 */
	protected $deleteStatus;
	
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