<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserCharacter;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findCharacterWallet(UserCharacter $character)
    {
        return $this->createQueryBuilder('w')
/*            ->select(['uc.currency', 'uc.amount'])
            ->join('w.currencies', 'uc')*/
            ->where(' w.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getSingleResult();
    }
}