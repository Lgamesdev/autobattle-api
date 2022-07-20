<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: CurrencyRepository::class)]
class Wallet
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(mappedBy: 'wallet', targetEntity: User::class)]
    private User $user;

    /**
     * Collection of Currency
     * @var Collection
     */
    #[OneToMany(mappedBy: 'wallet', targetEntity: Currency::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $currencies;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    function getCurrencies(): Collection
    {
        return $this->currencies;
    }

    public function add(Currency $currency): void
    {
        $this->currencies[] = $currency;
    }

    public function addCurrency(Currency $currency): self
    {
        dd($this->currencies);

        if (!$this->currencies->contains($currency)) {
            $this->currencies[] = $currency;
        }

        return $this;
    }
}
