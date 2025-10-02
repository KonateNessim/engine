<?php

namespace App\Enum;

enum EngineStatus: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case DEPRECATED = 'DEPRECATED';
    case ARCHIVED = 'ARCHIVED';
}
