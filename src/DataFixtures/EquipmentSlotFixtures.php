<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
use App\Entity\EquipmentSlot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EquipmentSlotFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $head = new EquipmentSlot();
        $head->setLabel('Head');
        $manager->persist($head);

        $chest = new EquipmentSlot();
        $chest->setLabel('Chest');
        $manager->persist($chest);

/*        $feet = new EquipmentSlot();
        $feet->setLabel('Feet');
        $manager->persist($feet);*/

        $weapon = new EquipmentSlot();
        $weapon->setLabel('Weapon');
        $manager->persist($weapon);

        $manager->flush();
    }
}
