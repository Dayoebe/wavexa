<?php

namespace App\Enums;

enum MediaStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Inactive = 'inactive';
}
