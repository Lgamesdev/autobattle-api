<?php

namespace App\Enum;

enum SocketAction : string
{
    case SUBSCRIBE = 'subscribe';
    case UNSUBSCRIBE = 'unsubscribe';
    case MESSAGE = 'message';
    case MESSAGE_LIST = 'messageList';
    case SHOP_LIST = 'shopList';
    case RANK_LIST = 'rankList';
    case EQUIP = 'equip';
    case UN_EQUIP = 'unEquip';
    case FIGHT_START = 'fightStart';
    case ATTACK = 'attack';
    case FIGHT_OVER = 'fightOver';
    case ERROR = 'error';
}