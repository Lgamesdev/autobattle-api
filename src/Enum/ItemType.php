<?php

namespace App\Enum;

enum ItemType: string
{
    case Item = 'Item';
    case LOOTBOX = 'LootBox';
    case EQUIPMENT = 'Equipment';
}