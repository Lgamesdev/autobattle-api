<?php

namespace App\Enum;

enum SocketSendAction : string
{
    case SUBSCRIBE = 'subscribe';
    case UNSUBSCRIBE = 'unsubscribe';
    case INITIALISATION = 'initialisation';
    case TUTORIAL_DONE = 'tutorialDone';
    case MESSAGE = 'message';
    case MESSAGE_LIST = 'messageList';
    case SHOP_LIST = 'shopList';
    case BUY_ITEM = 'buyItem';
    case SELL_ITEM = 'sellItem';
    case RANK_LIST = 'rankList';
    case ADD_STAT_POINT = 'addStatPoint';
    case EQUIP = 'equip';
    case UN_EQUIP = 'unEquip';
    case FIGHT_START = 'fightStart';
    case ATTACK = 'attack';
    case FIGHT_OVER = 'fightOver';
    case ERROR = 'error';
}