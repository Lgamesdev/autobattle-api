<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use App\Repository\CharacterStatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'stats')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups('characterStat')]
    #[ManyToOne(targetEntity: Statistic::class)]
    #[JoinColumn(name: 'stat_id', referencedColumnName: 'id')]
    private Statistic $stat;

    #[Groups('characterStat')]
    #[Column(type: Types::INTEGER)]
    private int $value;

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