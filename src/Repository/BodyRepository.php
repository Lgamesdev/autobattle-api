<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Body;
use App\Entity\User;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BodyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Body::class);
    }

    public function findCharacterBody(UserCharacter $character)
    {
        return $this->createQueryBuilder('b')
            ->where(' b.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}