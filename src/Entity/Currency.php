<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Wallet::class, inversedBy: 'currencies')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Wallet $wallet;

    #[ManyToOne(targetEntity: CurrencyType::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['get'])]
    private CurrencyType $currencyType;

    #[Column(type: Types::INTEGER)]
    #[Groups(['get'])]
    private int $amount;

    public function __construct(CurrencyType $currencyType, int $amount)
    {
        $this->currencyType = $currencyType;
        $this->amount = $amount;
    }

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

    public function getCurrencyType(): ?CurrencyType
    {
        return $this->currencyType;
    }

    public function setCurrencyType(?CurrencyType $currencyType): self
    {
        $this->currencyType = $currencyType;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
