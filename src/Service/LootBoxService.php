<?php

namespace App\Service;

use App\Entity\CharacterEquipment;
use App\Entity\CharacterEquipmentModifier;
use App\Entity\EquipmentStat;
use App\Entity\CharacterLootBox;
use App\Entity\Reward;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;

class LootBoxService
{
    private const MODIFIER_MAX_VALUE = 20;
    private const MODIFIER_PROBABILITY = 50;

    private EquipmentRepository $equipmentRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, EquipmentRepository $equipmentRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
        $this->em = $em;
    }

    public function openLootBox(CharacterLootBox $lootBox): CharacterEquipment
    {
        $character = $lootBox->getCharacter();

        $level = $lootBox->getCharacter()->getLevel();
        $equipments = $this->equipmentRepository->findByLevelAndItemQuality($level, $lootBox->getItem()->getItemQuality());

        $equipment = $equipments[array_rand($equipments)];

        $reward = new Reward();
        $reward->addItem($equipment);
        $lootBox->setReward($reward);

        $this->em->persist($reward);

        $characterEquipment = new CharacterEquipment();
        $characterEquipment->setItem($equipment);

        /** @var EquipmentStat $stat */
        foreach ($characterEquipment->getItem()->getStats() as $stat)
        {
            if (rand(0, 100) >= self::MODIFIER_PROBABILITY) {
                $modifier = new CharacterEquipmentModifier();
                $modifier->setStat($stat->getStat());
                $modifier->setValue((int)($stat->getValue() * (rand(0, self::MODIFIER_MAX_VALUE) / 100)));

                $characterEquipment->addModifier($modifier);
            }
        }

        $character->addToInventory($characterEquipment);

        $this->em->persist($character);
        $this->em->flush();

        return $characterEquipment;
    }
}