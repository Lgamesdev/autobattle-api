<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserCharacter;
use App\Exception\UserCharacterException;
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
     * @throws UserCharacterException
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
        } catch (NoResultException $e) {
            throw new UserCharacterException('User not found. error : ' . $e->getMessage());
        } catch (NonUniqueResultException $e) {
            throw new UserCharacterException('More than one user found. error : ' . $e->getMessage());
        }

    }
}