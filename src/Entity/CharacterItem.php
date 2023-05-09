<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;

#[Entity(repositoryClass: CharacterItemRepository::class)]
class CharacterItem extends BaseCharacterItem
{
    #[Groups(['playerInventory'])]
    #[ManyToOne(targetEntity: Item::class)]
    #[JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    protected Item $item;

    public function __construct(Item $item = null)
    {
        if($item != null) {
            $this->item = $item;
        } else {
            $this->item = new Item();
        }
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /*#[Groups(['playerInventory'])]
    #[VirtualProperty]
    #[SerializedName('$type')]
    public function getItemType(): string
    {
        $path = explode('\\', get_called_class());
        return array_pop($path);
    }*/
}