<?php

require dirname(__DIR__) . '/app/Services/Bloc6/WorkTimeCalculator.php';
require dirname(__DIR__) . '/app/Services/Bloc6/AnomalyRules.php';
require dirname(__DIR__) . '/app/Services/Bloc6/AnomalyTreatmentRules.php';
require dirname(__DIR__) . '/app/Services/Security/PartieDAuthorization.php';
require dirname(__DIR__) . '/app/Services/Bloc7/AbsenceRules.php';

date_default_timezone_set('Indian/Antananarivo');

use App\Services\Bloc6\AnomalyRules;
use App\Services\Bloc6\WorkTimeCalculator;
use App\Services\Bloc6\AnomalyTreatmentRules;
use App\Services\Security\PartieDAuthorization;
use App\Services\Bloc7\AbsenceRules;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (! $condition) {
        fwrite(STDERR, "ECHEC: {$message}\n");
        exit(1);
    }
};

$planning = [
    'date_travail' => '2026-11-08', 'heure_debut_prevue' => '08:00:00',
    'heure_fin_prevue' => '17:00:00', 'heures_prevues' => 8, 'est_jour_repos' => false,
];
$calculation = WorkTimeCalculator::calculate($planning, [
    'date_pointage' => '2026-11-08', 'heure_entree' => '08:15:00',
    'heure_sortie' => '18:00:00', 'pause_minutes' => 60,
]);
$assert($calculation['worked_minutes'] === 525, 'la pause doit être soustraite une seule fois');
$assert($calculation['delay_minutes'] === 15, 'le retard doit suivre le planning');
$assert($calculation['overtime_minutes'] === 60, 'les heures supplémentaires doivent suivre la fin planifiée');

$night = $planning;
$night['heure_debut_prevue'] = '22:00:00';
$night['heure_fin_prevue'] = '06:00:00';
$nightResult = WorkTimeCalculator::calculate($night, [
    'date_pointage' => '2026-11-08', 'heure_entree' => '22:00:00',
    'heure_sortie' => '06:00:00', 'pause_minutes' => 30,
]);
$assert($nightResult['worked_minutes'] === 450, 'le passage de minuit doit être géré');

$incomplete = WorkTimeCalculator::calculate($planning, ['date_pointage' => '2026-11-08', 'heure_entree' => '08:00:00', 'heure_sortie' => null]);
$assert($incomplete['worked_minutes'] === 0 && ! $incomplete['complete'], 'un pointage incomplet ne doit pas inventer une durée');

$before = AnomalyRules::detect($planning, ['heure_entree' => '08:00:00', 'heure_sortie' => null], new \DateTimeImmutable('2026-11-08 16:00:00'));
$after = AnomalyRules::detect($planning, ['heure_entree' => '08:00:00', 'heure_sortie' => null], new \DateTimeImmutable('2026-11-08 18:00:00'));
$assert($before === [], 'une sortie manquante ne doit pas être signalée avant la fin');
$assert(($after[0]['type'] ?? '') === 'ENTREE_SANS_SORTIE', 'une sortie manquante doit être détectée après la fin');

$missing = AnomalyRules::detect($planning, null, new \DateTimeImmutable('2026-11-08 18:00:00'));
$assert(($missing[0]['type'] ?? '') === 'JOURNEE_SANS_POINTAGE', 'une journée terminée sans pointage doit être détectée');
$rest = $planning;
$rest['est_jour_repos'] = 't';
$assert(AnomalyRules::detect($rest, null, new \DateTimeImmutable('2026-11-09 18:00:00')) === [], 'un jour de repos ne doit pas être déclaré absent');

$delay = AnomalyRules::detect($planning, [
    'date_pointage' => '2026-11-08', 'heure_entree' => '08:20:00',
    'heure_sortie' => '17:00:00', 'pause_minutes' => 60,
], new \DateTimeImmutable('2026-11-08 18:00:00'));
$assert(($delay[0]['type'] ?? '') === 'RETARD', 'un retard doit être distinct d’une erreur de pointage');
$assert(PartieDAuthorization::allows('rh') && PartieDAuthorization::allows('admin'), 'RH et Admin doivent être autorisés');
$assert(! PartieDAuthorization::allows('manager') && ! PartieDAuthorization::allows('finance'), 'les rôles non autorisés doivent être refusés');
$assert(AbsenceRules::periodsOverlap('2026-11-01', '2026-11-05', '2026-11-05', '2026-11-08'), 'un chevauchement incluant les bornes doit être refusé');
$assert(! AbsenceRules::periodsOverlap('2026-11-01', '2026-11-04', '2026-11-05', '2026-11-08'), 'deux périodes disjointes doivent être acceptées');
$reasonRequired = false;
try {
    AnomalyTreatmentRules::assertValid('REGULARISEE', '');
} catch (InvalidArgumentException) {
    $reasonRequired = true;
}
$assert($reasonRequired, 'une régularisation doit exiger un motif');
AnomalyTreatmentRules::assertValid('REGULARISEE', 'Sortie vérifiée avec le salarié');
$assert(true, 'une régularisation motivée doit être acceptée');

fwrite(STDOUT, "OK - {$assertions} assertions Partie D\n");
