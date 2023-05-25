<?php

namespace App\Enum;

enum FightActionType: string
{
    case ATTACK = 'Attack';
    case PARRY = 'Parry';
    case SPECIAL_ATTACK = 'SpecialAttack';
}