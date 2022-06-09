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

        for($i = 1; $i <= 5; ++$i) {
            $currencyType = new CurrencyType();
            $currencyType->setLabel(sprintf('CURR%d', $i));
            $manager->persist($currencyType);
        }

        $manager->flush();
    }
}
