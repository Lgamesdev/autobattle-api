<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: CharacterRepository::class)]
#[UniqueEntity(fields: 'name', message: 'This character\'s name is already used.')]
class UserCharacter
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(inversedBy: 'character', targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[Column(type: Types::INTEGER)]
    #[Assert\Range(notInRangeMessage: "minimum character\'s level must be 1", min: 1)]
    private int $level = 1;

    #[Column(type: Types::INTEGER)]
    private int $xp = 0;

    #[OneToOne(mappedBy: 'character', targetEntity: Body::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Body $body;

    #[OneToMany(mappedBy: 'character', targetEntity: Wallet::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $wallet;

    #[OneToMany(mappedBy: 'character', targetEntity: CharacterStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stats;

    #[OneToMany(mappedBy: 'character', targetEntity: CharacterEquipment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $equipments;

    #[OneToOne(mappedBy: 'character', targetEntity: Inventory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Inventory $inventory;

    #[Column(type: Types::BOOLEAN)]
    private bool $creationDone = false;

    #[Column(type: Types::BOOLEAN)]
    private bool $tutorialDone = false;

    public function __construct()
    {
        $this->body = new Body();
        $this->body->setCharacter($this);

        $this->inventory = new Inventory();
        $this->inventory->setCharacter($this);

        $this->wallet = new ArrayCollection();
        $this->stats = new ArrayCollection();
        $this->equipments = new ArrayCollection();
    }

    function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function setBody(Body $body): void
    {
        $this->body = $body;
    }

    public function getWallet(): ArrayCollection|Collection
    {
        return $this->wallet;
    }

    public function addCurrency(Wallet $wallet): self
    {
        if (!$this->wallet->contains($wallet)) {
            $this->wallet[] = $wallet;
            $wallet->setCharacter($this);
        }

        return $this;
    }

    //TODO: add currency get the correct object Wallet and add the amount to it
    public function currency(Currency $currency, int $amount) : void
    {
        $curr = new Wallet();
        $curr->setCurrency($currency);
        $curr->setAmount($amount);

        $this->addCurrency($curr);

//        dd($this->wallet);
//
//        $actualCurrency = $this->wallet->filter(function($element) use ($currency) {
//            return $element->getCurrency() == $currency;
//        });
    }

    public function getStats(): ArrayCollection|Collection
    {
        return $this->stats;
    }

    public function setStats(ArrayCollection|Collection $stats): void
    {
        $this->stats = $stats;
    }

    public function getEquipments(): ArrayCollection|Collection
    {
        return $this->equipments;
    }

    public function addEquipment(CharacterEquipment $equipment): self
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments[] = $equipment;
            $equipment->setCharacter($this);
        }

        return $this;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }

    public function isCreationDone(): bool
    {
        return $this->creationDone;
    }

    public function setCreationDone(bool $creationDone): void
    {
        $this->creationDone = $creationDone;
    }

    public function isTutorialDone(): bool
    {
        return $this->tutorialDone;
    }

    public function setTutorialDone(bool $tutorialDone): void
    {
        $this->tutorialDone = $tutorialDone;
    }

    public function getConf(): array
    {
        return [
            "level" => $this->getLevel(),
            "creationDone" => $this->isCreationDone(),
            "tutorialDone" => $this->isTutorialDone()
        ];
    }
}