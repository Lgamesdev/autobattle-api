<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findWalletByUserIndexed(User $user)
    {
        return $this->createQueryBuilder('c')
            ->select(['ct.label', 'c.amount'])
            ->join('c.currencyType', 'ct')
            ->where(' c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}