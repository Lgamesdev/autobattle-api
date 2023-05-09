<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CharacterStat;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterStat::class);
    }

    public function findCharacterStats(UserCharacter $character)
    {
        return $this->createQueryBuilder('cs')
            ->where(' cs.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}