<?php

namespace Admin\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Yantra implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'Admin\Model\Entity\Yantra';

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
     *  Get all yantra list
     *  @param int $userId and contactid
     */
    public function getAllYantra() {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('y')
                ->from(self::ENTITY, 'y')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     *  Get all yantra list
     *  @param int $userId and contactid
     */
    public function getAllYantraIds() {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('y.Id')
                ->from(self::ENTITY, 'y')
                ->getQuery()
                ->getArrayResult();
    }
}
