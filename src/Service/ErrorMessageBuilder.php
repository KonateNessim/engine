<?php

namespace App\Service;

class ErrorMessageBuilder
{
    public function buildConditionNotMet(?int $lineId, array $group): string
    {
        $logic = $group['logic'] ?? 'AND';
        $conds = [];
        foreach ($group['conditions'] ?? [] as $c) {
            $conds[] = sprintf("%s %s %s", $c['left'], $c['op'], $c['right']);
        }
        $condStr = implode(" $logic ", $conds);

        return "Condition non remplie sur la ligne {$lineId} : {$condStr}";
    }

    public function buildEvaluationError(?int $lineId, \Throwable $e): string
    {
        return "Erreur d’évaluation sur la ligne {$lineId} : " . $e->getMessage();
    }
}
