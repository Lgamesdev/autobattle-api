<?php

namespace App\Entity;

use App\Repository\CharacterStatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: CharacterCurrencyRepository::class)]
#[UniqueEntity(
    fields: ['character', 'stat'],
    message: 'This character stat already got a value'
)]
class CharacterCurrency
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Wallet::class, inversedBy: 'currencies')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private Wallet $wallet;

    #[ManyToOne(targetEntity: Currency::class)]
    #[JoinColumn(name: 'currency_id', referencedColumnName: 'id')]
    private Currency $currency;

    #[Groups('characterWallet')]
    #[Column(type: Types::INTEGER)]
    private int $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }
    public function setWallet(Wallet $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    #[Groups('characterWallet')]
    public function getCurrencyType(): string
    {
        return $this->currency->getLabel();
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