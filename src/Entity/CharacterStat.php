<?php

namespace App\Entity;

use App\Enum\StatType;
use App\Repository\CharacterStatRepository;
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

#[Entity(repositoryClass: CharacterStatRepository::class)]
#[UniqueEntity(
    fields: ['character', 'stat'],
    message: 'This character stat already got a value'
)]
class CharacterStat
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Fighter::class, inversedBy: 'stats')]
    #[JoinColumn(name: 'fighter_id', referencedColumnName: 'id')]
    private Fighter $fighter;

    #[Column(type: 'string', enumType: StatType::class)]
    #[Exclude]
    private StatType $stat;

    #[Groups(['characterStat', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFighter(): Fighter
    {
        return $this->fighter;
    }

    public function setFighter(Fighter $fighter): void
    {
        $this->fighter = $fighter;
    }

    public function getStat(): StatType
    {
        return $this->stat;
    }

    public function setStat(StatType $stat): void
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

    #[Groups(['characterStat', 'fighter', 'opponent_fighter'])]
    #[VirtualProperty]
    #[SerializedName('statType')]
    public function getStatType(): string
    {
        return $this->stat->value;
    }
}