<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Body;
use App\Entity\Fight;
use App\Entity\Fighter;
use App\Entity\Hero;
use App\Entity\User;
use App\Entity\UserCharacter;
use App\Enum\FightType;
use App\Enum\StatType;
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

    private const HERO_NAME = [
        'Llyris',
        'Elidys',
        'Ito',
        'Tami',
        'Hywell',
        'Wrynn',
        'Eliss',
        'Olaka',
        'Echo'
    ];

    private const FIGHTER_HEALTH_MULTIPLIER = 250;
    private const FIGHTER_STRENGTH_MULTIPLIER = 15;
    private const FIGHTER_ARMOR_MULTIPLIER = 1;
    private const FIGHTER_AGILITY_MULTIPLIER = 1;
    private const FIGHTER_INTELLIGENCE_MULTIPLIER = 2;
    private const FIGHTER_LUCK_MULTIPLIER = 1;

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
    public function findOpponent(UserCharacter $character, FightType $fightType): Fighter
    {
        switch ($fightType) {
            case FightType::PVP:
                $minLevel = $character->getLevel() > 2 ? $character->getLevel() - 2 : 1;
                $maxLevel = $character->getLevel() <= 98 ? $character->getLevel() + 2 : 100;
                $minRanking = $character->getRanking() > 100 ? $character->getRanking() - 100 : 0;
                $maxRanking = $character->getRanking() <= 1900 ? $character->getRanking() + 100 : 2000;

                $opponent = null;
                $i = 0;

                while ($opponent == null && $i < self::MAX_ITERATION) {
                    try {
                        $opponent = $this->findNearestCharacter($character, $minLevel, $maxLevel, $minRanking, $maxRanking);
                    } catch (NoResultException $e) {
                        $minLevel = $minLevel > 2 ? $minLevel - self::LEVEL_ITERATION : 1;
                        $maxLevel = $maxLevel <= (self::MAX_LEVEL - self::LEVEL_ITERATION) ? $maxLevel + self::LEVEL_ITERATION : self::MAX_LEVEL;
                        $minRanking = $minRanking > 100 ? $minRanking - self::RANK_ITERATION : 0;
                        $maxRanking = $maxRanking <= 1900 ? $maxRanking + self::RANK_ITERATION : self::MAX_RANK;
                    }
                    $i++;
                }

                if ($opponent == null) {
                    throw new FightException("no opponent found to fight :(");
                }
                return $opponent;
            case FightType::PVE:
                $opponentLevel = 1;
                if ($character->getHeroDefeated() > 0) {
                    $opponentLevel = 5 * $character->getHeroDefeated();
                }

                $opponent = new Hero();
                $opponent->setUsername(self::HERO_NAME[array_rand(self::HERO_NAME)]);
                $opponent->setLevel($opponentLevel);

                $body = new Body();
                $body->setRandomCustomization();
                $opponent->setBody($body);

                foreach (StatType::cases() as $statType) {
                    $statValue = match ($statType) {
                        StatType::HEALTH => self::FIGHTER_HEALTH_MULTIPLIER * $opponentLevel,
                        StatType::ARMOR => self::FIGHTER_ARMOR_MULTIPLIER * $opponentLevel,
                        StatType::STRENGTH => self::FIGHTER_STRENGTH_MULTIPLIER * $opponentLevel,
                        StatType::AGILITY => self::FIGHTER_AGILITY_MULTIPLIER * $opponentLevel,
                        StatType::LUCK => self::FIGHTER_LUCK_MULTIPLIER * $opponentLevel,
                        StatType::INTELLIGENCE => self::FIGHTER_INTELLIGENCE_MULTIPLIER * $opponentLevel
                    };
                    $opponent->stat($statType, $statValue);
                }

                return $opponent;
        }

        throw new FightException('error while generating the fight.');
    }


    /**
     * @throws NoResultException
     */
    private function findNearestCharacter(UserCharacter $character, $minLevel, $maxLevel, $minRanking, $maxRanking) : UserCharacter
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