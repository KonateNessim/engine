<?php

namespace App\Service;

use App\DTO\CalcRequest;
use App\DTO\CalcResult;
use App\DTO\LineResult;
use App\Enum\ExecutionStatus;

class CalculationService
{
  public function __construct(
    private CompiledVersionCache $cache,
    private LineExecutor $executor
  ) {}

  public function calculate(CalcRequest $req): CalcResult
  {
    $compiled = $this->cache->get($req->methodId, $req->versionNumber);

    $ctx = $req->inputs;
    $messages = [];
    $start = microtime(true);

    foreach ($compiled['lines'] as $line) {
      $result = $this->executor->run($line, $ctx);
      if ($result instanceof LineResult) {
        $messages[] = [
          'lineId'   => $result->getLineId(),
          'executed' => $result->isExecuted(),
          'message'  => $result->getMessage()
        ];
      }
    }
    $time = (microtime(true) - $start) * 1000.0;
    $ctx['messages'] = $messages;

    return new CalcResult(ExecutionStatus::SUCCESS, $ctx, $time);
  }
}
