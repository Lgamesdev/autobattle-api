<?php

namespace App\Entity;

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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: CharacterEquipmentRepository::class)]
#[UniqueEntity(
    fields: ['character', 'equipment', 'equipmentSlot'],
    message: 'This equipmentSlot is already used.'
)]
class CharacterEquipment
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'equipments')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups('characterEquipment')]
    #[ManyToOne(targetEntity: Equipment::class)]
    #[JoinColumn(name: 'equipment_id', referencedColumnName: 'id')]
    private Equipment $equipment;

    #[ManyToOne(targetEntity: EquipmentSlot::class)]
    #[JoinColumn(name: 'equipmentSlot_id', referencedColumnName: 'id')]
    private EquipmentSlot $equipmentSlot;

    #[Groups('characterEquipment')]
    #[OneToMany(mappedBy: 'characterEquipment', targetEntity: CharacterEquipmentStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $modifiers;

    public function __construct()
    {
        $this->modifiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): UserCharacter
    {
        return $this->character;
    }

    public function setCharacter(UserCharacter $character): void
    {
        $this->character = $character;
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipmentSlot = $equipment->getEquipmentSlot();
        $this->equipment = $equipment;
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->equipmentSlot;
    }

    public function getModifiers(): Collection
    {
        return $this->modifiers;
    }

    public function addModifier(CharacterEquipmentStat $stat): self
    {
        if (!$this->modifiers->contains($stat)) {
            $this->modifiers[] = $stat;
            $stat->setCharacterEquipment($this);
        }

        return $this;
    }
}