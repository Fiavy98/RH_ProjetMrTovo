<?php

namespace App\Services\Bloc6;

use DateTimeImmutable;

final class AnomalyRules
{
    public static function detect(?array $planning, ?array $pointage, DateTimeImmutable $now): array
    {
        $found = [];
        if ($planning !== null && WorkTimeCalculator::isTrue($planning['est_jour_repos'] ?? false)) {
            if ($pointage !== null && (! empty($pointage['heure_entree']) || ! empty($pointage['heure_sortie']))) {
                $found[] = ['type' => 'ECART_A_VERIFIER', 'detail' => 'Un pointage existe pendant une journée déclarée en repos.'];
            }
            return $found;
        }

        $shiftEnded = $planning === null || (($end = WorkTimeCalculator::plannedEnd($planning)) !== null && $now > $end);
        if ($planning !== null && $pointage === null && $shiftEnded) {
            return [['type' => 'JOURNEE_SANS_POINTAGE', 'detail' => 'La journée planifiée est terminée sans aucun pointage.']];
        }
        if ($pointage === null) {
            return [];
        }

        $hasEntry = ! empty($pointage['heure_entree']);
        $hasExit = ! empty($pointage['heure_sortie']);
        if ($hasEntry && ! $hasExit && $shiftEnded) {
            $found[] = ['type' => 'ENTREE_SANS_SORTIE', 'detail' => 'Une entrée est enregistrée mais la sortie manque après la fin de la journée.'];
        }
        if (! $hasEntry && $hasExit) {
            $found[] = ['type' => 'SORTIE_SANS_ENTREE', 'detail' => 'Une sortie est enregistrée sans heure d’entrée.'];
        }

        if ($planning !== null && $hasEntry && $hasExit) {
            $calc = WorkTimeCalculator::calculate($planning, $pointage);
            if (! $calc['coherent']) {
                $found[] = ['type' => 'HEURES_INCOHERENTES', 'detail' => 'La durée, le passage de minuit ou la pause produit un temps incohérent.'];
            } else {
                if ($calc['delay_minutes'] > 0) {
                    $found[] = ['type' => 'RETARD', 'detail' => 'Arrivée ' . $calc['delay_minutes'] . ' minute(s) après l’horaire planifié (retard connu, distinct d’une erreur de pointage).'];
                }
                if (abs($calc['worked_minutes'] - $calc['planned_minutes']) > 240) {
                    $found[] = ['type' => 'ECART_A_VERIFIER', 'detail' => 'Écart supérieur à 4 heures entre le temps prévu et le temps net pointé.'];
                }
            }
        } elseif ($planning === null && $hasEntry && $hasExit) {
            $found[] = ['type' => 'ECART_A_VERIFIER', 'detail' => 'Pointage enregistré sans planning associé.'];
        }

        return $found;
    }
}
