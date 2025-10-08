<?php

namespace App\Service;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class DomainExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            //  Calcul de l'âge en années
            new ExpressionFunction(
                'age',
                fn($date) => sprintf('(new \DateTimeImmutable())->diff(%s)->y', $date),
                fn(array $values, $date) => $date instanceof \DateTimeInterface ? (new \DateTimeImmutable())->diff($date)->y : null
            ),

            //  Différence en mois
            new ExpressionFunction(
                'monthsDiff',
                fn($d1, $d2) => sprintf('%s->diff(%s)->m + (%s->diff(%s)->y * 12)', $d1, $d2, $d1, $d2),
                fn(array $values, $d1, $d2) => ($d1 instanceof \DateTimeInterface && $d2 instanceof \DateTimeInterface) ?
                    ($d1->diff($d2)->y * 12 + $d1->diff($d2)->m) : null
            ),
            //  Différence en jours
            new ExpressionFunction(
                'daysDiff',
                fn($d1, $d2) => sprintf('%s->diff(%s)->days', $d1, $d2),
                fn(array $values, $d1, $d2) => ($d1 instanceof \DateTimeInterface && $d2 instanceof \DateTimeInterface) ?
                    $d1->diff($d2)->days : null
            ),
            //  Année d'une date
            new ExpressionFunction(
                'year',
                fn($d) => sprintf('%s->format("Y")', $d),
                fn(array $values, $d) => $d instanceof \DateTimeInterface ? (int) $d->format('Y') : null
            ),
            //  Mois d’une date
            new ExpressionFunction(
                'month',
                fn($d) => sprintf('%s->format("m")', $d),
                fn(array $values, $d) => $d instanceof \DateTimeInterface ? (int) $d->format('m') : null
            ),
            //  Jour d’une date
            new ExpressionFunction(
                'day',
                fn($d) => sprintf('%s->format("d")', $d),
                fn(array $values, $d) => $d instanceof \DateTimeInterface ? (int) $d->format('d') : null
            ),
        ];
    }
}
