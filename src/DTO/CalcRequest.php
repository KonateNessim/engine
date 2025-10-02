<?php
namespace App\DTO; class CalcRequest { public function __construct(public int $methodId, public string $versionNumber='v1', public array $inputs=[]){} }