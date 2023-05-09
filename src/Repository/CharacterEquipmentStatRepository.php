<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CharacterEquipmentModifier;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterEquipmentStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterEquipmentModifier::class);
    }

    public function findCharacterEquipmentStats(UserCharacter $character)
    {
        return $this->createQueryBuilder('cs')
            ->select(['s.label', 'cs.value'])
            ->join('cs.stat', 's')
            ->where(' cs.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}