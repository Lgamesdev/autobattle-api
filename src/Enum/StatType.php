<?php

namespace App\Enum;

enum StatType: string
{
    case HEALTH = 'Health';
    case ARMOR = 'Armor';
    case STRENGTH = 'Strength';
    case AGILITY = 'Agility';
    case INTELLIGENCE = 'Intelligence';
    case LUCK = 'Luck';
}