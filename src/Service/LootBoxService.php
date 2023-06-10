<?php

namespace App\Service;

use App\Entity\CharacterEquipment;
use App\Entity\CharacterEquipmentModifier;
use App\Entity\EquipmentStat;
use App\Entity\CharacterLootBox;
use App\Entity\Reward;
use App\Exception\UserCharacterException;
use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @throws UserCharacterException
     */
    public function openLootBox(CharacterLootBox $lootBox): CharacterLootBox
    {
        $character = $lootBox->getCharacter();

        $level = $lootBox->getCharacter()->getLevel();
        $equipments = $this->equipmentRepository->findByLevelAndItemQuality($level, $lootBox->getItem()->getItemQuality());

        $equipment = $equipments[array_rand($equipments)];

        $characterEquipment = new CharacterEquipment($equipment);

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

        $reward = new Reward(new ArrayCollection(array($characterEquipment)));
        $lootBox->setReward($reward);

        $character->addToInventory($characterEquipment);

        $this->em->persist($characterEquipment);

        try {
            $character->removeFromInventory($lootBox);
        } catch (UserCharacterException $e) {
            throw new UserCharacterException('error while removing lootbox from inventory : ' . $e->getMessage());
        }

        if($this->em->isOpen()) {
            $this->em->persist($character);
            $this->em->flush();
        } else {
            echo "/!\ error : entity manager closed \n";
        }

        return $lootBox;
    }
}