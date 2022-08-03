<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CharacterEquipment;
use App\Entity\Currency;
use App\Entity\Equipment;
use App\Entity\EquipmentSlot;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHarsher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, Currency> $currencyTypes */
        $currencyTypes = $manager->getRepository(Currency::class)->findAll();

        /** @var array<array-key, EquipmentSlot> $equipmentSlots */
        $equipmentSlots = $manager->getRepository(EquipmentSlot::class)->findAll();

        for($i = 1; $i <= 5; ++$i) {
            $user = new User();
            $user->setUsername(sprintf('user+%d', $i));
            $user->setEmail(sprintf('user+%d@email.com', $i));
            $user->setPassword($this->userPasswordHarsher->hashPassword($user, 'password'));

            $character = $user->getCharacter();
            $character->getBody()->setRandomCustomization();

            foreach ($currencyTypes as $currencyType)
            {
                $character->currency($currencyType, rand(100, 150));
            }

            foreach($equipmentSlots as $slot)
            {
                if(rand(0, 100) < 50)
                {
                    /** @var array<array-key, Equipment> $equipments */
                    $equipments = $manager->getRepository(Equipment::class)->findByEquipmentSlot($slot);
                    $equipment = $equipments[rand(0, (count($equipments) - 1))];

                    $characterEquipment = new CharacterEquipment();
                    $characterEquipment->setEquipment($equipment);
                    $character->addEquipment($characterEquipment);
                }
            }

            $manager->persist($user);
        }

        $manager->flush();
    }

    function getDependencies() : array
    {
        return [
            CurrencyFixtures::class,
        ];
    }
}
