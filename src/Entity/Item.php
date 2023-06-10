<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ItemType;
use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;

#[Entity(repositoryClass: ItemRepository::class)]
class Item extends BaseItem
{
    #[Exclude]
    protected ItemType $itemType = ItemType::Item;

    #[Groups(['playerInventory', 'shopList', 'lootBox'])]
    #[Column(type: Types::INTEGER)]
    protected int $cost;

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }
}