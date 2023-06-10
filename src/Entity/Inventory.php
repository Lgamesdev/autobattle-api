<?php

namespace App\Entity;

use App\Exception\UserCharacterException;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

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

    #[Groups(['playerInventory'])]
    #[ManyToMany(targetEntity: BaseCharacterItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'inventory_items')]
    #[JoinColumn(name: 'inventory_id', referencedColumnName: 'id', onDelete: "CASCADE")]
    #[InverseJoinColumn(name: 'character_item_id', referencedColumnName: 'id', unique: true, onDelete: "CASCADE")]
    #[Type("ArrayCollection<App\Entity\BaseCharacterItem>")]
    private Collection $items;

    #[Groups(['playerInventory'])]
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

    /**
     * @throws UserCharacterException
     */
    public function addCharacterItem(BaseCharacterItem $characterItem): self
    {
        if ($this->items->count() < $this->space) {
            if($characterItem instanceof CharacterItem || $characterItem instanceof CharacterLootBox) {
                $itemAlreadyInInventory = false;
                /** @var BaseCharacterItem $item */
                foreach ($this->items as $item) {
                    //echo "inventory item id : " . $item->getItem()->getId() . " characterItem item id : " . $characterItem->getItem()->getId() . "\n";
                    if($item->getItem()->getId() == $characterItem->getItem()->getId())
                    {
                        $item->setAmount($item->getAmount() + $characterItem->getAmount());
                        $itemAlreadyInInventory = true;
                    }
                }

                if(!$itemAlreadyInInventory) {
                    $this->items[] = $characterItem;
                    $characterItem->setCharacter($this->character);
                }
            } else {
                $this->items[] = $characterItem;
                $characterItem->setCharacter($this->character);
            }
        }else {
            throw new UserCharacterException("no enough space in inventory");
        }

        return $this;
    }

    /**
     * @throws UserCharacterException
     */
    public function removeCharacterItem(BaseCharacterItem $characterItem): self
    {
        if ($this->items->contains($characterItem)) {
            $characterItem->setAmount($characterItem->getAmount() - 1);

            if($characterItem->getAmount() < 1)
            {
                $this->items->removeElement($characterItem);
            }
        } else {
            throw new UserCharacterException("you can't remove an item that is not in your inventory");
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