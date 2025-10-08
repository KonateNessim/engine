<?php

namespace App\DTO;

class CalcRequest
{
    public function __construct(public int $methodId,  public array $inputs = []) {}
}
