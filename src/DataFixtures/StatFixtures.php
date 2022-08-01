<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
use App\Entity\EquipmentSlot;
use App\Entity\Statistic;
use App\Entity\StatType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class StatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $health = new Statistic();
        $health->setLabel('Health');
        $manager->persist($health);

        $armor = new Statistic();
        $armor->setLabel('Armor');
        $manager->persist($armor);

        $damage = new Statistic();
        $damage->setLabel('Damage');
        $manager->persist($damage);

        $speed = new Statistic();
        $speed->setLabel('Speed');
        $manager->persist($speed);

        $critical = new Statistic();
        $critical->setLabel('Critical');
        $manager->persist($critical);

        $manager->flush();
    }
}
