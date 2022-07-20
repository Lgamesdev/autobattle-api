<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
use App\Entity\EquipmentSlot;
use App\Entity\StatType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class StatTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $health = new StatType();
        $health->setLabel('Health');
        $manager->persist($health);

        $armor = new StatType();
        $armor->setLabel('Armor');
        $manager->persist($armor);

        $attack = new StatType();
        $attack->setLabel('Attack');
        $manager->persist($attack);

        $speed = new StatType();
        $speed->setLabel('Speed');
        $manager->persist($speed);

        $critical = new StatType();
        $critical->setLabel('Critical');
        $manager->persist($critical);

        $manager->flush();
    }
}
