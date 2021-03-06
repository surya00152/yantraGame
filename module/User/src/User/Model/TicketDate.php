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
    const USERENTITY = 'User\Model\Entity\User';
    const TICKET_ENTITY = 'User\Model\Entity\Ticket';
    const TRANSACTION_ENTITY = 'User\Model\Entity\Transaction';

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
    
    /**
     * delete data Date BY Date 
     */
    public function deleteDataByLowerDate($date) {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb->select('td,t,tn')
                    ->from(self::ENTITY, 'td')
                    ->leftJoin(self::TICKET_ENTITY, 't','with', 'td.Id = t.dateId')
                    ->leftJoin(self::TRANSACTION_ENTITY, 'tn','with', 'td.Id = tn.dateId')
                    ->where('td.drawDate < '.$qb->expr()->literal($date))
                    ->getQuery()
                    ->execute();
                    //->getArrayResult();
        if (count($result) > 0) {
            foreach ($result as $entity) {
                if (count($entity) > 0)
                    $this->entityManager->remove($entity);
            }
            $this->entityManager->flush();
        }
    }
    
    /**
     * Get User Data By Ticke Date
     */
    public function getUserDataByTicketDate($date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('td.Id,td.userId,u.userRoll,u.avaiPurchaseBal,u.avaiTransBal')
                ->from(self::ENTITY, 'td')
                ->leftJoin(self::USERENTITY, 'u','with', 'td.userId = u.Id')
                ->where('td.drawDate = '.$qb->expr()->literal($date))
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * Update Date Ticket Details By Id
     * 
     */
    public function updateDateTicket($id,$data) {
        $ticketDateEntity = $this->repository->findOneBy(array('Id' => $id));
        $ticketDateEntity->populate($data);
        $this->entityManager->merge($ticketDateEntity);
        $this->entityManager->flush();
        return $ticketDateEntity;
    }
}
