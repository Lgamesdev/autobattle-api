<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EquipmentSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentSlot::class);
    }

    public function findAllIndexed(): array
    {
        return $this->createQueryBuilder('es')
            ->orderBy('es.label', 'ASC')
            ->indexBy('es', 'es.label')
            ->getQuery()
            ->getResult();
    }
}