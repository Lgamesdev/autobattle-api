<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(inversedBy: 'inventory', targetEntity: UserCharacter::class)]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups('playerInventory')]
    #[ManyToMany(targetEntity: Item::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'inventorySlot')]
    private Collection $items;

    #[Groups('playerInventory')]
    #[Column(type: Types::INTEGER)]
    private int $space = 28;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCharacter(): UserCharacter
    {
        return $this->character;
    }

    public function setCharacter(UserCharacter $character): void
    {
        $this->character = $character;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item) && $this->items->count() < $this->space) {
            $this->items[] = $item;
        }

        return $this;
    }

    public function getSpace(): int
    {
        return $this->space;
    }

    public function setSpace(int $space): void
    {
        $this->space = $space;
    }
}