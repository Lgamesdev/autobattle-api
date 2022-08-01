<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CurrencyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $gold = new Currency();
        $gold->setLabel('Gold');
        $gold->setDescription('common money to buy stuff in game.');
        $manager->persist($gold);

        $crystal = new Currency();
        $crystal->setLabel('Crystal');
        $manager->persist($crystal);

        $manager->flush();
    }
}
