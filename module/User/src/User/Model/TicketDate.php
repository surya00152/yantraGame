<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class TicketDate implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\TicketDate';

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
     * Create New TicketDate
     */
    public function createTicketDate($post) {
        $entityPath = self::ENTITY;
        $ticketDateEntity = new $entityPath;
        $ticketDateEntity->populate($post);
        $this->entityManager->persist($ticketDateEntity);
        $this->entityManager->flush();
        return $ticketDateEntity;
    }
    
	
    /**
     * Get Ticke Date By UserId And CurrentDate
     */
    public function getTicketDate($userId,$date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('td')
                ->from(self::ENTITY, 'td')
                ->where('td.userId = '.$userId)
                ->andWhere('td.drawDate = '.$qb->expr()->literal($date))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Get Ticke Date By UserId And CurrentDate
     */
    public function getDateTicketByDate($date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('td')
                ->from(self::ENTITY, 'td')
                ->where('td.drawDate = '.$qb->expr()->literal($date))
                ->getQuery()
                ->getArrayResult();
    }
}
