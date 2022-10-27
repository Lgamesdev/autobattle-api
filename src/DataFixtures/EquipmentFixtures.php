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
    public function load(ObjectManager $manager): void
    {
        foreach (EquipmentSlot::cases() as $equipmentsSlot)
        {
            for($i = 1; $i <= 4; ++$i) {
                $equipment = new Equipment();
                $equipment->setEquipmentSlot($equipmentsSlot->value);
                $equipment->setName($equipmentsSlot->value . $i);
                $equipment->setIconPath(sprintf('Icons/Equipment/%1$s/%1$s_%2$d', $equipmentsSlot->value, $i));
                $equipment->setCost(rand(50, 100));
                $equipment->setSpriteId($i);
                $equipment->setIsDefaultItem(false);

                foreach (StatType::cases() as $statType) {
                    if ($statType != StatType::DAMAGE && rand(0, 100) < 50) {
                        $statValue = match ($statType) {
                            StatType::HEALTH => rand(30, 60),
                            StatType::DODGE => null,
                            StatType::ARMOR => $equipment->getEquipmentSlot() == EquipmentSlot::WEAPON ? null : rand(4, 8),
                            StatType::SPEED => rand(3, 5),
                            StatType::CRITICAL => rand(5, 18)
                        };
                        $equipment->stat($statType, $statValue);
                    } else if ($statType == StatType::DAMAGE && $equipment->getEquipmentSlot() == EquipmentSlot::WEAPON) {
                        $statValue = rand(12, 25);
                        $equipment->stat($statType, $statValue);
                    }
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
