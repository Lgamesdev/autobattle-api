<?php

namespace App\Entity;

use App\Repository\CharacterEquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
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

    #[Groups('characterEquipment')]
    #[ManyToOne(targetEntity: EquipmentSlot::class)]
    #[JoinColumn(name: 'equipmentSlot_id', referencedColumnName: 'id')]
    private EquipmentSlot $equipmentSlot;

    #[Groups('characterEquipment')]
    #[Column(type: 'array', nullable: true)]
    private array $modifiers = [];

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
        $this->equipment = $equipment;
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->equipmentSlot;
    }

    public function setEquipmentSlot(EquipmentSlot $equipmentSlot): void
    {
        $this->equipmentSlot = $equipmentSlot;
    }

    public function getModifiers(): ?array
    {
        return $this->modifiers;
    }

    public function addModifier(int $value): void
    {
        if ($value != 0) {
            $this->modifiers[] = $value;
        }
    }

}