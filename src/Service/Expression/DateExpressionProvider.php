<?php

namespace App\Service\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class DateExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            // Ajoute la fonction "now()" → retourne un DateTimeImmutable
            new ExpressionFunction(
                'now',
                fn () => '\DateTimeImmutable::createFromFormat("Y-m-d", date("Y-m-d"))',
                fn () => new \DateTimeImmutable("now")
            ),

            // Ajoute une fonction utilitaire "year(date)" → retourne l'année
            new ExpressionFunction(
                'year',
                fn ($arg) => sprintf('(new \DateTimeImmutable(%s))->format("Y")', $arg),
                fn ($args, $date) => (new \DateTimeImmutable($date))->format("Y")
            )
        ];
    }
}
