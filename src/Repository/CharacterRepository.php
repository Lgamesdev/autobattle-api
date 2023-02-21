<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCharacter::class);
    }

    public function findPlayersByCharacterRank(UserCharacter $character)
    {
        return $this->createQueryBuilder('uc')
            ->where('uc != :character')
            ->setParameter('character', $character)
            ->orderBy('uc.ranking', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws Exception
     */
    public function findPlayerByUsername(string $username)
    {
        try {
            return $this->createQueryBuilder('uc')
                ->select('user')
                ->from(User::class, 'user')
                ->where('user.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getSingleResult()
                ->getCharacter();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}