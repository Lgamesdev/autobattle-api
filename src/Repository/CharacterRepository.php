<?php

namespace App\Repository;

use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCharacter::class);
    }

    public function findPlayersByCharacterRank(UserCharacter $character)
    {
        $characterRepository = $this->getEntityManager()->getRepository(UserCharacter::class);

        return $characterRepository->createQueryBuilder('uc')
            ->where('uc != :character')
            ->setParameter('character', $character)
            ->orderBy('uc.ranking', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }
}