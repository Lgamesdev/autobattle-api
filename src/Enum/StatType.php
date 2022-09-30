<?php

namespace App\Enum;

enum StatType: string
{
    case HEALTH = 'Health';
    case ARMOR = 'Armor';
    case DAMAGE = 'Damage';
    case DODGE = 'Dodge';
    case SPEED = 'Speed';
    case CRITICAL = 'Critical';
}