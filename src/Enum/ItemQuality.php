<?php

namespace App\Enum;

enum ItemQuality: string
{
    case NORMAL = 'normal';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';
}