<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CharacterEquipment;
use App\Entity\Equipment;
use App\Entity\UserCharacter;
use App\Enum\EquipmentSlot;
use App\Enum\ItemQuality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRepository extends ServiceEntityRepository
{
    private const LEVEL_GAP = 10;

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

    /**
     * @return Equipment[]
     */
    public function findByLevelAndItemQuality(int $level, ItemQuality $itemQuality): array
    {
        $minLevel = max($level == UserCharacter::MAX_LEVEL ? $level : ($level - self::LEVEL_GAP), 1);
        $maxLevel = min($level == UserCharacter::MAX_LEVEL ? $level : ($level + self::LEVEL_GAP), UserCharacter::MAX_LEVEL);

        return $this->createQueryBuilder('e')
            ->where('e.requiredLevel >= :minLevel')
            ->andWhere('e.requiredLevel <= :maxLevel')
            ->andWhere('e.itemQuality = :itemQuality')
            ->setParameter('minLevel', $minLevel)
            ->setParameter('maxLevel', $maxLevel)
            ->setParameter('itemQuality', $itemQuality)
            ->getQuery()
            ->getResult();
    }
}