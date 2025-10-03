<?php

namespace App\Service;

use App\Enum\OperatorType;

class ConditionEvaluator
{
  public function eval($left, OperatorType $op, $right): bool
  {
    if (is_string($right) && is_numeric($right)) $right = 0 + $right;
    if (is_string($left) && is_numeric($left)) $left = 0 + $left;
    return match ($op) {
      OperatorType::Equal => $left == $right,
      OperatorType::NotEqual => $left != $right,
      OperatorType::GreaterThan => $left > $right,
      OperatorType::LessThan => $left < $right,
      OperatorType::GreaterOrEqual => $left >= $right,
      OperatorType::LessOrEqual => $left <= $right,
      OperatorType::In => in_array($left, (array)$right, true),
      OperatorType::NotIn => !in_array($left, (array)$right, true),
      OperatorType::LParen, // "("
      OperatorType::RParen  // ")"
      => true,
      default => true
    };
  }
}
