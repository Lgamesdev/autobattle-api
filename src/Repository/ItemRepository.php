<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findEquipments(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.type = Equipment')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}