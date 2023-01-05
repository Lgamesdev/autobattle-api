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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fight::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function createFight(UserCharacter $character): Fight
    {
        $minLevel = $character->getLevel() > 2 ? $character->getLevel() - 2 : 1;
        $maxLevel = $character->getLevel() <= 98 ? $character->getLevel() + 2 : 100;
        $minRanking = $character->getRanking() > 100 ? $character->getRanking() - 100 : 0;
        $maxRanking = $character->getRanking() <= 1900 ? $character->getRanking() + 100 : 2000;

        $opponent = null;
        $i = 0;

        while($opponent == null && $i < 35) {
            try {
                /** @var UserCharacter $opponent */
                $opponent = $this->findOpponent($character, $minLevel, $maxLevel, $minRanking, $maxRanking);
            } catch (NoResultException $e) {
                $minLevel = $character->getLevel() > 2 ? $minLevel - 1 : 1;
                $maxLevel = $character->getLevel() <= 98 ? $maxLevel + 1 : 100;
                $minRanking = $character->getRanking() > 100 ? $minRanking - 20 : 0;
                $maxRanking = $character->getRanking() <= 1900 ? $maxRanking + 20 : 2000;
            }
            $i++;
        }

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
    private function findOpponent(UserCharacter $character, $minLevel, $maxLevel, $minRanking, $maxRanking)
    {
        $characterRepository = $this->getEntityManager()->getRepository(UserCharacter::class);

        return $characterRepository->createQueryBuilder('uc')
            ->where(' uc.level >= :character_minLevel')
            ->andWhere(' uc.level <= :character_maxLevel')
            ->andWhere(' uc.ranking >= :character_minRanking')
            ->andWhere(' uc.ranking <= :character_maxRanking')
            ->andWhere(' uc != :character')
            ->setParameter('character_minLevel',  $minLevel)
            ->setParameter('character_maxLevel', $maxLevel)
            ->setParameter('character_minRanking', $minRanking)
            ->setParameter('character_maxRanking', $maxRanking)
            ->setParameter('character', $character)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}