<?php

namespace App\Service;

use App\DTO\LineResult;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use App\Enum\OperatorType;

class LineExecutor
{
  private ExpressionLanguage $expr;

  public function __construct(
    private ConditionEvaluator $condEval,
    private ExpressionBuilder $builder,
    DomainExpressionProvider $provider,
    private ErrorMessageBuilder $errorBuilder
  ) {
    $this->expr = new ExpressionLanguage();
    foreach ($provider->getFunctions() as $fn) {
      $this->expr->addFunction($fn);
    }
  }

  /**
   * Exécute une ligne compilée et met à jour le contexte.
   *
   * @param array $compiledLine Ligne compilée (avec expression, places, conditions…)
   * @param array $ctx Contexte des variables (inputs + résultats précédents)
   * @return LineResult
   */
  public function run(array $compiledLine, array &$ctx): LineResult
  {
    $lineId = $compiledLine['id'] ?? null;

    try {
      foreach (($compiledLine['groups'] ?? []) as $g) {
        $ok = ($g['logic'] ?? 'AND') === 'AND';
        foreach (($g['conditions'] ?? []) as $c) {
          $left = $ctx[$c['left']] ?? null;
          $right = is_numeric($c['right'])
            ? 0 + $c['right']
            : ($ctx[$c['right']] ?? $c['right']);

          $res = $this->condEval->eval($left, OperatorType::from($c['op']), $right);

          if (($g['logic'] ?? 'AND') === 'AND') {
            $ok = $ok && $res;
          } else {
            $ok = $ok || $res;
          }
        }

        if (!$ok) {
          return new LineResult(
            lineId: $lineId,
            executed: false,
            message: $this->errorBuilder->buildConditionNotMet($lineId, $g)
          );
        }
      }
      $expression = $compiledLine['expression'] ?? null;
      if (!$expression && !empty($compiledLine['places'])) {
        $expression = $this->builder->build($compiledLine['places'], $ctx);
      }
      if ($expression) {
        $value = $this->expr->evaluate($expression, $ctx);

        if (!empty($compiledLine['result'])) {
          $ctx[$compiledLine['result']] = $value;
        }

        return new LineResult(
          executed: true,
          lineId: $lineId,
          message: "Ligne {$lineId} exécutée avec succès : {$compiledLine['result']} = {$value}"
        );
      }

      return new LineResult(
        executed: false,
        lineId: $lineId,
        message: "Ligne {$lineId} ignorée : pas d’expression."
      );
    } catch (\Throwable $e) {
      return new LineResult(
        executed: false,
        lineId: $lineId,
        message: $this->errorBuilder->buildEvaluationError($lineId, $e)
      );
    }
  }
}
