<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findCharacterInventory(UserCharacter $character): Inventory
    {
        return $this->createQueryBuilder('i')
            ->where(' i.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getSingleResult();
    }
}