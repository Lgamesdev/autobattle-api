<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    public function findAllIndexed(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.label', 'ASC')
            ->indexBy('c', 'c.label')
            ->getQuery()
            ->getResult();
    }
}