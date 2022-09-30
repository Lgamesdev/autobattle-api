<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Groups;

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

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $damage = 0;

    #[Groups(['fight'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $critialHit = false;

    #[Groups(['fight'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $dodged = false;

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

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): void
    {
        $this->damage = $damage;
    }

    public function isCritialHit(): bool
    {
        return $this->critialHit;
    }

    public function setCritialHit(bool $critialHit): void
    {
        $this->critialHit = $critialHit;
    }

    public function isDodged(): bool
    {
        return $this->dodged;
    }

    public function setDodged(bool $dodged): void
    {
        $this->dodged = $dodged;
    }


}