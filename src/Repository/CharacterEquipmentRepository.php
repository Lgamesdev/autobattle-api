<?php

namespace App\Repository;

use App\Entity\CharacterEquipment;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterEquipment::class);
    }

    public function findCharacterEquipments(UserCharacter $character)
    {
        return $this->createQueryBuilder('ce')
            ->where(' ce.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}