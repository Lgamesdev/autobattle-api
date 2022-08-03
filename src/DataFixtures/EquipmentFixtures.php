<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\EquipmentSlot;
use App\Entity\Statistic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class EquipmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, EquipmentSlot> $equipmentsSlots */
        $equipmentsSlots = $manager->getRepository(EquipmentSlot::class)->findAll();

        /** @var array<array-key, Statistic> $statTypes */
        $statTypes = $manager->getRepository(Statistic::class)->findAll();

        foreach ($equipmentsSlots as $equipmentsSlot)
        {
            for($i = 1; $i <= 5; ++$i) {
                $equipment = new Equipment();
                $equipment->setEquipmentSlot($equipmentsSlot);
                $equipment->setName($equipmentsSlot->getLabel() . $i);
                $equipment->setIconPath(sprintf('Icon/Equipment/%1$s/%1$s_%2$d.png', $equipmentsSlot->getLabel(), $i));
                $equipment->setCost(rand(50, 100));
                $equipment->setSpritePath(sprintf('Equipment/%1$s/%1$s_%2$d.png', $equipmentsSlot->getLabel(), $i));
                //$equipment->setIsDefaultItem(false);

                foreach ($statTypes as $statType) {
                    if(rand(0, 100) < 50 || ($equipment->getEquipmentSlot()->getLabel() === 'Weapon' && $statType->getLabel() === 'Damage')) {
                        $statValue = match ($statType->getLabel()) {
                            'Health' => rand(30, 60),
                            'Armor', 'Speed' => rand(4, 12),
                            'Damage' => rand(12, 25),
                            'Critical' => rand(5, 18)
                        };
                        $equipment->stat($statType, $statValue);
                    }
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
            StatFixtures::class,
        ];
	}
}
