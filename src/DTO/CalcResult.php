<?php

namespace App\DTO;

use App\Enum\ExecutionStatus;

class CalcResult
{
    public function __construct(public ExecutionStatus $status, public array $outputs, public float $timeMs) {}
}
