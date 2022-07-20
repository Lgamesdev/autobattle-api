<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CurrencyType;
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
        /** @var array<array-key, CurrencyType> $currencyTypes */
        $currencyTypes = $manager->getRepository(CurrencyType::class)->findAll();

        for($i = 1; $i <= 5; ++$i) {
            $user = new User();
            $user->setUsername(sprintf('user+%d', $i));
            $user->setEmail(sprintf('user+%d@email.com', $i));
            $user->setPassword($this->userPasswordHarsher->hashPassword($user, 'password'));
            $user->setRandomCustomization();

            foreach ($currencyTypes as $currencyType)
            {
                $user->currency($currencyType, rand(100, 150));
            }

            $manager->persist($user);
        }

        $manager->flush();
    }

    function getDependencies() : array
    {
        return [
            CurrencyTypeFixtures::class,
        ];
    }
}
