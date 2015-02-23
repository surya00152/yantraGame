<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class User implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\User';

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
    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        $this->repository = $this->entityManager->getRepository(self::ENTITY);
        return $this;
    }
	
   /*
    * Check is Logged User
    */
    public function isLoggedUser($post) {
        $obj = $this->repository->findOneBy(array('loginToken' => $post['token'],'accountStatus' => 'Active'));
        if (!$obj)
            return 0;
        else
            return $obj->Id;
    }
    
    /**
     * Create New User
     */
    public function createUser($post) {
        $entityPath = self::ENTITY;
        $userEntity = new $entityPath;
        $userEntity->populate($post);
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();
        return $userEntity;
    }
    
    /**
     *  Update User Details
     *  @param int $userId
     */
    public function updateUser($userId,$data) {
        $userEntity = $this->repository->findOneBy(array('Id' => $userId));
        $userEntity->populate($data);
        $this->entityManager->merge($userEntity);
        $this->entityManager->flush();
        return $userEntity;
    }
    
    /**
     * update user Balance
     */
    public function updateUserBal($userId,$bal) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->update(self::ENTITY, 'u')
                  ->set('u.avaiPurchaseBal', 'u.avaiPurchaseBal+'.$bal['avaiPurchaseBal'])
                  ->set('u.totalWinBal', 'u.totalWinBal+'.$bal['totalWinBal'])
                  ->where('u.Id = '.$userId)
                  ->getQuery()
                  ->execute();
    }
    
    /**
     * get user by phone no
     */
    public function getUserByPhoneNo($poneNo) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.phoneNo ='.$poneNo)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
     /**
     * get user by phone no
     */
    public function getUsersDetailsForAdmin() {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('count(u.Id) as totalUsers,u.userRoll,SUM(u.avaiPurchaseBal) as localBal,SUM(u.avaiTransBal) as agentBal')
                ->from(self::ENTITY, 'u')
                ->groupBy('u.userRoll')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get user by login Token
     */
    public function getUserByToken($token) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.loginToken =' .$qb->expr()->literal($token))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * get user by Id
     */
    public function getUserById($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.Id =' . $userId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * check login credential
     */
    public function userLogin($post) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.phoneNo =' . $post['phoneNo'].' AND '.'u.password = '.$qb->expr()->literal(sha1($post['password'])))
                ->andWhere('u.userRoll = '.$qb->expr()->literal($post['roll']))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Check account verify code exist
     */
    public function verifyCodeExist($verifyCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.verifyCode =' . $verifyCode)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Check password verify code exist
     */
    public function passwordVerifyCodeExist($verifyCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.passVerifyCode =' . $verifyCode)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    } 
    
    /**
     * get Local user By Phone No
     */
    public function getLocalUserByPhoneNo($phoneNo) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.phoneNo =' . $phoneNo)
                ->andWhere('u.userRoll = ' . $qb->expr()->literal('local'))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
       
    /**
     * get Local user By Request Code
     */
    public function getLocalUserByTransferCode($reqCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.balReqCode = ' . $reqCode)
                ->andWhere('u.userRoll = ' . $qb->expr()->literal('local'))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Check transfer verify code exist
     */
    public function transferVerifyCodeExist($verifyCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u.Id')
                ->from(self::ENTITY, 'u')
                ->where('u.balReqCode =' . $verifyCode)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * get users by Roll
     */
    public function getUserByRoll($roll) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.userRoll ='.$qb->expr()->literal($roll))
                ->getQuery()
                ->getArrayResult();
    }

}
