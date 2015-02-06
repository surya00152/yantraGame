<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class DrowYantra implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\DrowYantra';

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
    
    /**
     * Add Drow Yantra
     * 
     * @return object drow yantra entity.
     */
    public function insertDrawYantra($post) {
        $entityPath = self::ENTITY;
        $drawYantraEntity = new $entityPath;
        $drawYantraEntity->populate($post);
        $this->entityManager->persist($drawYantraEntity);
        $this->entityManager->flush();
        return $drawYantraEntity;
    }
	
    /**
     * Get all drow yantra
     */
    public function getAllDrowYantra($date,$time = null) {
        $qb = $this->entityManager->createQueryBuilder();
        if ($time !== null) {
            return $qb->select('dy')
                ->from(self::ENTITY, 'dy')
                ->where($qb->expr()->like('dy.drawTime', $qb->expr()->literal($date. '%')))
                ->where($qb->expr()->notLike('dy.drawTime', $qb->expr()->literal('%'.$time)))
                ->orderBy('dy.Id','desc')
                ->getQuery()
                ->getArrayResult();
        } else {
            return $qb->select('dy')
                ->from(self::ENTITY, 'dy')
                ->where($qb->expr()->like('dy.drawTime', $qb->expr()->literal($date. '%')))
                ->orderBy('dy.Id','desc')
                ->getQuery()
                ->getArrayResult();
        }
        
    }
    
    /**
     * get Drow is exist or not
     */
    public function drowExistByDateTime($dateTime) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('dy')
                ->from(self::ENTITY, 'dy')
                ->where('dy.drawTime ='.$qb->expr()->literal($dateTime))
                ->getQuery()
                ->getArrayResult();
    }
    
}
