<?php

namespace App\Service;

use App\Enum\OperatorType;

/**
 * Évalue une condition élémentaire entre deux valeurs (left / right).
 * Gère les comparaisons numériques, booléennes, textuelles et temporelles.
 */
class ConditionEvaluator
{
  /**
   * Évalue la condition en fonction de l’opérateur.
   *
   * @param mixed $left
   * @param OperatorType $op
   * @param mixed $right
   */
  public function eval(mixed $left, OperatorType $op, mixed $right): bool
  {
    //Normalisation : on convertit les strings numériques en nombre
    if (is_string($left) && is_numeric($left)) $left = 0 + $left;
    if (is_string($right) && is_numeric($right)) $right = 0 + $right;

    // Si les deux sont des dates, on compare les timestamps
    if ($left instanceof \DateTimeInterface && $right instanceof \DateTimeInterface) {
      $left = $left->getTimestamp();
      $right = $right->getTimestamp();
    }

    //Si l’un est une date et l’autre une string de date
    if ($left instanceof \DateTimeInterface && is_string($right) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $right)) {
      $right = strtotime($right);
      $left = $left->getTimestamp();
    } elseif ($right instanceof \DateTimeInterface && is_string($left) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $left)) {
      $left = strtotime($left);
      $right = $right->getTimestamp();
    }

    //Gestion des opérateurs logiques
    return match ($op) {
      OperatorType::Equal          => $left == $right,
      OperatorType::NotEqual       => $left != $right,
      OperatorType::GreaterThan    => $left > $right,
      OperatorType::LessThan       => $left < $right,
      OperatorType::GreaterOrEqual => $left >= $right,
      OperatorType::LessOrEqual    => $left <= $right,
      OperatorType::In             => in_array($left, (array) $right, true),
      OperatorType::NotIn          => !in_array($left, (array) $right, true),
      OperatorType::LParen,        // "("
      OperatorType::RParen         // ")"
      => true,
      default => true,
    };
  }
}
