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
    public function getAdminTransactionReport($adminId = 1,$date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('t.Id,ul.name as Lname,ug.name as Aname,t.transType,t.transBalance,t.time')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::USERENTITY, 'ul','with', 't.userId = ul.Id')
                ->leftJoin(self::USERENTITY, 'ug','with', 't.agentId = ug.Id')
                ->innerJoin(self::DATEENTITY, 'dt','with', 't.dateId = dt.Id')
                ->where('dt.drawDate ='.$qb->expr()->literal($date))
                ->andWhere('t.agentId ='.$adminId.' OR t.userId ='.$adminId)
                ->orderBy('t.Id','desc')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get Transaction Daywise report
     */
    public function getAdminTransactionDaywiseReport($adminId = 1,$startDate,$endDate) {
        
        $stmt = $this->entityManager->getConnection()
                    ->prepare("SELECT d0_.drawDate AS drawDate, SUM((SELECT SUM(t1_.transBalance) AS dctrn__1 FROM transaction t1_ WHERE t1_.transType = 'Credit' AND t1_.dateId = d0_.Id AND (t1_.agentId = 1 OR t1_.userId = 1) GROUP BY d0_.drawDate)) as creditBal, SUM((SELECT SUM(t2_.transBalance) AS dctrn__2 FROM transaction t2_ WHERE t2_.transType = 'Debit' AND t2_.dateId = d0_.Id AND (t2_.agentId = 1 OR t2_.userId = 1) GROUP BY d0_.drawDate)) as debitBal FROM transaction t3_ LEFT JOIN users u4_ ON (t3_.userId = u4_.Id) LEFT JOIN users u5_ ON (t3_.agentId = u5_.Id) LEFT JOIN date d0_ ON (t3_.dateId = d0_.Id) WHERE d0_.drawDate >= '$startDate' AND d0_.drawDate <= '$endDate' AND (t3_.agentId = 1 OR t3_.userId = 1) GROUP BY d0_.drawDate");
        $stmt->execute();
        return $stmt->fetchAll();
//        echo '<pre>';print_r($result);exit;
//        $qb = $this->entityManager->createQueryBuilder();
//        return $qb->select("dt.drawDate,SUM(SELECT SUM(tc.transBalance) FROM ".self::ENTITY." tc WHERE tc.transType = 'Credit' AND tc.dateId = dt.Id AND (tc.agentId=1 OR tc.userId=1) GROUP BY dt.drawDate ) as creditBal,SUM(SELECT SUM(td.transBalance) FROM ".self::ENTITY." td WHERE td.transType = 'Debit' AND td.dateId = dt.Id AND (td.agentId=1 OR td.userId=1) GROUP BY dt.drawDate) as debitBal")
//                ->from(self::ENTITY, 't')
//                ->leftJoin(self::USERENTITY, 'ul','with', 't.userId = ul.Id')
//                ->leftJoin(self::USERENTITY, 'ug','with', 't.agentId = ug.Id')
//                ->leftJoin(self::DATEENTITY, 'dt','with', 't.dateId = dt.Id')
//                ->where('dt.drawDate >='.$qb->expr()->literal($startDate))
//                ->andWhere('dt.drawDate <='.$qb->expr()->literal($endDate))
//                ->andWhere('t.agentId ='.$adminId.' OR t.userId ='.$adminId)
//                ->groupBy('dt.drawDate')
//                ->getQuery()
//                ->getArrayResult();
    
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
        return $qb->select("dt.drawDate as transDate,dt.openingBal,(SELECT SUM(tc.transBalance) FROM ".self::ENTITY." tc WHERE tc.transType = 'Credit' AND tc.agentId=t.agentId AND tc.dateId = dt.Id) as creditBal,(SELECT SUM(td.transBalance) FROM ".self::ENTITY." td WHERE td.transType = 'Debit' AND td.agentId=t.agentId AND td.dateId = dt.Id) as debitBal,dt.closeBal")
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
