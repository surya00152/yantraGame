<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Activityusers implements ServiceManagerAwareInterface
{
	
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'Api\Model\Entity\Activityusers';
	  
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
	 * Store each User related to a specific Activity
	 * @param array $post Activity
	 * @return object activity entity
	 */
	public function addActivityUsers($post)
	{
		$entityPath = self::ENTITY;
		$activityEntity = new $entityPath;				
		$activityEntity->populate($post);		
		$this->entityManager->persist($activityEntity);
		$this->entityManager->flush();
		return $activityEntity; 		
	}
	
	/**
	 * Delete an Activity for User
	 * @param int $activityid Activity
	 * @param int $userId User Id
	 */
	public function deleteActivity($activityid,$userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'au')
       	   ->set('au.deleteStatus',1)
		   ->where('au.activityId =' .$activityid. 'and au.users='.$userId)
       	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Mark the Activity as read
	 * @param int $activityid Activity
	 * @param int $userId User Id
	 */
	public function readActivity($activityid,$user)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'au')
      	   ->set('au.readStatus',1)
		   ->where('au.activityId =' .$activityid. 'and au.users='.$user)
       	   ->getQuery()
		   ->execute();			
	}
}