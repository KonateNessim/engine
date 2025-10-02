<?php

namespace App\Service;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use App\Enum\OperatorType;

class LineExecutor
{
  private ExpressionLanguage $expr;
  public function __construct(private ConditionEvaluator $condEval, private ExpressionBuilder $builder)
  {
    $this->expr = new ExpressionLanguage();
  }
  public function run(array $compiledLine, array &$ctx): void
  {
    foreach (($compiledLine['groups'] ?? []) as $g) {
      $ok = ($g['logic'] ?? 'AND') === 'AND';
      foreach (($g['conditions'] ?? []) as $c) {
        $left = $ctx[$c['left']] ?? null;
        $right = is_numeric($c['right']) ? 0 + $c['right'] : ($ctx[$c['right']] ?? $c['right']);
        $res = $this->condEval->eval($left, OperatorType::from($c['op']), $right);
        if (($g['logic'] ?? 'AND') === 'AND') $ok = $ok && $res;
        else $ok = $ok || $res;
      }
      if (!$ok) return;
    }
    $expression = $compiledLine['expression'] ?? null;
    if (!$expression && !empty($compiledLine['places'])) $expression = $this->builder->build($compiledLine['places'], $ctx);
    if ($expression) {
      $value = $this->expr->evaluate($expression, $ctx);
      if (!empty($compiledLine['result'])) $ctx[$compiledLine['result']] = $value;
    }
  }
}
