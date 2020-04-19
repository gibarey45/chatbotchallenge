<?php

namespace App\Repository;

use App\Entity\UserTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTransaction[]    findAll()
 * @method UserTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTransaction::class);
    }

    /**
     * @param UserTransaction $entity
     * @param bool $flush
     * @return object|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function insert(UserTransaction $entity, bool $flush = true): ?UserTransaction
    {
        $this->_em->persist($entity);
        if($flush) {
            $this->_em->flush();
        }

        return $entity;
    }

    /**
     * @param UserTransaction $entity
     * @param bool $flush
     * @return UserTransaction|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(UserTransaction $entity, bool $flush = true): ?UserTransaction
    {
        $this->_em->persist($entity);
        if($flush) {
            $this->_em->flush();
        }

        return $entity;
    }
}
