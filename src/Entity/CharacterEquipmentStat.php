<?php

namespace App\Entity;

use App\Repository\CharacterEquipmentStatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: CharacterEquipmentStatRepository::class)]
#[UniqueEntity(
    fields: ['character', 'equipment', 'equipmentSlot'],
    message: 'This equipmentSlot is already used.'
)]
class CharacterEquipmentStat
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Groups('characterEquipment')]
    #[ManyToOne(targetEntity: CharacterEquipment::class, inversedBy: 'modifiers')]
    private CharacterEquipment $characterEquipment;

    #[Groups('characterEquipment')]
    #[ManyToOne(targetEntity: Statistic::class)]
    private Statistic $stat;

    #[Groups('characterEquipment')]
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

    public function getStat(): Statistic
    {
        return $this->stat;
    }

    public function setStat(Statistic $stat): void
    {
        $this->stat = $stat;
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