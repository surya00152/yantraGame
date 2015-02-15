<?php

namespace Api\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Users implements ServiceManagerAwareInterface
{
	
 /**
  * Entity code.
  * Can be used as part of method name for entity processing
  */
  const ENTITY = 'Api\Model\Entity\Users';
	  
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
	 * Get User name in Search
	 * @param str $keyword for User.
	 * @return mixed user info.
	 */
	public function getUserList($keyword)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.id,u.firstName as name,u.Image')
                        ->from(self::ENTITY,'u')
                        ->where($qb->expr()->like('u.firstName', $qb->expr()->literal('%'.$keyword. '%')))
                        ->getQuery()
                        ->getArrayResult();	
        }
	
	/**
	 * Add User
	 * @param mixed user info.
	 * @return object user entity.
	 */
	public function addUser($post)
	{
	    if(isset($post['email']))
            $post['email'] = strtolower($post['email']);
		$entityPath = self::ENTITY;
		$userEntity = new $entityPath;				
		$userEntity->populate($post);										
		$this->entityManager->persist($userEntity);
		$this->entityManager->flush(); 		
		return $userEntity; 
	}
	
	/**
	 *  Check for existing users
	 *  @param str $email user email
	 *  @return int count 
	 */	 
	public function checkEmailExists($email)
	{
	   
        $email = strtolower($email);
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('count(u.id) as record_count,u.id')
				  ->from(self::ENTITY,'u')
				  ->where('u.email ='.$qb->expr()->literal($email).'and u.preUserStatus !=1')
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();			
	}

	/**
	 *  Check for existing users
	 *  @param str $email user email
	 *  @return int count 
	 */	 
	public function checkPreUserExists($email,$phoneNo)
	{
	    $email = strtolower($email);
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('count(u.id) as record_count , u.id')
				  ->from(self::ENTITY,'u')
				  ->where('u.email ='.$qb->expr()->literal($email).' OR u.phoneSMS ='.$qb->expr()->literal($phoneNo))
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();			
	}
	
	/**
	 *  Check for existing contact
	 *  @param str $email user email
	 *  @return int count 
	 */	  
	public function checkPreContactExists($email,$contact_id)
	{
	    $email = strtolower($email);
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('count(u.id) as record_count , u.id')
				  ->from(self::ENTITY,'u')
				  ->where('u.email ='.$qb->expr()->literal($email).'and u.id !='.$contact_id)
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();			
	}
	
	/**
	 * Get User Transactions
	 * @param int $userId User.
	 * @return mixed User Information.	 
	 */
	public function getProfile($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.id,u.balance,u.firstName,u.email,u.phoneSMS,u.lastLogin')
				  ->from(self::ENTITY,'u')
				  ->where('u.id='.$userId)
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();		
	}

	/**
	 * Get User Balance
	 * @param int $userId User.
	 * @return array balance of the User. 
	 */
	public function getBalance($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.balance')
				  ->from(self::ENTITY,'u')
				  ->where('u.id='.$userId)
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();		
	}
	
	/**
	 * Add Balance
	 * @param int $id User.
	 * @param int $add amount to be added. 
	 */
	public function addBalance($id,$add)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'u')
		   ->set('u.balance','u.balance +'.$add)
		   ->where('u.id =' .$id)
		   ->getQuery()
		   ->execute();			
	}

	/**
	 * Subtract Balance
	 * @param int $id User.
	 * @param int $subtract amount to be subtracted. 
	 */
	public function subBalance($id,$subtract)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'u')
	       ->set('u.balance','u.balance -'.$subtract)
		   ->where('u.id =' .$id)
    	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Ban/Block User.
	 * @param int $id User to be blocked.
	 */
	public function banUser($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'u')
	       ->set('u.status',1) // Status = 1 means User is Banned
		   ->where('u.id =' .$id)
       	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Checks if the User with given hexcode Exists.
	 * @param hex $hex Inviter invitationCode
	 */	 
	public function checkUser($hex)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.id')
				  ->from(self::ENTITY,'u')
				  ->where('u.invitationCode ='.$qb->expr()->literal($hex))
				  ->getQuery()
				  ->getArrayResult();
	}

	/**
	 * Add Balance for Sending Invites
	 * @param int $id Inviter UserId
	 * @param hex $hexcode Inviter invitatioinCode
	 */
	public function addInviteBalance($id,$hexcode)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update(self::ENTITY, 'u')
    	   ->set('u.balance','u.balance + 10')
		   ->where('u.id =' .$id. 'and u.invitationCode='.$qb->expr()->literal($hexcode))
       	   ->getQuery()
		   ->execute();			
	}

	/**
	 * Gets the Invitation Code Of User
	 * @param int $id UserId
	 * @return hex Invitation code
	 */
	public function getInvitationCode($id)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.invitationCode')
				  ->from(self::ENTITY,'u')
				  ->where('u.id=' .$id)
				  ->setMaxResults(1)
			      ->getQuery()
			      ->getOneOrNullResult();			
	}
	
	/**
	 *  Get the User Id
	 *  @param str $email user email
	 *  @return int user id 
	 */	 
	public function getUserId($email)
	{
	    $email = strtolower($email);
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.id')
				  ->from(self::ENTITY,'u')
				  ->where('u.email ='.$qb->expr()->literal($email).'and u.preUserStatus !=1')
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();			
	}
	
	/**
	 *  Get the Logged User Details
	 *  @param str $userId
	 *  @return int user id 
	 */	 
	public function getLoggedUserDetails($userId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('u.id,u.firstName,u.lastName,u.city,u.state,u.Country,u.email,u.phoneSMS,u.Image,u.company')
				  ->from(self::ENTITY,'u')
				  ->where('u.id ='.$userId)
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();			
	}
	
	public function countRegisterUser()
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('count(u.id) as countUser')
				  ->from(self::ENTITY,'u')
				  ->where('u.preUserStatus = 0')
				  ->getQuery()
				  ->getArrayResult();
	}
	
	/**
	 *  Check for existing image in user profile
	 *  @param str $image user profile image 
	 *  @return int count 
	 */	 
	public function checkImageExist($image)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select('count(u.id) as counts')
				  ->from(self::ENTITY,'u')
				  ->where('u.Image ='.$qb->expr()->literal($image))
				  ->setMaxResults(1)
				  ->getQuery()
				  ->getOneOrNullResult();
	}
        
    public function searchUserDetails($userIds,$word)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $concatFields = array(
                        'LOWER(u.firstName)',
                        'LOWER(u.lastName)',
                        'LOWER(u.lastName)',
                        'LOWER(u.firstName)',
                    );

        // Routine code. All fields will be separated by ' '.
        foreach ($concatFields as $field) {
            if (!isset($searchIn)) {
                $searchIn = $qb->expr()->concat($qb->expr()->literal(' '), $field);
                continue;
            }

            $searchIn = $qb->expr()->concat(
                $searchIn,
                $qb->expr()->concat($qb->expr()->literal(' '), $field)
            );
        }
        
        return $qb->select('u.id,u.firstName,u.lastName,u.email')
                  ->from(self::ENTITY,'u')
                  ->where($qb->expr()->like('LOWER(u.firstName)', $qb->expr()->literal('%'.strtolower($word).'%')).' OR '.$qb->expr()->like('LOWER(u.lastName)', $qb->expr()->literal('%'.strtolower($word).'%')))
                  ->orwhere($qb->expr()->like($searchIn, $qb->expr()->literal('%'.strtolower($word).'%')))
                  ->andwhere($qb->expr()->notIn('u.id', $userIds))
                  ->orderBy('u.firstName', 'ASC')
                  ->getQuery()
                  ->getArrayResult();          
    }
}