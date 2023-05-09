<?php

namespace App\Enum;

enum InitialisationStage: string
{
    case BODY = 'Body';
    case PROGRESSION = 'Progression';
    case WALLET = 'Wallet';
    case EQUIPMENT = 'Equipment';
    case INVENTORY = 'Inventory';
    case CHARACTER_STATS = 'CharacterStats';
}