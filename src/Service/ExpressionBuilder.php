<?php
namespace App\Service;
use App\Enum\OperatorType;
class ExpressionBuilder {
  public function build(array $places, array $ctx): string {
    $parts=[];
    foreach ($places as $p) {
      $op = $p['op'] ?? null;
      if ($op === OperatorType::LParen->value) { $parts[]='('; continue; }
      if ($op === OperatorType::RParen->value) { $parts[]=')'; continue; }
      if (!empty($p['arg'])) { $parts[] = $ctx[$p['arg']] ?? $p['arg']; }
      elseif (!empty($p['op'])) { $parts[] = $p['op']; }
      elseif (!empty($p['val'])) { $parts[] = $p['val']; }
    }
    return implode(' ', $parts);
  }
}
