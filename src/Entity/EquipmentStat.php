<?php

namespace App\Entity;

use App\Enum\StatType;
use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Entity(repositoryClass: CharacterRepository::class)]
#[UniqueEntity(
    fields: ['equipment', 'stat'],
    message: 'This equipment stat already got a value'
)]
class EquipmentStat
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Equipment::class, inversedBy: 'stats')]
    private Equipment $equipment;

    #[Column(type: 'string', enumType: StatType::class)]
    #[Exclude]
    private StatType $stat;

    #[Groups(['gear', 'playerInventory', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
    #[Column(type: Types::INTEGER)]
    private int $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipment = $equipment;
    }

    public function getStat(): StatType
    {
        return $this->stat;
    }

    public function setStat(StatType $stat): void
    {
        $this->stat = $stat;
    }

    #[Groups(['gear', 'fighter', 'opponent_fighter', 'playerInventory', 'shopList'])]
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