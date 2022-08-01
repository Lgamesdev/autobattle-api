<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\CurrencyType;
use App\Entity\Equipment;
use App\Entity\EquipmentSlot;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 5; ++$i) {
            $item = new Item();
            $item->setName(sprintf('Item%d', $i));
            $item->setIconPath(sprintf('Icon/Item/item_%d.png', $i));
            $item->setCost(rand(20, 40));

            $manager->persist($item);
        }

        $manager->flush();
    }
}
