<?php

namespace App\Entity;

use App\Enum\EquipmentSlot;
use App\Repository\CharacterEquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use JMS\Serializer\Annotation\Groups;

#[Entity(repositoryClass: CharacterEquipmentRepository::class)]
/*#[UniqueEntity(
    fields: ['character', 'equipment', 'equipmentSlot'],
    message: 'This equipmentSlot is already used.'
)]*/
class CharacterEquipment extends BaseCharacterItem
{
    #[Groups(['characterEquipment', 'playerInventory', 'fighter', 'opponent_fighter'])]
    #[ManyToOne(targetEntity: Equipment::class)]
    #[JoinColumn(name: 'equipment_id', referencedColumnName: 'id')]
    protected Equipment $equipment;

    #[Groups(['characterEquipment', 'playerInventory', 'fighter', 'opponent_fighter'])]
    #[OneToMany(mappedBy: 'characterEquipment', targetEntity: CharacterEquipmentModifier::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $modifiers;

    public function __construct(Equipment $equipment = null)
    {
        $this->modifiers = new ArrayCollection();

        if($equipment != null) {
            $this->equipment = $equipment;
        }
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->item->getEquipmentSlot();
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipment = $equipment;
    }

    public function getModifiers(): Collection
    {
        return $this->modifiers;
    }

    public function addModifier(CharacterEquipmentModifier $stat): self
    {
        if (!$this->modifiers->contains($stat)) {
            $this->modifiers[] = $stat;
            $stat->setCharacterEquipment($this);
        }

        return $this;
    }
}