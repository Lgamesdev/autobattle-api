<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PlayerCharacter;
use App\Entity\Equipment;
use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class InventoryFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, PlayerCharacter> $characters */
        $characters = $manager->getRepository(PlayerCharacter::class)->findAll();

        /** @var array<array-key, Item> $items */
        $items = $manager->getRepository(Item::class)->findAll();

        /** @var array<array-key, Equipment> $equipments */
        $equipments = $manager->getRepository(Equipment::class)->findAll();

        foreach($characters as $character) {

            for($x = 1; $x <= rand(2, 5); ++$x) {
                $item = $items[array_rand($items)];
                $character->getInventory()->addItem($item);
            }

            for($x = 1; $x <= rand(2, 5); ++$x) {
                $equipment = $equipments[array_rand($equipments)];
                $character->getInventory()->addItem($equipment);
            }

            $manager->persist($character);
        }

        $manager->flush();
    }

    function getDependencies() : array
    {
        return [
            ItemFixtures::class,
            EquipmentFixtures::class,
            UserFixtures::class
        ];
    }
}
