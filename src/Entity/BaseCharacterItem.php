<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BaseCharacterItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Groups;

#[Entity(repositoryClass: BaseCharacterItemRepository::class)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[DiscriminatorMap([
    'character_item' => CharacterItem::class,
    'character_equipment' => CharacterEquipment::class,
    'character_lootbox' => CharacterLootBox::class,
])]
abstract class BaseCharacterItem
{
    #[Groups(['playerInventory', 'gear', 'lootBox'])]
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'items')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    protected UserCharacter $character;

    #[Groups(['playerInventory', 'lootBox'])]
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}