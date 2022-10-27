<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Enum\StatType;
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
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: CharacterRepository::class)]
/*#[UniqueEntity(fields: 'name', message: 'This character\'s name is already used.')]*/
class UserCharacter
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[OneToOne(inversedBy: 'character', targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    #[Assert\Range(notInRangeMessage: "minimum character\'s level must be 1 at minimum", min: 1)]
    private int $level = 1;

    #[Groups(['fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $experience = 0;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $ranking = 150;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[OneToOne(mappedBy: 'character', targetEntity: Body::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Body $body;

    #[Groups(['fighter'])]
    #[OneToOne(mappedBy: 'character', targetEntity: Wallet::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Wallet $wallet;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[OneToMany(mappedBy: 'character', targetEntity: CharacterStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stats;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[OneToOne(mappedBy: 'character', targetEntity: Gear::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Gear $gear;

    #[OneToOne(mappedBy: 'character', targetEntity: Inventory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Inventory $inventory;

    #[OneToMany(mappedBy: 'character', targetEntity: Fight::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $fights;

    #[Column(type: Types::BOOLEAN)]
    private bool $creationDone = false;

    #[Column(type: Types::BOOLEAN)]
    private bool $tutorialDone = false;

    public function __construct()
    {
        $this->body = new Body();
        $this->body->setCharacter($this);

        $this->wallet = new Wallet();
        $this->wallet->setCharacter($this);

        $this->gear = new Gear();
        $this->gear->setCharacter($this);

        $this->inventory = new Inventory();
        $this->inventory->setCharacter($this);

        $this->stats = new ArrayCollection();
        $this->fights = new ArrayCollection();
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

    #[Groups(['fighter', 'opponent_fighter'])]
    #[VirtualProperty]
    #[SerializedName('username')]
    public function getUsername(): string
    {
        return $this->user->getUsername();
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): void
    {
        $this->experience = $experience;
    }

    public function addExperience(int $experience): void
    {
        $this->experience += $experience;

        while(!$this->isMaxLevel() && $this->experience >= $this->CalculateRequiredExperienceForLevel())
        {
            $this->experience -= $this->CalculateRequiredExperienceForLevel();
            $this->level++;
        }
    }

    public function getRanking(): int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }

    public function addRanking(int $ranking): void
    {
        $this->ranking += $ranking;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function setBody(Body $body): void
    {
        $this->body->setBeardIndex($body->getBeardIndex());
        $this->body->setChestColor($body->getChestColor());
        $this->body->setHairColor($body->getHairColor());
        $this->body->setHairIndex($body->getHairIndex());
        $this->body->setIsMaleGender($body->isMaleGender());
        $this->body->setMoustacheIndex($body->getMoustacheIndex());
        $this->body->setShortColor($body->getShortColor());
        $this->body->setSkinColor($body->getSkinColor());
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function currency(CurrencyType $currency, int $amount) : void
    {
        $curr = new Currency();
        $curr->setCurrency($currency);
        $curr->setAmount($amount);

        $this->wallet->addCurrency($curr);
    }

    public function tryBuy(BaseItem $item): CharacterItem|CharacterEquipment|null
    {
        if($item instanceof Item || $item instanceof Equipment) {
            return $this->wallet->tryBuy($item);
        } else {
            return null;
        }
    }

    public function sell(BaseCharacterItem $item): bool
    {
        if($item instanceof CharacterItem || $item instanceof CharacterEquipment) {
            return $this->wallet->sell($item);
        } else {
            return false;
        }
    }

    public function getStats(): ArrayCollection|Collection
    {
        return $this->stats;
    }

    public function addStat(CharacterStat $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats[] = $stat;
            $stat->setCharacter($this);
        }

        return $this;
    }

    public function stat(StatType $stat, ?int $value) : void
    {
        if($value != null) {
            $newStat = new CharacterStat();
            $newStat->setStat($stat);
            $newStat->setValue($value);
            $this->addStat($newStat);
        }
    }

    public function getGear(): Gear
    {
        return $this->gear;
    }

    public function equip(CharacterEquipment $characterEquipment): void
    {
        $this->gear->equip($characterEquipment);
    }

    public function unEquip(CharacterEquipment $characterEquipment): void
    {
        $this->gear->unEquip($characterEquipment);
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function addToInventory(CharacterItem|CharacterEquipment $item): void
    {
        $this->inventory->addCharacterItem($item);
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

    public function isMaxLevel(): bool
    {
        return $this->level == 200;
    }

    public function isMaxRank(): bool
    {
        return $this->ranking == 2000;
    }

    public function CalculateRequiredExperienceForLevel(): int
    {
        $solveForRequiredXp = 0;

        for ($levelCycle = 1; $levelCycle <= $this->level; $levelCycle++) {
            $solveForRequiredXp += Floor($levelCycle + 300 * Pow(2, $levelCycle / 14));
        }

        return $solveForRequiredXp / 4;
    }

    #[Groups(['fighter', 'opponent_fighter'])]
    #[VirtualProperty]
    #[SerializedName('fullStats')]
    public function getFullStats(): ArrayCollection
    {
        $fullStats = new ArrayCollection();

        /** @var CharacterStat $characterStat */
        foreach ($this->stats as $characterStat) {
            $fullStats[$characterStat->getStatType()] = $characterStat->getValue();
        }

        /** @var CharacterEquipment $equipment */
        foreach ($this->gear->getCharacterEquipments() as $equipment)
        {
            /** @var Equipment $item */
            $item = $equipment->getItem();

            foreach ($item->getStats() as $equipmentStat)
            {
                if(!$fullStats->containsKey($equipmentStat->getStatType())) {
                    $fullStats[$equipmentStat->getStatType()] = $equipmentStat->getValue();
                } else {
                    $fullStats[$equipmentStat->getStatType()] += $equipmentStat->getValue();
                }
            }
            /** @var CharacterEquipmentModifier $equipmentModifier */
            foreach ($equipment->getModifiers() as $equipmentModifier)
            {
                if(!$fullStats->containsKey($equipmentModifier->getStatType())) {
                    $fullStats[$equipmentModifier->getStatType()] = $equipmentModifier->getValue();
                } else {
                    $fullStats[$equipmentModifier->getStatType()] += $equipmentModifier->getValue();
                }
            }
        }

        return $fullStats;
    }

    public function getConf(): array
    {
        return [
            "creationDone" => $this->isCreationDone(),
            "tutorialDone" => $this->isTutorialDone()
        ];
    }
}