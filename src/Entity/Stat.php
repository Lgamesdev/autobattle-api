<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: StatRepository::class)]
class Stat
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Equipment::class, inversedBy: 'stats')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Equipment $equipment;

    #[Column(type: Types::INTEGER)]
    private int $baseValue;

    #[ManyToOne(targetEntity: StatType::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private StatType $type;

    #[Column(type: 'array', nullable: true)]
    private array $modifiers = [];

    public function __construct(StatType $type, int $baseValue)
    {
        $this->type = $type;
        $this->baseValue = $baseValue;
    }

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

    public function getBaseValue(): int
    {
        return $this->baseValue;
    }

    public function setBaseValue(int $baseValue): void
    {
        $this->baseValue = $baseValue;
    }

    public function getType(): StatType
    {
        return $this->type;
    }

    public function setType(StatType $type): void
    {
        $this->type = $type;
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