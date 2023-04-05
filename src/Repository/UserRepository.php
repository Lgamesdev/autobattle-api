<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneByResetToken(string $resetToken)
    {
        return  $this->findOneBy(['resetToken' => $resetToken]);
    }
}