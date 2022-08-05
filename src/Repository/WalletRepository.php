<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserCharacter;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findCharacterWallet(UserCharacter $character)
    {
        return $this->createQueryBuilder('w')
            ->select(['c.label as type', 'uc.amount'])
            ->join('w.currencies', 'uc')
            ->join('uc.currency', 'c')
            ->where(' w.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}