<?php

namespace App\Enum;

enum SocketReceiveAction : string
{
    case TRY_SUBSCRIBE = 'trySubscribe';
    case TRY_UNSUBSCRIBE = 'tryUnsubscribe';
    case SEND_MESSAGE = 'sendMessage';
    case GET_SHOP_LIST = 'getShopList';
    case TRY_BUY_ITEM = 'tryBuyItem';
    case TRY_SELL_ITEM = 'trySellItem';
    case GET_RANK_LIST = 'getRankList';
    case TRY_ADD_STAT_POINT = 'tryAddStatPoint';
    case TRY_EQUIP = 'tryEquip';
    case TRY_UN_EQUIP = 'tryUnEquip';
    case TRY_ATTACK = 'tryAttack';
}