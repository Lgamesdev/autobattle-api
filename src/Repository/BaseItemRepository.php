<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BaseItem;
use App\Entity\Item;
use App\Exception\ShopException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Base;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class BaseItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseItem::class);
    }

    public function findEquipments(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.type = Equipment')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws ShopException
     */
    public function findById(int $id): BaseItem
    {
        $qb = $this->createQueryBuilder('base_item')
            ->where('base_item.id = :id')
            ->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (Exception $e) {
            throw new ShopException($e->getMessage());
        }
    }
}