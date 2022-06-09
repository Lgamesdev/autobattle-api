<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher) 
    {
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 5; ++$i) {
            $user = new User();
            $user->setUsername(sprintf('user+%d', $i));
            $user->setEmail(sprintf('user+%d@email.com', $i));
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
