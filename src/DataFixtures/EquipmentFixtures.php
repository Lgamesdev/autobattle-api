<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Equipment;
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
        'Infantry'
    ];

    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i <= count($this->equipmentSets) - 1; ++$i) {
            foreach (EquipmentSlot::cases() as $equipmentsSlot) {
                if($this->equipmentSets[$i] == 'Beginner' && $equipmentsSlot == EquipmentSlot::HELMET
                    || $this->equipmentSets[$i] == 'Infantry' && $equipmentsSlot == EquipmentSlot::WEAPON) {
                    continue;
                }

                $equipment = new Equipment();
                $equipment->setEquipmentSlot($equipmentsSlot->value);
                $equipment->setName($this->equipmentSets[$i] . ' ' . $equipmentsSlot->value);
                $equipment->setIconPath(sprintf('Icons/Equipment/%1$s/%2$s', $equipmentsSlot->value, $this->equipmentSets[$i]));
                $equipment->setCost(($i + 1) * 60);
                $equipment->setSpriteId($i + 1);
                $equipment->setIsDefaultItem(false);
                $equipment->setRequiredLevel($i * 8);

                switch ($equipment->getEquipmentSlot())
                {
                    case EquipmentSlot::HELMET:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 7 * $i + 1,
                                StatType::DODGE, StatType::DAMAGE => null,
                                StatType::ARMOR => 3 * $i + 1,
                                StatType::SPEED => $i + 1,
                                StatType::CRITICAL => 1 * $i + 1
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::CHEST:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 12 * $i + 1,
                                StatType::DODGE, StatType::CRITICAL => $i + 1,
                                StatType::ARMOR => 5 * $i + 1,
                                StatType::SPEED => (int)($i * 1.2) + 1,
                                StatType::DAMAGE => null,
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::PANTS:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH => 10 * $i + 1,
                                StatType::DODGE => $i + 2,
                                StatType::ARMOR => 6 * $i + 1,
                                StatType::CRITICAL => $i + 1,
                                StatType::SPEED => (int)($i * 1.5) + 1,
                                StatType::DAMAGE => null,
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                    case EquipmentSlot::WEAPON:
                        foreach (StatType::cases() as $statType) {
                            $statValue = match ($statType) {
                                StatType::HEALTH, StatType::ARMOR, StatType::DODGE => null,
                                StatType::CRITICAL => 2 * $i + 1,
                                StatType::SPEED => $i + 1,
                                StatType::DAMAGE => 10 * ($i + 1),
                            };
                            $equipment->stat($statType, $statValue);
                        }
                        break;
                }

                /*foreach (StatType::cases() as $statType) {
                    if ($statType != StatType::DAMAGE && rand(0, 100) < 50) {
                        $statValue = match ($statType) {
                            StatType::HEALTH => 15 * $i + 1,
                            StatType::DODGE => null,
                            StatType::ARMOR => $equipment->getEquipmentSlot() == EquipmentSlot::WEAPON ? null : 5 * $i + 1,
                            StatType::SPEED => $i + 1,
                            StatType::CRITICAL => 2 * $i + 1
                        };
                        $equipment->stat($statType, $statValue);
                    } else if ($statType == StatType::DAMAGE && $equipment->getEquipmentSlot() == EquipmentSlot::WEAPON) {
                        $statValue = 10 * $i + 1;
                        $equipment->stat($statType, $statValue);
                    }
                }*/

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
