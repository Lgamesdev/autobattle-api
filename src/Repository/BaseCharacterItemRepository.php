<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BaseCharacterItem;
use App\Exception\UserCharacterException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class BaseCharacterItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseCharacterItem::class);
    }

    /**
     * @throws UserCharacterException
     */
    public function findById(int $id): BaseCharacterItem
    {
        $qb = $this->createQueryBuilder('base_character_item')
            ->where('base_character_item.id = :id')
            ->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (Exception $e) {
            throw new UserCharacterException($e->getMessage());
        }
    }
}