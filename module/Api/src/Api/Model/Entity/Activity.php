<?php

/**
 * @author Samier Sompura <>
 */

namespace Api\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Activity Doctroine Entity Class 
 * @ORM\Entity
 * @ORM\Table(name="activity_log")	
 */

class Activity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer");
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	
	/**
	 * @ORM\Column(length=255);
	 */
	protected $typeActivity;
	
	/**
	 * @ORM\Column(type="text");
	 */
	protected $activityMessage;
	
	/**
	 * @ORM\Column(length=255);
	 */
	protected $activityUser;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $dateCreated;
	
	/**
	 * Magic getter to expose protected properties.
	 *
	 * @param string $property
	 * @return mixed
	 */	
	public function __get($property) 
	{
		return $this->$property;
	}
	
	/**
	 * Magic setter to save protected properties.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value) 
	{
		$this->$property = $value;
	}
	
	/**
	 * Populate from an array.
	 *
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