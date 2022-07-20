<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Equipment;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function findEquipementsByUser(User $user)
    {
        return $this->createQueryBuilder('e')
            ->where(' c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}