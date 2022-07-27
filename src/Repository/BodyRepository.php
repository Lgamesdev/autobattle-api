<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CurrencyType;
use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BodyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findBodyByUser(User $user)
    {
        return $this->createQueryBuilder('b')
            ->where(' b.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}