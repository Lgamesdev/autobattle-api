<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\StatType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StatTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatType::class);
    }

    public function findAllIndexed(): array
    {
        return $this->createQueryBuilder('st')
            ->orderBy('st.label', 'ASC')
            ->indexBy('st', 'st.label')
            ->getQuery()
            ->getResult();
    }
}