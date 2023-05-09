<?php

namespace App\Repository;

use App\Entity\CharacterEquipment;
use App\Entity\UserCharacter;
use App\Exception\CharacterEquipmentException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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

    /**
     * @throws CharacterEquipmentException
     */
    public function findById(int $id): CharacterEquipment
    {
        $qb = $this->createQueryBuilder('character_equipment')
            ->where('character_equipment.id = :id')
            ->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (Exception $e) {
            throw new CharacterEquipmentException($e->getMessage());
        }
    }
}