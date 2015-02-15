<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Doctrine\ORM\Query\ResultSetMapping;

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
        $pastDate = new \DateTime($date);
        $pastDate->modify("-1 day");
        $pastDate = $pastDate->format('d-m-Y');
        $pastDate = $pastDate." 00:00:00";
        //exit;
        
        $qb = $this->entityManager->createQueryBuilder();
        if ($time !== null) {
            return $qb->select('dy')
                ->from(self::ENTITY, 'dy')
                ->where($qb->expr()->like('dy.drawTime', $qb->expr()->literal($date. '%')))
                ->andWhere($qb->expr()->notLike('dy.drawTime', $qb->expr()->literal('%'.$time)))
                ->orWhere('dy.drawTime = '.$qb->expr()->literal($pastDate))
                ->orderBy('dy.Id','desc')
                ->getQuery()
                ->getArrayResult();
        } else {
            return $qb->select('dy')
                ->from(self::ENTITY, 'dy')
                ->where($qb->expr()->like('dy.drawTime', $qb->expr()->literal($date. '%')))
                ->orWhere('dy.drawTime = '.$qb->expr()->literal($pastDate))
                ->orderBy('dy.Id','desc')
                ->getQuery()
                ->getArrayResult();
        }
        
    }
    
    public function getAllDrowYantraByDates($startDate,$endDate) {
        
        $startDate = $startDate." 00:00:00";
        $endDate = $endDate." 23:59:59";
        
//        $rsm = new ResultSetMapping;
//        
//        $qb = $this->entityManager->createNativeQuery("SELECT sum(pl) as plt,DATE_FORMAT(STR_TO_DATE(drawTime, '%d-%m-%Y'),'%d-%m-%Y') as dates from draw WHERE drawTime >= '".$startDate."' AND drawTime <= '".$endDate."' group by dates",$rsm);
//        print_r($qb->getSQL());
        
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select("dy")
                ->from(self::ENTITY, 'dy')
                ->where('dy.drawTime >= '.$qb->expr()->literal($startDate))
                ->andWhere('dy.drawTime <= '.$qb->expr()->literal($endDate))
                //->groupBy('dates')
                ->getQuery()
                ->getArrayResult();
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
    
    
    /**
     * delete notification by date
     */
    public function deleteDrawByLowerDate($date) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->delete(self::ENTITY,'dy')
                ->where('dy.drawTime < '.$qb->expr()->literal($date))
                ->getQuery()
                ->execute();
    }
}
