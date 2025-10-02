<?php

namespace App\Enum;

enum ExecutionStatus: string
{
    case SUCCESS = 'SUCCESS';
    case ERROR = 'ERROR';
    case TIMEOUT = 'TIMEOUT';
    case SKIPPED = 'SKIPPED';
}
