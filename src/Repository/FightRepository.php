<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Body;
use App\Entity\Fight;
use App\Entity\User;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class FightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fight::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function createFight(UserCharacter $character): Fight
    {
        /** @var UserCharacter $opponent */
        $opponent = $this->findOpponent($character);

        $fight = new Fight();
        $fight->setCharacter($character);
        $fight->setOpponent($opponent);
        $fight->generate();

        return $fight;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function findOpponent(UserCharacter $character)
    {
        $characterRepository = $this->getEntityManager()->getRepository(UserCharacter::class);

        return $characterRepository->createQueryBuilder('uc')
            ->where(' uc.level >= :character_minLevel')
            ->andWhere(' uc.level <= :character_maxLevel')
            ->andWhere(' uc.ranking >= :character_minRanking')
            ->andWhere(' uc.ranking <= :character_maxRanking')
            ->andWhere(' uc != :character')
            ->setParameter('character_minLevel', $character->getLevel() > 2 ? $character->getLevel() - 2 : 1)
            ->setParameter('character_maxLevel', $character->getLevel() <= 198 ? $character->getLevel() + 2 : 200)
            ->setParameter('character_minRanking', $character->getRanking() > 100 ? $character->getRanking() - 100 : 0)
            ->setParameter('character_maxRanking', $character->getRanking() <= 1900 ? $character->getRanking() + 100 : 2000)
            ->setParameter('character', $character)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}