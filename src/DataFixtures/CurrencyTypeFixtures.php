<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CurrencyTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $gold = new CurrencyType();
        $gold->setLabel('Gold');
        $manager->persist($gold);

        $crystal = new CurrencyType();
        $crystal->setLabel('Crystal');
        $manager->persist($crystal);

        $manager->flush();
    }
}
