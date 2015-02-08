<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Transaction implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\Transaction';
    const USERENTITY = 'User\Model\Entity\User';
    const DATEENTITY = 'User\Model\Entity\TicketDate';
    
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
     * Create New Transaction Report
     */
    public function createTransaction($post) {
        $entityPath = self::ENTITY;
        $transEntity = new $entityPath;
        $transEntity->populate($post);
        $this->entityManager->persist($transEntity);
        $this->entityManager->flush();
        return $transEntity;
    }
    
    /**
     * get Transaction report
     */
    public function getTransactionReport($agentId,$date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u.name,t.transType,t.transBalance,t.time')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::USERENTITY, 'u','with', 't.userId = u.Id')
                ->innerJoin(self::DATEENTITY, 'dt','with', 't.dateId = dt.Id')
                ->where('dt.drawDate ='.$qb->expr()->literal($date))
                ->andWhere('t.agentId ='.$agentId)
                ->orderBy('t.time','desc')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get Transaction Details By UserId And Date
     */
    public function getTransactionDataByUserIdAndDate($userId,$date) {
        /*$qb = $this->entityManager->createQueryBuilder();
        return $qb->select('SUM(t.transBalance) as bal,t.transType')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::USERENTITY, 'u','with', 't.userId = u.Id')
                ->where('t.date >='.$qb->expr()->literal($date.' 00:00:01'))
                ->andWhere('t.date <='.$qb->expr()->literal($date.' 23:59:59'))
                ->andWhere('t.userId ='.$userId)
                ->groupBy('t.transType')
                ->getQuery()
                ->getArrayResult();*/
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('SUM(t.transBalance) as bal,t.transType')
                ->from(self::ENTITY, 't')
                ->innerJoin(self::DATEENTITY, 'dt','with', 't.dateId = dt.Id')
                ->where('dt.drawDate ='.$qb->expr()->literal($date))
                ->andWhere('t.userId ='.$userId)
                ->groupBy('t.transType')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get Transaction by Start date to End Date
     */
    public function getDaywiseReport($agentId,$startDate,$endDate) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select("dt.drawDate as transDate,dt.openingBal,(SELECT SUM(tc.transBalance) FROM ".self::ENTITY." tc WHERE tc.transType = 'Credit' AND tc.agentId=t.agentId) as creditBal,(SELECT SUM(td.transBalance) FROM ".self::ENTITY." td WHERE td.transType = 'Debit' AND td.agentId=t.agentId) as debitBal,dt.closeBal")
                ->from(self::ENTITY, 't')
                ->innerJoin(self::DATEENTITY, 'dt','with', 't.dateId = dt.Id')
                ->where('dt.drawDate >='.$qb->expr()->literal($startDate))
                ->andWhere('dt.drawDate <='.$qb->expr()->literal($endDate))
                ->andWhere('t.agentId ='.$agentId)
                ->groupBy('dt.drawDate')
                ->getQuery()
                ->getArrayResult();
    }
}
