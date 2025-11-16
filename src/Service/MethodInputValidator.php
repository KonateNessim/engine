<?php

namespace App\Service;

use App\Entity\Method;
use App\Entity\MethodRequirement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de validation des entrées pour les méthodes du moteur.
 * 
 * Vérifie :
 * - les champs requis
 * - les types de données
 * - les règles de validation (min, max, regex)
 * - applique les valeurs par défaut si manquantes
 */
class MethodInputValidator
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Valide les inputs d'une méthode avant exécution.
     *
     * @throws \InvalidArgumentException Si un input obligatoire est manquant ou invalide.
     */
    public function validate(Method $method, array &$inputs): void
    {
        $requirements = $this->em
            ->getRepository(MethodRequirement::class)
            ->findBy(['method' => $method]);

        if (empty($requirements)) {
            return; // aucune exigence → rien à valider
        }

        $errors = [];

        foreach ($requirements as $req) {
            $code = $req->getCode();
            $label = $req->getLabel();
           // $dataType = $req->getDataType()?->getName() ?? 'mixed';
            $required = $req->isRequired();
            $defaultValue = $req->getDefaultValue();
            $rules = $req->getValidationRules() ?? [];

            $hasValue = array_key_exists($code, $inputs);
            $value = $hasValue ? $inputs[$code] : null;

            // 1️ Présence obligatoire
            if ($required && !$hasValue) {
                if ($defaultValue !== null) {
                    $inputs[$code] = $defaultValue;
                } else {
                    $errors[] = sprintf("Le champ requis '%s' (%s) est manquant.", $label, $code);
                    continue;
                }
            }

            // 2️ Validation du type (si valeur présente)
            if ($hasValue && !$this->isTypeValid($value)) {
                $errors[] = sprintf(
                    "Type invalide pour '%s' (%s) : attendu %s, reçu %s.",
                    $label,
                    $code,
                    get_debug_type($value)
                );
                continue;
            }

            // 3 Validation des règles (min, max, regex, etc.)
            if ($hasValue && $rules) {
                $error = $this->validateRules($code, $value, $rules);
                if ($error) {
                    $errors[] = sprintf("Champ '%s' (%s) : %s", $label, $code, $error);
                }
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(' | ', $errors));
        }
    }

    // Vérifie le type de donnée
    private function isTypeValid(mixed $value, string $expected): bool
    {
        return match ($expected) {
            'integer' => is_int($value),
            'float'   => is_float($value) || is_int($value),
            'string'  => is_string($value),
            'boolean' => is_bool($value),
            'date'    => $value instanceof \DateTimeInterface || (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)),
            default   => true
        };
    }

    // Applique les contraintes min, max, regex...
    private function validateRules(string $code, mixed $value, array $rules): ?string
    {
        if (isset($rules['min']) && is_numeric($value) && $value < $rules['min']) {
            return sprintf("la valeur doit être ≥ %s", $rules['min']);
        }
        if (isset($rules['max']) && is_numeric($value) && $value > $rules['max']) {
            return sprintf("la valeur doit être ≤ %s", $rules['max']);
        }
        if (isset($rules['regex']) && is_string($value) && !preg_match($rules['regex'], $value)) {
            return sprintf("ne correspond pas au format attendu (%s)", $rules['regex']);
        }
        if (isset($rules['enum']) && !in_array($value, $rules['enum'], true)) {
            return sprintf("valeur invalide, attendue dans [%s]", implode(', ', $rules['enum']));
        }
        return null;
    }
}
