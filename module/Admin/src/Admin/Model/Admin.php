<?php

namespace Admin\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Admin implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'Admin\Model\Entity\Admin';

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
     *  check Admin Login
     *  
     */
    public function checkAdminLogin($post) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('a')
                ->from(self::ENTITY, 'a')
                ->where('a.userName = '.$qb->expr()->literal($post['uname']))
                ->andWhere('a.password = '.$qb->expr()->literal($post['password']))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     *  Get DrowMode
     *  
     */
    public function getDrawMode() {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('a.drawMode,a.percentage,a.manual,a.isJackpot,a.jackpotValue')
                ->from(self::ENTITY, 'a')
                ->where('a.Id = 1')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}
