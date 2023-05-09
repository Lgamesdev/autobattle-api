<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\UserCharacter;
use App\Enum\EquipmentSlot;
use App\Entity\Statistic;
use App\Enum\StatType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class EquipmentFixtures extends Fixture /*implements DependentFixtureInterface*/
{
    private array $equipmentSets = [
        'Beginner',
        'Iron',
        'Barbarian',
        'Gladiator',
        'Infantry',
        'Knight'
    ];

    public function load(ObjectManager $manager): void
    {
        $levelRequiredGap = (int)(UserCharacter::MAX_LEVEL / (count($this->equipmentSets) * count(EquipmentSlot::cases())));

        for($i = 0; $i <= count($this->equipmentSets) - 1; ++$i) {
            foreach (EquipmentSlot::cases() as $slotIndex => $equipmentsSlot) {
                if($this->equipmentSets[$i] == 'Beginner' && $equipmentsSlot == EquipmentSlot::HELMET) {
                    continue;
                }

                $equipment = new Equipment();
                $equipment->setEquipmentSlot($equipmentsSlot->value);
                $equipment->setName($this->equipmentSets[$i] . ' ' . $equipmentsSlot->value);
                $equipment->setIconPath(sprintf('Icons/Equipment/%1$s/%2$s', $equipmentsSlot->value, $this->equipmentSets[$i]));
                $equipment->setSpriteId($i + 1);
                $equipment->setIsDefaultItem(false);
                $equipment->setRequiredLevel((($i * 4) + ($slotIndex)) * $levelRequiredGap);
                $equipment->setCost((int)((($i * 4) + ($slotIndex)) * 150 * ($equipment->getRequiredLevel() * 0.1)));
                //$equipment->setRequiredLevel(($i * 8) + ($i + 1));

                switch ($equipment->getEquipmentSlot())
                {
                    case EquipmentSlot::HELMET:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 4 * $i + 1,
                                StatType::ARMOR => 2 * $i + 1,
                                StatType::STRENGTH => (int)($i * 1.1) + 1,
                                StatType::AGILITY, StatType::LUCK => 1 * $i + 1,
                                StatType::INTELLIGENCE => null
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::CHEST:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 10 * $i + 1,
                                StatType::ARMOR => 5 * $i + 1,
                                StatType::STRENGTH => (int)($i * 1.7) + 1,
                                StatType::AGILITY => (int)($i * 1.2) + 1,
                                StatType::LUCK => 1 * $i + 1,
                                StatType::INTELLIGENCE => null,
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::PANTS:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 6 * $i + 1,
                                StatType::ARMOR => 4 * $i + 1,
                                StatType::STRENGTH => (int)($i * 1.3) + 1,
                                StatType::AGILITY, StatType::LUCK => 1 * $i + 1,
                                StatType::INTELLIGENCE => null
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::WEAPON:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                                StatType::STRENGTH => 10 * ($i + 1),
                                StatType::LUCK => 2 * $i + 1,
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                }

                $manager->persist($equipment);
            }
        }

        $manager->flush();
    }

	/*function getDependencies(): array
    {
        return [
        ];
	}*/
}
