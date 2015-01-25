<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Ticket implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\Ticket';
    const DATEENTITY = 'User\Model\Entity\TicketDate';
    const DRAWENTITY = 'User\Model\Entity\DrowYantra';
    
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
    public function createTicket($post) {
        $entityPath = self::ENTITY;
        $ticketEntity = new $entityPath;
        $ticketEntity->populate($post);
        $this->entityManager->persist($ticketEntity);
        $this->entityManager->flush();
        return $ticketEntity;
    }
    
	
    /**
     * Get Ticke Date By DateId And YantraId And Time
     */
    public function getTicket($dateId,$yantraId,$time) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('t')
                ->from(self::ENTITY, 't')
                ->where('t.dateId = '.$dateId)
                ->andWhere('t.yantraId = '.$yantraId)
                ->andWhere('t.time = '.$qb->expr()->literal($time))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     *  Update Ticket Details By Id
     *  @param int $userId
     */
    public function updateTicket($id,$data) {
        $ticketEntity = $this->repository->findOneBy(array('Id' => $id));
        $ticketEntity->populate($data);
        $this->entityManager->merge($ticketEntity);
        $this->entityManager->flush();
        return $ticketEntity;
    }
    
    /**
     * Get Ticke Date By DateId And YantraId And Time
     */
    public function getYantraRate($drawDate,$drawTime) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('SUM(t.quantity) as quantity,t.yantraId')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                ->where('t.time ='.$qb->expr()->literal($drawTime))
                ->andWhere('dt.drawDate ='.$qb->expr()->literal($drawDate))
                ->groupBy('t.yantraId')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * Get Ticke By YantraId
     */
    public function getTicketByYantraId($drawDate,$drawTime,$yantraId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('t.Id,t.quantity,t.yantraId,dt.userId as userId')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                ->where('t.time ='.$qb->expr()->literal($drawTime))
                ->andWhere('dt.drawDate ='.$qb->expr()->literal($drawDate))
                ->andWhere('t.yantraId ='.$yantraId)
                ->groupBy('dt.userId')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * update Ticket status By DATE and TIME
     */
    public function updateTicketStatus($drawTime,$dateId,$status) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->update(self::ENTITY, 't')
                  ->set('t.status', $qb->expr()->literal($status))
                  ->where('t.time ='.$qb->expr()->literal($drawTime))
                  ->andWhere('t.dateId ='.$dateId)
                  ->getQuery()
                  ->execute();
    }
    
    /**
     * get ticket by UserId
     */
    public function getTicketByUserId($drawDate,$drawTime,$userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('t')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                ->where('t.time ='.$qb->expr()->literal($drawTime))
                ->andWhere('dt.drawDate ='.$qb->expr()->literal($drawDate))
                ->andWhere('dt.userId ='.$userId)
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get ticket by Date
     */
    public function getPurchaseTicketByDate($drawDate,$userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('t')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                ->where('dt.drawDate ='.$qb->expr()->literal($drawDate))
                ->andWhere('dt.userId ='.$userId)
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get ticket by Start date to End Date
     */
    public function getDaywiseReport($userId,$startDate,$endDate) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('dt.drawDate,dt.openingBal,SUM(t.totalPrice) as purchase,SUM(t.totalWin) as win,dt.closeBal')
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                ->where('dt.drawDate >='.$qb->expr()->literal($startDate))
                ->andWhere('dt.drawDate <='.$qb->expr()->literal($endDate))
                ->andWhere('dt.userId ='.$userId)
                ->groupBy('dt.drawDate')
                ->getQuery()
                ->getArrayResult();
    }
    
    /**
     * get Showwise Report
     */
    public function getShowwiseReport($userId,$date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select("dt.drawDate as date,t.time as showTime,SUM(t.totalPrice) as purchase,SUM(t.totalWin) as win")
                ->from(self::ENTITY, 't')
                ->leftJoin(self::DATEENTITY, 'dt','with', 'dt.Id = t.dateId')
                //->leftJoin(self::DRAWENTITY, 'dy','with', 'dy.drawTime = dt.drawDate +  t.time')
                ->where('dt.drawDate ='.$qb->expr()->literal($date))
                ->andWhere('dt.userId ='.$userId)
                //->andWhere("dy.drawTime = CONCAT(CONCAT(dt.drawDate,' '),t.time)")
                ->groupBy('t.time')
                ->getQuery()
                //->getSQL();
                ->getArrayResult();
    }
    
}
