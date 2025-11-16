<?php

namespace App\Service;

use App\DTO\LineResult;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use App\Enum\OperatorType;

/**
 * Service d’exécution d’une ligne compilée.
 *
 * Il évalue les conditions, construit l’expression finale (si nécessaire),
 * puis évalue cette expression et injecte le résultat dans le contexte.
 */
class LineExecutor
{
  private ExpressionLanguage $expr;

  public function __construct(
    private ConditionEvaluator $condEval,
    private ExpressionBuilder $builder,
    private DomainExpressionProvider $provider,
    private ErrorMessageBuilder $errorBuilder
  ) {
    $this->expr = new ExpressionLanguage();
    foreach ($this->provider->getFunctions() as $fn) {
      $this->expr->addFunction($fn);
    }
  }

  /**
   * Exécute une ligne de méthode compilée.
   *
   * @param array $compiledLine La ligne (expression, conditions, variables…)
   * @param array &$ctx Contexte partagé (inputs + résultats intermédiaires)
   * @return LineResult
   */
  public function run(array $compiledLine, array &$ctx): LineResult
  {
    $lineId = $compiledLine['id'] ?? null;
    $resultVar = $compiledLine['result'] ?? null;
    $expression = $compiledLine['expression'] ?? null;

    try {
      foreach ($compiledLine['groups'] ?? [] as $group) {
        $groupLogic = strtoupper($group['logic'] ?? 'AND');
        $groupResult = ($groupLogic === 'AND'); // valeur initiale selon le type de logique

        foreach ($group['conditions'] ?? [] as $cond) {
          $leftName = $cond['left'] ?? null;
          $op = OperatorType::from($cond['op'] ?? '=');
          $rightRaw = $cond['right'] ?? null;

          $left = $ctx[$leftName] ?? null;
          $right = $this->resolveRightValue($rightRaw, $ctx);

          $res = $this->condEval->eval($left, $op, $right);

          if ($groupLogic === 'AND') {
            $groupResult = $groupResult && $res;
            if (!$res) break; 
          } else {
            $groupResult = $groupResult || $res;
            if ($res) break; 
          }
        }

        if (!$groupResult) {
          return new LineResult(
            executed: false,
            lineId: $lineId,
            message: $this->errorBuilder->buildConditionNotMet($lineId, $group)
          );
        }
      }
      if (!$expression && !empty($compiledLine['places'])) {
        $expression = $this->builder->build($compiledLine['places'], $ctx);
      }
      if (!$expression) {
        return new LineResult(
          executed: false,
          lineId: $lineId,
          message: "Ligne {$lineId} ignorée : aucune expression à évaluer."
        );
      }
      $value = $this->evaluateExpressionSafely($expression, $ctx);
      if ($resultVar) {
        $ctx[$resultVar] = $value;
      }

      return new LineResult(
        executed: true,
        lineId: $lineId,
        message: sprintf("Ligne %s exécutée : %s = %s", $lineId, $resultVar, json_encode($value))
      );
    } catch (\Throwable $e) {
      // 6️⃣ Gestion d’erreur unifiée
      return new LineResult(
        executed: false,
        lineId: $lineId,
        message: $this->errorBuilder->buildEvaluationError($lineId, $e)
      );
    }
  }

  /**
   * 🔧 Tente de résoudre une valeur "droite" de condition :
   * - Si c’est une variable du contexte, la remplace
   * - Si c’est un nombre, le cast
   * - Si c’est une date (YYYY-MM-DD), la convertit en DateTimeImmutable
   */
  private function resolveRightValue(mixed $raw, array $ctx): mixed
  {
    if (is_string($raw)) {
      if (isset($ctx[$raw])) return $ctx[$raw];

      // Détection d'une date (ex: "2025-10-06")
      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        try {
          return new \DateTimeImmutable($raw);
        } catch (\Throwable) {
          return $raw;
        }
      }
    }

    if (is_numeric($raw)) return 0 + $raw;
    return $raw;
  }

  /**
   * 🧠 Sécurise l’évaluation d’une expression pour éviter les erreurs types/date
   */
  private function evaluateExpressionSafely(string $expression, array $ctx): mixed
  {
    try {
      return $this->expr->evaluate($expression, $ctx);
    } catch (\Throwable $e) {
      throw new \RuntimeException(
        sprintf("Erreur d’évaluation '%s': %s", $expression, $e->getMessage())
      );
    }
  }
}
