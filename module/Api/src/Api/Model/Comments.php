<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Comments implements ServiceManagerAwareInterface
{
	
	/**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'Api\Model\Entity\Comments';
	  
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
	 * Store the Comments
	 * @param array $post Comment
	 * @return object Comment Entity 
	 */
	public function addComments($post)
	{
		$entityPath = self::ENTITY;
		$CommentEntity = new $entityPath;				
		$CommentEntity->populate($post);
		$this->entityManager->persist($CommentEntity);
		$this->entityManager->flush();
		return $CommentEntity; 
	}
	
	/**
	 * Get the Comments
	 * @param int $id Message id
	 * @return array Comments
	 */
	public function getComments($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('c.messageContent ,c.attachments as files, c.senderId, u.firstName,u.Image')
				  ->from(self::ENTITY,'c')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'u.id = c.senderId')
				  ->where('c.messageId='.$id)
				  ->orderBy('c.dateCreated', 'ASC')
				  ->getQuery()
				  ->getArrayResult();	
	}
	
	/**
	 * Update message table in database
	 * @param int $id message Id
	 */
	public function updateDate($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'c')
		   ->innerJoin('Api\Model\Entity\Message', 'm', 'WITH', 'm.id = c.messageId')
       	   ->set('m.dateUpdated',1)
		   ->where('m.id='.$id)
           ->getQuery()
		   ->execute();		
	}
	
	/**
	 * Get Comment attachments
	 * @param int $id message Id
	 */
	public function getAttachments($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('c.attachments as files')
				  ->from(self::ENTITY,'c')
				  ->where('c.messageId='.$id)
				  ->getQuery()
				  ->getArrayResult();	
	}

	/**
	 * Delete Comments
	 * @param int $id Message Id	 
	 */	
	public function deleteMessage($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->delete(self::ENTITY, 'c')
		   ->where('c.messageId =' .$id)
       	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Get Username of the Sender
	 * @param int $id Comment Id
	 * @return array User info
	 */
	public function getUsername($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.firstName, u.Image')
				  ->from(self::ENTITY,'c')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'c.senderId = u.id')
				  ->where('c.id='.$id)
				  ->getQuery()
				  ->getArrayResult();	
	}
}