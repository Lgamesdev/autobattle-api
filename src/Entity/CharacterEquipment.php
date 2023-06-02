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
#[UniqueEntity(
    fields: ['character', 'equipment', 'equipmentSlot'],
    message: 'This equipmentSlot is already used.'
)]
class CharacterEquipment extends BaseCharacterItem
{
    #[Groups(['gear', 'playerInventory', 'fighter', 'opponent_fighter'])]
    #[ManyToOne(targetEntity: Equipment::class)]
    #[JoinColumn(name: 'equipment_id', referencedColumnName: 'id')]
    protected Equipment $item;

    #[Groups(['gear', 'playerInventory', 'fighter', 'opponent_fighter'])]
    #[OneToMany(mappedBy: 'characterEquipment', targetEntity: CharacterEquipmentModifier::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $modifiers;

    public function __construct(Equipment $item = null)
    {
        $this->modifiers = new ArrayCollection();

        if($item != null) {
            $this->item = $item;
        }
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->item->getEquipmentSlot();
    }

    public function getItem(): Equipment
    {
        return $this->item;
    }

    public function setItem(Equipment $item): void
    {
        $this->item = $item;
    }

    public function getModifiers(): Collection
    {
        return $this->modifiers;
    }

    public function addModifier(CharacterEquipmentModifier $stat): self
    {
        $statsMatched = $this->modifiers->filter(function($element) use ($stat) {
            return $element->getStatType() === $stat->getStatType();
        });

        if($statsMatched->count() > 0) {
            $statValue = $statsMatched->first()->getValue();
            $this->modifiers[$statsMatched->key()]->setValue($statValue + $stat->getValue());
        } else {
            $this->modifiers[] = $stat;
            $stat->setCharacterEquipment($this);
        }

        return $this;
    }
}