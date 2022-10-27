<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 5; ++$i) {
            $item = new Item();
            $item->setName(sprintf('Item%d', $i));
            $item->setIconPath(sprintf('Icons/Item/item_%d', $i));
            $item->setCost(rand(20, 40));

            $manager->persist($item);
        }

        $manager->flush();
    }
}
