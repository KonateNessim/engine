<?php

namespace App\Service;

/**
 * Construit des messages d'erreur détaillés et cohérents pour les lignes de calcul.
 */
class ErrorMessageBuilder
{
    /**
     * Génère un message quand les conditions d’une ligne ne sont pas remplies.
     *
     * @param int|null $lineId
     * @param array $group Le groupe de conditions concerné
     */
    public function buildConditionNotMet(?int $lineId, array $group): string
    {
        $logic = strtoupper($group['logic'] ?? 'AND');
        $conds = [];

        foreach ($group['conditions'] ?? [] as $c) {
            $left = $this->stringify($c['left'] ?? '?');
            $op   = $c['op'] ?? '=';
            $right = $this->stringify($c['right'] ?? '?');
            $conds[] = sprintf("%s %s %s", $left, $op, $right);
        }

        $condStr = implode(" {$logic} ", $conds);

        return sprintf(
            "Ligne %s non exécutée : condition(s) non remplie(s) (%s).",
            $lineId ?? '?',
            $condStr ?: 'aucune condition'
        );
    }

    /**
     * Génère un message lorsqu’une erreur d’évaluation survient sur une ligne.
     *
     * @param int|null $lineId
     * @param \Throwable $e
     */
    public function buildEvaluationError(?int $lineId, \Throwable $e): string
    {
        $message = $e->getMessage();

        // Nettoyage des messages Symfony ExpressionLanguage souvent trop verbeux
        $message = preg_replace('/around position [0-9]+/', '', $message);
        $message = preg_replace('/for expression .*/', '', $message);

        return sprintf(
            "Erreur d’évaluation sur la ligne %s : %s",
            $lineId ?? '?',
            trim($message)
        );
    }

    /**
     * Convertit proprement une valeur en texte.
     */
    private function stringify(mixed $val): string
    {
        if ($val instanceof \DateTimeInterface) {
            return $val->format('Y-m-d');
        }
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }
        if (is_null($val)) {
            return 'null';
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return (string) $val;
    }
}
