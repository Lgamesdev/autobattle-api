<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CharacterEquipment;
use App\Entity\CharacterEquipmentModifier;
use App\Entity\Currency;
use App\Entity\Equipment;
use App\Enum\CurrencyType;
use App\Enum\EquipmentSlot;
use App\Entity\Statistic;
use App\Entity\User;
use App\Enum\StatType;
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
        for($i = 1; $i <= 25; ++$i) {
            $user = new User();
            $user->setUsername(sprintf('user+%d', $i));
            $user->setEmail(sprintf('user+%d@email.com', $i));
            $user->setPassword($this->userPasswordHarsher->hashPassword($user, 'password'));

            $character = $user->getCharacter();
            $character->getBody()->setRandomCustomization();
            $character->setRanking(rand(100, 200));

            foreach (StatType::cases() as $statType) {
                $statValue = match ($statType) {
                    StatType::HEALTH => 100,
                    StatType::ARMOR => null,
                    StatType::DODGE => 2,
                    StatType::SPEED, 4,
                    StatType::DAMAGE => 10,
                    StatType::CRITICAL => 5
                };
                $character->stat($statType, $statValue);
            }

            foreach (CurrencyType::cases() as $currencyType)
            {
                $character->currency($currencyType, 200);
            }

            /*foreach (EquipmentSlot::cases() as $equipmentSlot)
            {
                if(rand(0, 100) < 50)
                {
                    // @var array<array-key, Equipment> $equipments
                    $equipments = $manager->getRepository(Equipment::class)->findByEquipmentSlot($equipmentSlot);
                    $equipment = $equipments[rand(0, (count($equipments) - 1))];
                    $characterEquipment = new CharacterEquipment($equipment);

                    foreach (StatType::cases() as $statType) {
                        $statValue = match ($statType) {
                            StatType::HEALTH => rand(0, 5),
                            StatType::ARMOR => ($equipmentSlot == EquipmentSlot::WEAPON) ? null : rand(0, 1),
                            StatType::SPEED, StatType::DODGE => ($equipmentSlot == EquipmentSlot::WEAPON) ? null : rand(0, 2),
                            StatType::DAMAGE => ($equipmentSlot == EquipmentSlot::WEAPON) ? rand(0, 4) : null,
                            StatType::CRITICAL => ($equipmentSlot == EquipmentSlot::WEAPON) ? rand(0, 2) : null
                        };

                        if ($statValue != null) {
                            $equipmentModifier = new CharacterEquipmentModifier();
                            $equipmentModifier->setStat($statType);
                            $equipmentModifier->setValue($statValue);

                            $characterEquipment->addModifier($equipmentModifier);
                        }
                    }

                    $character->equip($characterEquipment);
                }
            }*/

            $manager->persist($user);
        }

        $manager->flush();
    }

    function getDependencies() : array
    {
        return [
            ItemFixtures::class,
            EquipmentFixtures::class
        ];
    }
}
