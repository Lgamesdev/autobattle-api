<?php

namespace App\Entity;

use App\Enum\StatType;
use App\Repository\CharacterEquipmentStatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Entity(repositoryClass: CharacterEquipmentStatRepository::class)]
class CharacterEquipmentModifier
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: CharacterEquipment::class, inversedBy: 'modifiers')]
    #[JoinColumn(name: 'character_equipment_id', referencedColumnName: 'id', onDelete: "CASCADE")]
    private CharacterEquipment $characterEquipment;

    #[Column(type: 'string', enumType: StatType::class)]
    #[Exclude]
    private StatType $stat;

    #[Groups(['gear', 'playerInventory', 'fighter', 'opponent_fighter', 'lootBox'])]
    #[Column(type: Types::INTEGER)]
    private int $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacterEquipment(): CharacterEquipment
    {
        return $this->characterEquipment;
    }

    public function setCharacterEquipment(CharacterEquipment $equipment): void
    {
        $this->characterEquipment = $equipment;
    }

    public function getStat(): StatType
    {
        return $this->stat;
    }

    public function setStat(StatType $stat): void
    {
        $this->stat = $stat;
    }

    #[Groups(['gear', 'playerInventory', 'fighter', 'opponent_fighter', 'lootBox'])]
    #[VirtualProperty]
    #[SerializedName('statType')]
    public function getStatType(): string
    {
        return $this->stat->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}