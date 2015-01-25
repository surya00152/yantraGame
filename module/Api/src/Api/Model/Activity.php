<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Activity implements ServiceManagerAwareInterface
{
   /**
    * Entity code.
    * Can be used as part of method name for entity processing
    */
    const ENTITY = 'Api\Model\Entity\Activity';
	  
	/**
	 * @var ServiceManager
	 */
	protected $serviceManager;
	
	/**             
	 * @var Doctrine\ORM\EntityManager
 	 */                
	protected $entityManager;
	
	/**             
	 * @var Doctrine\ORM\EntityManager\Repository
 	 */                
	protected $repository;

	/**
	 * @param ServiceManager $serviceManager
	 * @return Form
	 */
	public function setServiceManager(ServiceManager $serviceManager)
	{
		$this->serviceManager = $serviceManager;
		$this->entityManager  = $serviceManager->get('doctrine.entitymanager.orm_default');
		$this->repository     = $this->entityManager->getRepository(self::ENTITY);		
		return $this;
	}

	/**
	 * add activityMessage in Database
	 * @param array $post Activity
	 * @return object activity entity
	 */
	public function addActivity($post)
	{
		$entityPath = self::ENTITY;
		$activityEntity = new $entityPath;				
		$activityEntity->populate($post);		
		$this->entityManager->persist($activityEntity);
		$this->entityManager->flush();		
		return $activityEntity; 
	}

	/**
	 * Get activity Messages
	 * @param int $userId UserId
	 * @return array activity Message
	 */
	public function getActivities($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('a.activityMessage, a.dateCreated')
				  ->from(self::ENTITY,'a')
				  ->innerJoin('Api\Model\Entity\Activityusers', 'au', 'WITH', 'a.id = au.activityId')
				  ->where('au.users='.$userId. 'and au.deleteStatus=0')
				  ->orderBy('a.dateCreated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();
    }	

	/**
	 * Get notifications
	 * @param int $userId UserId
	 * @return array activity notifications
	 */
	public function getNotifications($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('a.activityMessage, a.dateCreated,a.id')
				  ->from(self::ENTITY,'a')
				  ->innerJoin('Api\Model\Entity\Activityusers', 'au', 'WITH', 'a.id = au.activityId')
				  ->where('au.users='.$userId. 'and au.deleteStatus=0'. 'and au.readStatus=0' )
				  ->orderBy('a.dateCreated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();
    }

	/**
	 * Count total Notifications
	 * @param int $userId UserId
	 * @return array activity notifications
	 */
	public function countNotifications($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('a.dateCreated')
				  ->from(self::ENTITY,'a')
				  ->innerJoin('Api\Model\Entity\Activityusers', 'au', 'WITH', 'a.id = au.activityId')
				  ->where('au.users='.$userId. 'and au.readStatus=0 and au.deleteStatus=0')
				  ->orderBy('a.dateCreated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();
    }	

	/**
	 * Get all read Notifications
	 * @param int $userId UserId
	 * @return array activity notifications
	 */
	public function getReadNotifications($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('a.activityMessage, a.dateCreated,a.id')
				  ->from(self::ENTITY,'a')
				  ->innerJoin('Api\Model\Entity\Activityusers', 'au', 'WITH', 'a.id = au.activityId')
				  ->where('au.users='.$userId. 'and au.deleteStatus=0')
				  ->orderBy('a.dateCreated', 'DESC')
				  ->setMaxResults(10)
				  ->getQuery()
				  ->getArrayResult();
    }
}