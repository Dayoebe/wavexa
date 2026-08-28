<?php

namespace App\Enums;

enum StreamStatus: string
{
    case Unknown = 'unknown';
    case Online = 'online';
    case Offline = 'offline';
}
