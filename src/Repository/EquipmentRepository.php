<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Equipment;
use App\Enum\EquipmentSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function findByEquipmentSlot(EquipmentSlot $equipSlot): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.equipmentSlot = :equipmentSlot')
            ->setParameter('equipmentSlot', $equipSlot)
            ->getQuery()
            ->getResult();
    }
}