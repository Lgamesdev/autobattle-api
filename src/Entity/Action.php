<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Enum\FightActionType;
use App\Repository\ActionRepository;
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

#[Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Fight::class, inversedBy: 'actions')]
    #[JoinColumn(name: 'fight_id', referencedColumnName: 'id')]
    private Fight $fight;

    #[Groups(['fight'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $playerTeam = true;

    #[Exclude]
    #[Column(type: 'string', enumType: FightActionType::class)]
    private FightActionType $actionType;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $damage = 0;

    #[Groups(['fight'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $criticalHit = false;

    #[Groups(['fight'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $dodged = false;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $energyGained = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFight(): Fight
    {
        return $this->fight;
    }

    public function setFight(Fight $fight): void
    {
        $this->fight = $fight;
    }

    public function isPlayerTeam(): bool
    {
        return $this->playerTeam;
    }

    public function setPlayerTeam(bool $playerTeam): void
    {
        $this->playerTeam = $playerTeam;
    }

    public function getActionType(): FightActionType
    {
        return $this->actionType;
    }

    public function setActionType(FightActionType $actionType): void
    {
        $this->actionType = $actionType;
    }

    #[Groups(['fight'])]
    #[VirtualProperty]
    #[SerializedName('actionType')]
    public function getActionTypeValue(): string
    {
        return $this->actionType->value;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): void
    {
        $this->damage = $damage;
    }

    public function isCriticalHit(): bool
    {
        return $this->criticalHit;
    }

    public function setCriticalHit(bool $criticalHit): void
    {
        $this->criticalHit = $criticalHit;
    }

    public function isDodged(): bool
    {
        return $this->dodged;
    }

    public function setDodged(bool $dodged): void
    {
        $this->dodged = $dodged;
    }

    public function getEnergyGained(): int
    {
        return $this->energyGained;
    }

    public function setEnergyGained(int $energyGained): void
    {
        $this->energyGained = $energyGained;
    }
}