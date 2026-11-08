<?php

namespace App\Services\Bloc6;

use InvalidArgumentException;

final class AnomalyTreatmentRules
{
    public static function assertValid(string $status, string $reason): void
    {
        if (! in_array($status, ['A_EXAMINER', 'EN_COURS', 'REGULARISEE', 'IGNOREE'], true)) {
            throw new InvalidArgumentException('Statut invalide.');
        }
        if (in_array($status, ['REGULARISEE', 'IGNOREE'], true) && trim($reason) === '') {
            throw new InvalidArgumentException('Le motif est obligatoire pour clôturer une anomalie.');
        }
    }
}
