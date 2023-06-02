<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\LootBox;
use App\Enum\ItemQuality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (ItemQuality::cases() as $quality)
        {
            $lootBox = new LootBox();
            $lootBox->setName(ucfirst($quality->value) . ' LootBox');
            $lootBox->setItemQuality($quality);
            $lootBox->setIconPath('Icons/Item/LootBox/' . $quality->value . ' LootBox');
            $lootBox->setCost(match($quality){
                ItemQuality::NORMAL => 60000,
                ItemQuality::RARE => 80000,
                ItemQuality::EPIC => 100000,
                ItemQuality::LEGENDARY => 150000,
            });

            $manager->persist($lootBox);
        }

        $manager->flush();
    }
}
