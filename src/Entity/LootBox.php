<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ItemType;
use Doctrine\ORM\Mapping\Entity;
use JMS\Serializer\Annotation\Exclude;

#[Entity()]
class LootBox extends Item
{
    #[Exclude]
    protected ItemType $itemType = ItemType::LOOTBOX;
}