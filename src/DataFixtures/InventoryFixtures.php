<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
use App\Entity\Equipment;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class InventoryFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        /** @var array<array-key, Item> $items */
        $items = $manager->getRepository(Item::class)->findAll();

        /** @var array<array-key, Equipment> $equipments */
        $equipments = $manager->getRepository(Equipment::class)->findAll();

        foreach($users as $user) {

            for($x = 1; $x <= rand(2, 5); ++$x) {
                $item = $items[array_rand($items)];
                $user->getInventory()->addItem($item);
            }

            for($x = 1; $x <= rand(2, 5); ++$x) {
                $equipment = $equipments[array_rand($equipments)];
                $user->getInventory()->addItem($equipment);
            }

            $manager->persist($user);
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
