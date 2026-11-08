<?php

namespace App\Services\Bloc6;

use DateTimeImmutable;
use DateTimeZone;

final class WorkTimeCalculator
{
    public static function calculate(array $planning, ?array $pointage): array
    {
        $plannedMinutes = self::isTrue($planning['est_jour_repos'] ?? false)
            ? 0
            : (int) round(((float) ($planning['heures_prevues'] ?? 0)) * 60);

        $result = [
            'planned_minutes' => $plannedMinutes,
            'worked_minutes' => 0,
            'delay_minutes' => 0,
            'overtime_minutes' => 0,
            'complete' => false,
            'coherent' => true,
        ];

        if ($pointage === null || empty($pointage['heure_entree']) || empty($pointage['heure_sortie'])) {
            return $result;
        }

        $date = substr((string) ($pointage['date_pointage'] ?? $planning['date_travail']), 0, 10);
        $timezone = new DateTimeZone('Indian/Antananarivo');
        $entry = new DateTimeImmutable($date . ' ' . $pointage['heure_entree'], $timezone);
        $exit = new DateTimeImmutable($date . ' ' . $pointage['heure_sortie'], $timezone);
        if ($exit <= $entry) {
            $exit = $exit->modify('+1 day');
        }

        $gross = (int) round(($exit->getTimestamp() - $entry->getTimestamp()) / 60);
        $pause = max(0, (int) ($pointage['pause_minutes'] ?? 0));
        $net = $gross - $pause;
        $result['coherent'] = $gross > 0 && $gross <= 20 * 60 && $net >= 0;
        if (! $result['coherent']) {
            return $result;
        }

        $result['worked_minutes'] = $net;
        $result['complete'] = true;

        if (! empty($planning['heure_debut_prevue'])) {
            $plannedStart = new DateTimeImmutable($date . ' ' . $planning['heure_debut_prevue'], $timezone);
            $result['delay_minutes'] = max(0, (int) round(($entry->getTimestamp() - $plannedStart->getTimestamp()) / 60));
        }

        if (! empty($planning['heure_fin_prevue'])) {
            $plannedEnd = new DateTimeImmutable($date . ' ' . $planning['heure_fin_prevue'], $timezone);
            if (! empty($planning['heure_debut_prevue'])) {
                $plannedStart = new DateTimeImmutable($date . ' ' . $planning['heure_debut_prevue'], $timezone);
                if ($plannedEnd <= $plannedStart) {
                    $plannedEnd = $plannedEnd->modify('+1 day');
                }
            }
            $result['overtime_minutes'] = max(0, (int) round(($exit->getTimestamp() - $plannedEnd->getTimestamp()) / 60));
        }

        return $result;
    }

    public static function plannedEnd(array $planning): ?DateTimeImmutable
    {
        if (empty($planning['date_travail']) || empty($planning['heure_fin_prevue'])) {
            return null;
        }
        $date = substr((string) $planning['date_travail'], 0, 10);
        $timezone = new DateTimeZone('Indian/Antananarivo');
        $end = new DateTimeImmutable($date . ' ' . $planning['heure_fin_prevue'], $timezone);
        if (! empty($planning['heure_debut_prevue'])) {
            $start = new DateTimeImmutable($date . ' ' . $planning['heure_debut_prevue'], $timezone);
            if ($end <= $start) {
                $end = $end->modify('+1 day');
            }
        }
        return $end;
    }

    public static function isTrue(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true'], true);
    }
}
