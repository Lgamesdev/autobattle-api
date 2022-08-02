<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: WalletRepository::class)]
#[UniqueEntity(
    fields: ['character', 'currency'],
    message: 'This character already got an amount of this currency'
)]
class Wallet
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'wallet')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups('wallet')]
    #[ManyToOne(targetEntity: Currency::class)]
    #[JoinColumn(name: 'currency_id', referencedColumnName: 'id')]
    private Currency $currency;

    #[Groups('wallet')]
    #[Column(type: Types::INTEGER)]
    private int $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): UserCharacter
    {
        return $this->character;
    }

    public function setCharacter(UserCharacter $character): void
    {
        $this->character = $character;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}
