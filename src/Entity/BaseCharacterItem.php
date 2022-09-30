<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use JMS\Serializer\Annotation\Groups;

#[MappedSuperclass]
abstract class BaseCharacterItem
{
    #[Groups(['playerInventory', 'characterEquipment'])]
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'items')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    protected UserCharacter $character;

    #[Groups(['playerInventory'])]
    #[Column(type: Types::INTEGER)]
    protected int $amount = 1;

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
}