<?php

namespace User\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class User implements ServiceManagerAwareInterface {

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'User\Model\Entity\User';

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
	
   /*
    * Check is Logged User
    */
    public function isLoggedUser($post) {
        $obj = $this->repository->findOneBy(array('loginToken' => $post['token']));
        if (!$obj)
            return 0;
        else
            return $obj->Id;
    }
    
    /**
     * Create New User
     */
    public function createUser($post) {
        $entityPath = self::ENTITY;
        $userEntity = new $entityPath;
        $userEntity->populate($post);
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();
        return $userEntity;
    }
    
    /**
     *  Update User Details
     *  @param int $userId
     */
    public function updateUser($userId,$data) {
        $userEntity = $this->repository->findOneBy(array('Id' => $userId));
        $userEntity->populate($data);
        $this->entityManager->merge($userEntity);
        $this->entityManager->flush();
        return $userEntity;
    }
    
    /**
     * update user Balance
     */
    public function updateUserBal($userId,$bal) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->update(self::ENTITY, 'u')
                  ->set('u.avaiPurchaseBal', 'u.avaiPurchaseBal+'.$bal['avaiPurchaseBal'])
                  ->set('u.totalWinBal', 'u.totalWinBal+'.$bal['totalWinBal'])
                  ->where('u.Id = '.$userId)
                  ->getQuery()
                  ->execute();
    }
    
    /**
     * get user by phone no
     */
    public function getUserByPhoneNo($poneNo) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.phoneNo =' . $poneNo)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * get user by login Token
     */
    public function getUserByToken($token) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.loginToken =' .$qb->expr()->literal($token))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * get user by Id
     */
    public function getUserById($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.Id =' . $userId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * check login credential
     */
    public function userLogin($post) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.phoneNo =' . $post['phoneNo'].' AND '.'u.password = '.$qb->expr()->literal(sha1($post['password'])))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Check account verify code exist
     */
    public function verifyCodeExist($verifyCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.verifyCode =' . $verifyCode)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
    
    /**
     * Check password verify code exist
     */
    public function passwordVerifyCodeExist($verifyCode) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u')
                ->from(self::ENTITY, 'u')
                ->where('u.passVerifyCode =' . $verifyCode)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    } 
    
    
    
    
    
    
    
    
    
    
    

    /**
     *  Set contacts By Id Only
     *  @param int $userId and contactid
     */
    public function UpdateContactById($data, $contactId) {
        if(isset($data['email']))
            $data['email'] = strtolower($data['email']);
        $userEntity = $this->repository->findOneBy(array('id' => $contactId));
        $userEntity->populate($data);
        $this->entityManager->merge($userEntity);
        $this->entityManager->flush();
        return $userEntity;
    }
    /**
     *  Set contacts By Id Only
     *  @param int $userId and contactid
     */
    public function UpdateContactByEmail($data, $email) {
        if(isset($data['email']))
            $data['email'] = strtolower($data['email']);
        $userEntity = $this->repository->findOneBy(array('email' => strtolower($email)));
        if(isset($userEntity)){
            $userEntity->populate($data);
            $this->entityManager->merge($userEntity);
            $this->entityManager->flush();
        }
        return $userEntity;
    }
    
    /**
     * get contact details by contactId
     */
    public function getContact($userId, $contactId) {
        if(is_array($contactId))
            $contactId  = implode(',',$contactId);
            
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.id in(' . $contactId . ') and c.userId =' . $userId)
                       // ->setMaxResults(1)
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * get all contact details by userId
     */
    public function getAllContactByUserId($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.userId =' . $userId)
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * get all contact details by ContactuserId
     */
    public function getAllContactByContactUserId($contactUserId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.contactUserId =' . $contactUserId)
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * Get Contact details of the User 
     * @param int $userId User
     * @return array Contacts details
     */
    public function getContacts($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('u.firstName,u.lastName,u.email, u.user, u.Image, c.contactUserId as cid, c.id')
                        ->from(self::ENTITY, 'c')
                        ->innerJoin('Api\Model\Entity\Users', 'u', 'WITH', 'u.id = c.contactUserId')
                        ->where('c.userId =' . $userId)
                        ->orderBy('u.firstName', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * set Contact details of the User 
     * @param string $email User
     * @return array Contacts details
     */
    public function setContactUserIdByEmail($userId, $email) {
        $email = strtolower($email);
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->update(self::ENTITY, 'c')
                        ->set('c.contactUserId', $userId)
                        ->where('c.email =' . $qb->expr()->literal(strtolower($email)))
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * Get Contact details of the User 
     * @param string $email User
     * @return array Contacts details
     */
    public function getAllUserIdByEmail($email) {
        $email = strtolower($email);
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.userId')
                        ->from(self::ENTITY, 'c')
                        ->where('c.email =' . $qb->expr()->literal(strtolower($email)))
                        ->getQuery()
                        ->execute();
    }

    /**
     * Delete the Contact from User List
     * @param int $userId User
     * @param int $contactId Contact
     */
    public function delContact($userId, $contactId) {
        $qb = $this->entityManager->createQueryBuilder();

        if (is_array($contactId)) {
            $qb->delete(self::ENTITY, 'c')
                    ->where('c.userId =' . $userId)
                    ->andWhere($qb->expr()->in('c.id', $contactId))
                    ->getQuery()
                    ->execute();
        } else {
            $qb->delete(self::ENTITY, 'c')
                    ->where('c.userId =' . $userId . 'and c.id =' . $contactId)
                    ->getQuery()
                    ->execute();
        }
    }

    /**
     * Get Contact details of the User 
     * @param int $userId User
     * @return array Contacts details
     */
    public function checkContactsExists($userId, $phoneNo = NULL, $email = NULL) {
        $qb = $this->entityManager->createQueryBuilder();
        if(!empty($email))
            $email = strtolower($email);
        if (!empty($phoneNo) && !empty($email)) {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.userId =' . $userId . ' AND (c.phoneSMS =' . $qb->expr()->literal($phoneNo) . ' OR c.email =' . $qb->expr()->literal($email) . ')')
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        } else if (!empty($phoneNo)) {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.userId =' . $userId . ' AND c.phoneSMS =' . $qb->expr()->literal($phoneNo))
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        } else {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.userId =' . $userId . ' AND c.email =' . $qb->expr()->literal($email))
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        }
    }

    /**
     * Check Contact details of the User 
     * @param int $userId User
     * @return array Contacts details
     */
    public function checkContactExists($userId, $contactId, $phoneNo = NULL, $email = NULL) {
        if(!empty($email))
            $email = strtolower($email);
        $qb = $this->entityManager->createQueryBuilder();
        if (!empty($phoneNo) && !empty($email)) {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.id != ' . $contactId . ' AND c.userId =' . $userId . ' AND (c.phoneSMS =' . $qb->expr()->literal($phoneNo) . ' OR c.email =' . $qb->expr()->literal($email) . ')')
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        } else if (!empty($phoneNo)) {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.id != ' . $contactId . ' AND c.userId =' . $userId . ' AND c.phoneSMS =' . $qb->expr()->literal($phoneNo))
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        } else {
            return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                            ->from(self::ENTITY, 'c')
                            ->where('c.id != ' . $contactId . ' AND c.userId =' . $userId . ' AND c.email =' . $qb->expr()->literal($email))
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult();
        }
    }

    /**
     * Check Contact details of the User 
     * @param int $userId User
     * @return array Contacts details
     */
    public function checkContactsAddress($userId, $contactId, $Address) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('count(c.id) as cid, c.id,c.firstName,c.email,c.phoneSMS,c.Image')
                        ->from(self::ENTITY, 'c')
                        ->where('c.id != ' . $contactId . ' AND c.userId =' . $userId . ' AND c.address =' . $qb->expr()->literal($Address))
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    /**
     * Adds Contacts in Userlist
     * @param array $post user data
     * @return object entity
     */
    public function addContacts($post) {
        if(isset($post['email']))
            $post['email'] = strtolower($post['email']);
        $entityPath = self::ENTITY;
        $contactEntity = new $entityPath;
        $contactEntity->populate($post);
        $this->entityManager->persist($contactEntity);
        $this->entityManager->flush();
        return $contactEntity;
    }

    /**
     * Search Contacts By name in User's Addressbook
     * @param array $post user data
     * @return object entity
     */
    public function searchContacts($userId, $word, $contactIds) {
        $qb = $this->entityManager->createQueryBuilder();
        $concatFields = array(
            'LOWER(c.firstName)',
            'LOWER(c.lastName)',
            'LOWER(c.lastName)',
            'LOWER(c.firstName)',
        );

        // Routine code. All fields will be separated by ' '.
        foreach ($concatFields as $field) {
            if (!isset($searchIn)) {
                $searchIn = $qb->expr()->concat($qb->expr()->literal(' '), $field);
                continue;
            }

            $searchIn = $qb->expr()->concat(
                    $searchIn, $qb->expr()->concat($qb->expr()->literal(' '), $field)
            );
        }
        if(count($contactIds) > 0)
            $where = ' AND '.$qb->expr()->notIn('c.id', $contactIds);
        else
            $where = '';
        
        return $qb->select('c.id,c.firstName,c.lastName,c.contactUserId,c.email')
                        ->from(self::ENTITY, 'c')
                        ->where($qb->expr()->like('LOWER(c.firstName)', $qb->expr()->literal('%' . strtolower($word) . '%')) . ' OR ' . $qb->expr()->like('LOWER(c.lastName)', $qb->expr()->literal('%' . strtolower($word) . '%')))
                        ->orwhere($qb->expr()->like($searchIn, $qb->expr()->literal('%' . strtolower($word) . '%')))
                        ->andWhere('c.userId =' . $userId." AND c.importId is null".$where)
                        ->orderBy('c.firstName', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * Check email exist on user contact list 
     * @param int $userId User
     * @return array Contacts details
     */
    public function checkEmailExists($userId, $email) {
        if(!empty($email))
            $email = strtolower($email);
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('count(c.id) as counts')
                        ->from(self::ENTITY, 'c')
                        ->where('c.userId =' . $userId . ' AND c.email =' . $qb->expr()->literal($email))
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    /**
     * get User id by emai
     */
    public function getuserIdByemail($email) {
        if(!empty($email))
            $email = strtolower($email);
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.userId')
                        ->from(self::ENTITY, 'c')
                        ->where('c.email =' . $qb->expr()->literal($email))
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    /*
     * Get All Contact Details by userId
     */

    public function GetAllContactsById($id) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.id = ' . $id)
                        ->getQuery()
                        ->getArrayResult();
    }

    /*
     * Get Import Contact Details by UserId and ImportId
     */

    public function GetImportContactByImportId($userId, $importId) {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.userId = ' . $userId . ' AND c.importId =' . $importId)
                        ->getQuery()
                        ->getArrayResult();
    }

    /*
     * Check User is exist on Addressbook
     */

    public function checkUserExistOnAddressbook($userId, $selectedUserId) {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('c')
                        ->from(self::ENTITY, 'c')
                        ->where('c.userId = ' . $userId . ' AND c.contactUserId =' . $selectedUserId)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    /*
     * Get Multiple Contact Details By Ids
     */

    public function getContactDetailsByIds($userId, $contactIds) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.id,c.firstName,c.lastName,c.phoneSMS,c.contactUserId')
                        ->from(self::ENTITY, 'c')
                        ->where('c.userId = ' . $userId)
                        ->andWhere($qb->expr()->in('c.id', $contactIds))
                        ->groupBy('c.id')
                        ->getQuery()
                        ->getArrayResult();
    }
    /*
     * Get Multiple Contact Details By Ids
     */

    public function getContactByIds($contactIds) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.id,c.firstName,c.lastName,c.phoneSMS,c.contactUserId')
                        ->from(self::ENTITY, 'c')
                        ->where($qb->expr()->in('c.id', $contactIds))
                        ->groupBy('c.id')
                        ->getQuery()
                        ->getArrayResult();
    }

    /**
     * get contact details for get who invite me
     */
    public function getInviterContactList($userId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.userId,c.firstName,c.lastName,c.Image')
                        ->from(self::ENTITY, 'c')
                        ->where("c.userId =" . $userId . " AND c.contactUserId != ''")
                        ->getQuery()
                        ->execute();
    }

    /*
     * Get Sigle Contact Details And CompanyDeatils By Id
     */

    public function getContactAndCompanyDetailsById($userId, $contactId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.id,c.firstName,c.lastName,c.email,c.extraEmail,c.phoneSMS,c.extraPhoneSMS,c.Image,p.userDealTypes,p.Id,p.companyName,p.companyLogo,p.companyRelationship')
                        ->from(self::ENTITY, 'c')
                        ->leftjoin('Company\Model\Entity\CompanyUserProfile', 'p', 'with', 'c.contactUserId = p.userId')
                        ->where('c.userId = ' . $userId)
                        ->andWhere('c.id =' . $contactId)
                        ->getQuery()
                        ->getArrayResult();
    }
    
    /*
     * Get Multiple Contact Details By Ids
     */
    public function getContactUserContactId($userId,$contactId) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('c.id,c.firstName,c.lastName,c.phoneSMS,c.contactUserId')
                        ->from(self::ENTITY, 'c')
                        ->where($qb->expr()->eq('c.contactUserId', $userId))
                        ->orWhere($qb->expr()->eq('c.id', $contactId))
                        ->getQuery()
                        ->getArrayResult();
    }
    
    /*
     * Get Contact By Logged UserId And another UserId
     */
    public function getContactByOtherUserId($loggedUserId,$otherUserId) {
               
        $qb = $this->entityManager->createQueryBuilder();
        return  $qb->select('c.id,c.firstName,c.lastName,c.phoneSMS,c.contactUserId')
                        ->from(self::ENTITY, 'c')
                        ->where($qb->expr()->eq('c.contactUserId', $otherUserId))
                        ->orWhere($qb->expr()->eq('c.userId', $loggedUserId))
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        
    }

}
