<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EquipmentSlot;
use App\Enum\StatType;
use App\Repository\EquipmentRepository;
use App\Trait\EntityEquipmentTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;

#[Entity(repositoryClass: EquipmentRepository::class)]
class Equipment extends Item
{
    #[Column(type: 'string', enumType: EquipmentSlot::class)]
    protected EquipmentSlot $equipmentSlot;

    #[Groups(['characterEquipment', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
    #[OneToMany(mappedBy: 'equipment', targetEntity: EquipmentStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $stats;

    #[Groups(['characterEquipment', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
    #[Column(type: Types::INTEGER)]
    protected int $spriteId;

    #[Groups(['characterEquipment', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
    protected bool $isDefaultItem = false;

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
        if (!$this->stats->contains($stat)) {
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

    #[Groups(['characterEquipment', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
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
}