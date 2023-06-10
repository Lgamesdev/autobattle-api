<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EquipmentSlot;
use App\Enum\ItemType;
use App\Enum\StatType;
use App\Repository\EquipmentRepository;
use App\Trait\EntityEquipmentTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints\Range;

#[Entity(repositoryClass: EquipmentRepository::class)]
class Equipment extends BaseItem
{
    #[Exclude]
    protected ItemType $itemType = ItemType::EQUIPMENT;

    #[Column(type: 'string', enumType: EquipmentSlot::class)]
    protected EquipmentSlot $equipmentSlot;

    #[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList', 'lootBox'])]
    #[OneToMany(mappedBy: 'equipment', targetEntity: EquipmentStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $stats;

    #[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList', 'lootBox'])]
    #[Column(type: Types::INTEGER)]
    protected int $spriteId;

    /*#[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
    protected bool $isDefaultItem = false;*/

    #[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList', 'lootBox'])]
    #[Column(type: Types::INTEGER)]
    #[Range(min: 1, max: 100)]
    protected int $requiredLevel;

    #[Groups(['gear', 'playerInventory', 'shopList', 'lootBox'])]
    #[Column(type: Types::INTEGER)]
    protected int $cost;

    public function __construct()
    {
        $this->stats = new ArrayCollection();
    }

    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(EquipmentStat $stat): self
    {
        $statsMatched = $this->stats->filter(function($element) use ($stat) {
            return $element->getStatType() === $stat->getStatType();
        });

        if($statsMatched->count() > 0) {
            $statValue = $statsMatched->first()->getValue();
            $this->stats[$statsMatched->key()]->setValue($statValue + $stat->getValue());
        } else {
            $this->stats[] = $stat;
            $stat->setEquipment($this);
        }

        return $this;
    }

    public function stat(StatType $stat, ?int $value) : void
    {
        if ($value != null) {
            $newStat = new EquipmentStat();
            $newStat->setStat($stat);
            $newStat->setValue($value);
            $this->addStat($newStat);
        }
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->equipmentSlot;
    }

    public function setEquipmentSlot(string $value): void
    {
        $this->equipmentSlot = EquipmentSlot::from($value);
    }

    #[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList', 'lootBox'])]
    #[VirtualProperty]
    #[SerializedName('equipmentSlot')]
    public function getEquipmentSlotValue(): string
    {
        return $this->equipmentSlot->value;
    }

    public function getSpriteId(): int
    {
        return $this->spriteId;
    }

    public function setSpriteId(int $spriteId): void
    {
        $this->spriteId = $spriteId;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(int $level): void
    {
        $this->requiredLevel = $level;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }
}