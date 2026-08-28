<?php

namespace App\Enums;

enum ArtworkKind: string
{
    case Logo = 'logo';
    case Cover = 'cover';
    case Banner = 'banner';
    case Thumbnail = 'thumbnail';
}
