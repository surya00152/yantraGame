<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Notification implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\Notification';
    
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
     * Create New Notification
     */
    public function createNotification($post) {
        $entityPath = self::ENTITY;
        $noteEntity = new $entityPath;
        $noteEntity->populate($post);
        $this->entityManager->persist($noteEntity);
        $this->entityManager->flush();
        return $noteEntity;
    }
    
    /**
     * Get Notification By Local User ID
     */
    public function getNotification($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('n.requestedName as agentName,n.message,n.date')
                ->from(self::ENTITY, 'n')
                ->where('n.reqTo = '.$userId)
                ->orderBy('n.date','desc')
                ->getQuery()
                ->getArrayResult();
    }    
}
