<?php

namespace App\Service;

class ExpressionBuilder
{
  public function build(array $places, array $ctx): string
  {
    $parts = [];
    foreach ($places as $p) {
      if ($p['arg']) $parts[] = $ctx[$p['arg']] ?? 0;
      elseif ($p['op']) $parts[] = $p['op'];
      else $parts[] = $p['val'] ?? 0;
    }
    return implode(' ', $parts);
  }
}
