<?php

namespace App\Repository;

use App\Entity\CharacterEquipment;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterEquipment::class);
    }

    public function findCharacterEquipments(UserCharacter $character): array
    {
        /*$qb = $this->getEntityManager()->getRepository(EquipmentSlot::class)
            ->createQueryBuilder('es')
            ->select('ce')
            ->leftJoin(CharacterEquipment::class, 'ce',
                Join::WITH, 'ce.equipmentSlot = es.id AND ce.character = :character')
            ->setParameter('character', $character);*/

        $qb = $this->createQueryBuilder('character_equipment')
            ->where('character_equipment.character = :character')
            ->setParameter('character', $character);

        return $qb->getQuery()->getResult();
    }
}