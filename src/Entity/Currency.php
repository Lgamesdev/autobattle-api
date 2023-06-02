<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[Exclude]
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Exclude]
    #[Column(type: 'string', enumType: CurrencyType::class)]
    private CurrencyType $currency;

    #[Groups(['characterWallet', 'fight', 'fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): CurrencyType
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyType $currency): void
    {
        $this->currency = $currency;
    }

    #[Groups(['characterWallet', 'fight', 'fighter'])]
    #[VirtualProperty]
    #[SerializedName('currencyType')]
    public function getCurrencyType(): string
    {
        return $this->currency->value;
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