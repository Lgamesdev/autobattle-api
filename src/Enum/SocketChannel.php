<?php

namespace App\Enum;

enum SocketChannel : string
{
    case DEFAULT = 'general';
    case CHAT_DEFAULT = 'chat_general';
    case FIGHT_SUFFIX = 'fight_';
}