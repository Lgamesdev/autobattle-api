<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\CurrencyType;
use App\Entity\Equipment;
use App\Entity\EquipmentSlot;
use App\Entity\StatType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class EquipmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, EquipmentSlot> $equipmentsSlots */
        $equipmentsSlots = $manager->getRepository(EquipmentSlot::class)->findAll();

        /** @var array<array-key, StatType> $statTypes */
        $statTypes = $manager->getRepository(StatType::class)->findAll();

        foreach ($equipmentsSlots as $equipmentsSlot)
        {
            for($i = 1; $i <= 5; ++$i) {
                $equipment = new Equipment($equipmentsSlot);
                $equipment->setName($equipmentsSlot->getLabel() . $i);
                $equipment->setIconPath(sprintf('Icon/Equipment/%1$s/%1$s_%2$d.png', $equipmentsSlot->getLabel(), $i));
                $equipment->setCost(rand(50, 100));
                $equipment->setSpritePath(sprintf('Equipment/%1$s/%1$s_%2$d.png', $equipmentsSlot->getLabel(), $i));
                //$equipment->setIsDefaultItem(false);

                for($x = 1; $x <= rand(2, 4); ++$x) {
                    $statType = $statTypes[array_rand($statTypes)];
                    $statValue = match($statType->getLabel()) {
                        'Health' => rand(30, 60),
                        'Armor', 'Speed' => rand(4, 12),
                        'Attack' => rand(12, 25),
                        'Critical' => rand(5, 18)
                    };

                    $equipment->stat($statType, $statValue);
                }

                $manager->persist($equipment);
            }
        }

        $manager->flush();
    }

	function getDependencies(): array
    {
        return [
            EquipmentSlotFixtures::class,
            StatTypeFixtures::class,
        ];
	}
}
