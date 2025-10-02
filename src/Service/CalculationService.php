<?php

namespace App\Service;

use App\DTO\CalcRequest;
use App\DTO\CalcResult;
use App\Enum\ExecutionStatus;

class CalculationService
{
  public function __construct(private CompiledVersionCache $cache, private LineExecutor $executor) {}
  public function calculate(CalcRequest $req): CalcResult
  {
    $compiled = $this->cache->get($req->methodId, $req->versionNumber);
    $ctx = $req->inputs;
    $start = microtime(true);
    foreach ($compiled['lines'] as $line) {
      $this->executor->run($line, $ctx);
    }
    $time = (microtime(true) - $start) * 1000.0;
    return new CalcResult(ExecutionStatus::SUCCESS, $ctx, $time);
  }
}
