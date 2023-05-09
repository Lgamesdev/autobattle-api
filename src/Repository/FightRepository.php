<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Body;
use App\Entity\Fight;
use App\Entity\User;
use App\Entity\UserCharacter;
use App\Exception\FightException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FightRepository extends ServiceEntityRepository
{
    private const MAX_LEVEL = 100;
    private const MAX_RANK = 2000;
    private const MAX_ITERATION = 50;
    private const LEVEL_ITERATION = self::MAX_LEVEL / self::MAX_ITERATION;
    private const RANK_ITERATION = self::MAX_RANK / self::MAX_ITERATION;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fight::class);
    }

    /**
     * @throws FightException
     */
    public function createFight(UserCharacter $character): Fight
    {
        $opponent = $this->findOpponent($character);

        $fight = new Fight();
        $fight->setCharacter($character);
        $fight->setOpponent($opponent);
        $fight->generate();

        return $fight;
    }

    /**
     * @throws FightException
     */
    public function findOpponent(UserCharacter $character): UserCharacter
    {
        $minLevel = $character->getLevel() > 2 ? $character->getLevel() - 2 : 1;
        $maxLevel = $character->getLevel() <= 98 ? $character->getLevel() + 2 : 100;
        $minRanking = $character->getRanking() > 100 ? $character->getRanking() - 100 : 0;
        $maxRanking = $character->getRanking() <= 1900 ? $character->getRanking() + 100 : 2000;

        $opponent = null;
        $i = 0;

        while($opponent == null && $i < self::MAX_ITERATION) {
            try {
                /** @var UserCharacter $opponent */
                $opponent = $this->findNearestCharacter($character, $minLevel, $maxLevel, $minRanking, $maxRanking);
            } catch (NoResultException $e) {
                $minLevel = $minLevel > 2 ? $minLevel - self::LEVEL_ITERATION : 1;
                $maxLevel = $maxLevel <= (self::MAX_LEVEL - self::LEVEL_ITERATION) ? $maxLevel + self::LEVEL_ITERATION : self::MAX_LEVEL;
                $minRanking = $minRanking > 100 ? $minRanking - self::RANK_ITERATION : 0;
                $maxRanking = $maxRanking <= 1900 ? $maxRanking + self::RANK_ITERATION : self::MAX_RANK;
            }
            $i++;
        }

        if($opponent == null) {
            throw new FightException("no opponent found to fight :(");
        }

        return $opponent;
    }

    /**
     * @throws NoResultException
     */
    private function findNearestCharacter(UserCharacter $character, $minLevel, $maxLevel, $minRanking, $maxRanking)
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