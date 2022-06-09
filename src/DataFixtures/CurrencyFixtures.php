<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\CurrencyType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CurrencyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var array<array-key, User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        /** @var array<array-key, CurrencyType> $currencyTypes */
        $currencyTypes = $manager->getRepository(CurrencyType::class)->findAll();

        foreach ($currencyTypes as $currencyType) 
        {
            foreach($users as $user) {
                $currency = new Currency();
                $currency->setAmount(rand(135, 165));
                $currency->setCurrencyType($currencyType);
                $user->addCurrency($currency);

                $manager->persist($currency);
            }
        }

        $manager->flush();
    }

	function getDependencies() {
        return [
            CurrencyTypeFixtures::class,
            UserFixtures::class
        ];
	}
}
