<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Message implements ServiceManagerAwareInterface
{
	
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'Api\Model\Entity\Message';
	  
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
	 * Store the message
	 * @param array $post message
	 * @return object stored message entity
	 */
	public function addMessage ($post)
	{
		$entityPath = self::ENTITY;
		$messageEntity = new $entityPath;				
		$messageEntity->populate($post);
		$this->entityManager->persist($messageEntity);
		$this->entityManager->flush();
		return $messageEntity; 
	}
	
	/**
	 * Get Single Message
	 * @param int $id Message Id
	 * @return array message
	 */
	public function getSingleMessage($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.firstName, u.Image, m.id,m.messageContent,m.subject,m.dateCreated,m.displayStatus,m.dateUpdated,m.attachments,m.senderId')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->where('m.id='.$id)
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getArrayResult();								
	}
		
	/**
	 * Get Message List
	 * @param int $id Message Id
	 * @param int $offset limit
	 * @param datetime $dateUpdated newest date for message
	 * @return array message list
	 */
	public function getMessageList($id,$offset,$dateUpdated )
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.firstName, u.Image, m.id,m.messageContent,m.subject,m.dateCreated,m.displayStatus,m.dateUpdated,m.attachments,m.senderId')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->where(('m.senderId='.$id.'and m.deleteStatus=0').('or r.receiverId=' .$id. ' and r.deleteStatus=0').'and m.dateUpdated <=' .$qb->expr()->literal($dateUpdated))
				  ->groupBy('m.id')
				  ->orderBy('m.dateUpdated', 'DESC')
				  ->setFirstResult($offset)
				  ->setMaxResults(5)
				  ->getQuery()
				  ->getArrayResult();								
	}
	
	/**
	 * Get List of Searched Messages 
	 * @param int $id Message Id
	 * @param int $offset limit
	 * @param str $term searched term
	 * @param datetime $dateUpdated newest date for message
	 * @return array message list
	 */
	public function getSearchMessageList($id,$offset,$dateUpdated,$term)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.firstName, u.Image, m.id,m.messageContent,m.subject,m.dateCreated,m.displayStatus,m.dateUpdated,m.attachments,m.senderId')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->leftJoin('Api\Model\Entity\Comments', 'c', 'WITH', 'c.messageId = m.id')
				  ->where($qb->expr()->like('m.subject', $qb->expr()->literal('%'.$term. '%')))
				  ->orwhere($qb->expr()->like('m.messageContent', $qb->expr()->literal('%'.$term. '%')))
				  ->orwhere($qb->expr()->like('c.messageContent', $qb->expr()->literal('%'.$term. '%')))
				  ->andwhere(('m.senderId='.$id.'and m.deleteStatus=0').('or r.receiverId=' .$id. ' and r.deleteStatus=0').'and m.dateUpdated <=' .$qb->expr()->literal($dateUpdated))
				  ->groupBy('m.id')
				  ->orderBy('m.dateUpdated', 'DESC')
				  ->setFirstResult($offset)
				  ->setMaxResults(5)
				  ->getQuery()
				  ->getArrayResult();								
	}

	/**
	 * Get List of Prepend Messages 
	 * @param int $id Message Id
	 * @param datetime $dateUpdated newest date for message
	 * @return array message list
	 */
	public function getPrependMessage($id,$dateUpdated )
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.firstName, u.Image, m.id,m.messageContent,m.subject,m.dateCreated,m.displayStatus,m.dateUpdated,m.attachments,m.senderId')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->where('m.senderId!='.$id .' and m.dateUpdated >' .$qb->expr()->literal($dateUpdated).'and m.deleteStatus=0 and r.receiverId='.$id.'and r.deleteStatus=0')
				  ->orderBy('m.dateUpdated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();								
	}

	/**
	 * Counts total Message in list 
	 * @param int $receiverId User Id
	 * @return array total messages
	 */
	public function countMessages($receiverId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('m.dateUpdated', 'm.subject', 'm.id')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->where(('m.senderId='.$receiverId.'and m.deleteStatus=0').('or r.receiverId=' .$receiverId. ' and r.deleteStatus=0'))
				  ->groupBy('m.id')
				  ->orderBy('m.dateUpdated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();								
	}

	/**
	 * Counts total Message while Search 
	 * @param int $senderId User Id
	 * @param str $term searched term
	 * @return array total messages
	 */
	public function countSearchMessages($senderId,$term)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('m.dateUpdated', 'm.subject', 'm.id')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'm.senderId = u.id')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->leftJoin('Api\Model\Entity\Comments', 'c', 'WITH', 'c.messageId = m.id')
				  ->where($qb->expr()->like('m.subject', $qb->expr()->literal('%'.$term. '%')))
				  ->orwhere($qb->expr()->like('m.messageContent', $qb->expr()->literal('%'.$term. '%')))
				  ->orwhere($qb->expr()->like('c.messageContent', $qb->expr()->literal('%'.$term. '%')))
				  ->andwhere('m.senderId='.$senderId.'and m.deleteStatus=0 or r.receiverId=' .$senderId. ' and r.deleteStatus=0')
				  ->groupBy('m.id')
				  ->orderBy('m.dateUpdated', 'DESC')
				  ->getQuery()
				  ->getArrayResult();								
	}
		
	/**
	 * Searchs Message by the given term
	 * @param int $senderId User Id
	 * @param str $term searched term
	 * @return array total search related messages
	 */
	public function Search($keyword,$senderId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('m.id,m.subject as label,u.firstName')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'u.id = m.senderId')
				  ->innerJoin('Api\Model\Entity\Msgrecipient', 'r', 'WITH', 'r.messageId = m.id')
				  ->leftJoin('Api\Model\Entity\Comments', 'c', 'WITH', 'c.messageId = m.id')
				  ->where($qb->expr()->like('m.subject', $qb->expr()->literal('%'.$keyword. '%')))
				  ->orwhere($qb->expr()->like('m.messageContent', $qb->expr()->literal('%'.$keyword. '%')))
				  ->orwhere($qb->expr()->like('c.messageContent', $qb->expr()->literal('%'.$keyword. '%')))
				  ->andwhere('m.senderId='.$senderId.'and m.deleteStatus=0 or r.receiverId=' .$senderId. ' and r.deleteStatus=0')
				  ->groupBy('m.id')
				  ->getQuery()
				  ->getArrayResult();	
	}
	
	/**
	 * Mark message as read
	 * @param int $id Message Id
	 */
	public function setMsgStatus($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'm')
       	   ->set('m.displayStatus', 1)
		   ->where('m.id = ?1')
       	   ->setParameter(1, $id)
       	   ->getQuery()
		   ->execute();		
	}
	
	/**
	 * Delete message from Message table for Specific User
	 * @param int $id Message Id
	 */	
	public function updateDelStatus($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'm')
       	   ->set('m.deleteStatus',1)
		   ->where('m.id =' .$id)
       	   ->getQuery()
		   ->execute();
	}

	/**
	 * Checks if the message is Deleted from Message Table
	 * @param int $id Message Id
	 * @return array MessageStatus
	 */	
	public function checkDeleteStatus($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('m.deleteStatus, m.attachments')
				  ->from(self::ENTITY, 'm')
				  ->where('m.id =' .$id)
				  ->getQuery()
				  ->getArrayResult();			
	}

	/**
	 * Delete Message from database
	 * @param int $id Message Id
	 */	
	public function deleteMessage($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->delete(self::ENTITY, 'm')
		   ->where('m.id =' .$id)
       	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Get Date of Newly created Message
	 * @param int $id Message Id
	 */
	public function getDate($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('m.dateUpdated,m.dateCreated,u.firstName,u.Image')
				  ->from(self::ENTITY,'m')
				  ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'u.id = m.senderId')
				  ->where('m.id=' .$id)
				  ->getQuery()
				  ->getArrayResult();	
	}	
}
?>