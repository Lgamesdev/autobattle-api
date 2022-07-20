<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CurrencyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyType::class);
    }

    public function findAllIndexed(): array
    {
        return $this->createQueryBuilder('ct')
            ->orderBy('ct.label', 'ASC')
            ->indexBy('ct', 'ct.label')
            ->getQuery()
            ->getResult();
    }
}