<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Msgrecipient implements ServiceManagerAwareInterface
{
	
   /**
    * Entity code.
    * Can be used as part of method name for entity processing
    */
    const ENTITY = 'Api\Model\Entity\Msgrecipient';
	  
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
	 *  Store Message Recipients
	 *  @param array $post message recipients
	 *  @return object entity
	 */
	public function addRecipientMessage ($post)
	{
		$entityPath = self::ENTITY;
		$messageEntity = new $entityPath;				
		$messageEntity->populate($post);
		$this->entityManager->persist($messageEntity);
		$this->entityManager->flush();
		return $messageEntity; 
	}

	/**
	 *  Set message as deleted for specific User
	 *  @param int $id Message Id
	 *  @param int $logged_user Login User
	 */	
	public function updateDelStatusRecipient($id,$logged_user)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'r')
	       ->set('r.deleteStatus',1)
		   ->where('r.messageId =' .$id. 'and r.receiverId=' .$logged_user)
       	   ->getQuery()
		   ->execute();			
	}
		
	/**
	 *  Check the Delete Status
	 *  @param int $id Message Id
	 */	
	public function checkDeleteStatus($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('r.deleteStatus')
			 	  ->from(self::ENTITY, 'r')
			 	  ->where('r.messageId =' .$id. 'and r.deleteStatus=0')
       			  ->getQuery()
			 	  ->getArrayResult();			
	}
	
	/**
	 *  Delete Message
	 *  @param int $id Message Id	 
	 */	
	public function deleteMessage($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->delete(self::ENTITY, 'r')
		   ->where('r.messageId =' .$id)
       	   ->getQuery()
		   ->execute();			
	}
	
	/**
	 *  Get all the Receipients of Message 
	 *  @param int $id Message Id
	 */
	public function getReceiverId($messageId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('r.receiverId as id, u.firstName as name')
				  ->from(self::ENTITY,'r')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'r.receiverId = u.id')								
				  ->where('r.messageId=' .$messageId. 'and r.deleteStatus=0')
				  ->getQuery()
				  ->getArrayResult();								
	}	
}
?>