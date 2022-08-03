<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Equipment;
use App\Entity\EquipmentSlot;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function findByEquipmentSlot(EquipmentSlot $equipSlot)
    {
        return $this->createQueryBuilder('e')
            ->join('e.equipmentSlot', 'es')
            ->where(' e.equipmentSlot = :equipmentSlot')
            ->setParameter('equipmentSlot', $equipSlot)
            ->getQuery()
            ->getResult();
    }
}