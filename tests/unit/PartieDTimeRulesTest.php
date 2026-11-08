<?php

namespace Tests\Unit;

use App\Services\Bloc6\AnomalyRules;
use App\Services\Bloc6\AnomalyTreatmentRules;
use App\Services\Bloc6\WorkTimeCalculator;
use App\Services\Security\PartieDAuthorization;
use App\Services\Bloc7\AbsenceRules;
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;

final class PartieDTimeRulesTest extends CIUnitTestCase
{
    private array $planning;

    protected function setUp(): void
    {
        parent::setUp();
        date_default_timezone_set('Indian/Antananarivo');
        $this->planning = [
            'id' => 1, 'employe_id' => 10, 'date_travail' => '2026-11-08',
            'heure_debut_prevue' => '08:00:00', 'heure_fin_prevue' => '17:00:00',
            'pause_minutes_prevue' => 60, 'heures_prevues' => 8, 'est_jour_repos' => false,
        ];
    }

    public function testNetTimeSubtractsPauseOnceAndCalculatesDelayAndOvertime(): void
    {
        $result = WorkTimeCalculator::calculate($this->planning, [
            'date_pointage' => '2026-11-08', 'heure_entree' => '08:15:00',
            'heure_sortie' => '18:00:00', 'pause_minutes' => 60,
        ]);
        $this->assertSame(525, $result['worked_minutes']);
        $this->assertSame(15, $result['delay_minutes']);
        $this->assertSame(60, $result['overtime_minutes']);
    }

    public function testOvernightShiftIsCalculatedAcrossMidnight(): void
    {
        $plan = $this->planning + [];
        $plan['heure_debut_prevue'] = '22:00:00';
        $plan['heure_fin_prevue'] = '06:00:00';
        $plan['heures_prevues'] = 7.5;
        $result = WorkTimeCalculator::calculate($plan, [
            'date_pointage' => '2026-11-08', 'heure_entree' => '22:00:00',
            'heure_sortie' => '06:00:00', 'pause_minutes' => 30,
        ]);
        $this->assertSame(450, $result['worked_minutes']);
        $this->assertTrue($result['coherent']);
    }

    public function testIncompletePunchDoesNotInventWorkedTime(): void
    {
        $result = WorkTimeCalculator::calculate($this->planning, ['date_pointage' => '2026-11-08', 'heure_entree' => '08:00:00', 'heure_sortie' => null]);
        $this->assertSame(0, $result['worked_minutes']);
        $this->assertFalse($result['complete']);
    }

    public function testMissingExitDetectedOnlyAfterShiftEnd(): void
    {
        $punch = ['date_pointage' => '2026-11-08', 'heure_entree' => '08:00:00', 'heure_sortie' => null];
        $before = AnomalyRules::detect($this->planning, $punch, new DateTimeImmutable('2026-11-08 16:00:00'));
        $after = AnomalyRules::detect($this->planning, $punch, new DateTimeImmutable('2026-11-08 18:00:00'));
        $this->assertSame([], $before);
        $this->assertSame('ENTREE_SANS_SORTIE', $after[0]['type']);
    }

    public function testMissingDayDetectedAfterEndAndRestDayIsNotAbsence(): void
    {
        $after = AnomalyRules::detect($this->planning, null, new DateTimeImmutable('2026-11-08 18:00:00'));
        $this->assertSame('JOURNEE_SANS_POINTAGE', $after[0]['type']);
        $rest = $this->planning;
        $rest['est_jour_repos'] = 't';
        $this->assertSame([], AnomalyRules::detect($rest, null, new DateTimeImmutable('2026-11-09 18:00:00')));
    }

    public function testDelayIsClassifiedSeparatelyFromClockError(): void
    {
        $found = AnomalyRules::detect($this->planning, [
            'date_pointage' => '2026-11-08', 'heure_entree' => '08:20:00',
            'heure_sortie' => '17:00:00', 'pause_minutes' => 60,
        ], new DateTimeImmutable('2026-11-08 18:00:00'));
        $this->assertSame('RETARD', $found[0]['type']);
        $this->assertStringContainsString('distinct', $found[0]['detail']);
    }

    public function testUnauthorizedRolesAreRejected(): void
    {
        $this->assertTrue(PartieDAuthorization::allows('rh'));
        $this->assertTrue(PartieDAuthorization::allows('admin'));
        $this->assertFalse(PartieDAuthorization::allows('manager'));
        $this->assertFalse(PartieDAuthorization::allows('finance'));
    }

    public function testAbsenceOverlapIncludesBoundaryDays(): void
    {
        $this->assertTrue(AbsenceRules::periodsOverlap('2026-11-01', '2026-11-05', '2026-11-05', '2026-11-08'));
        $this->assertFalse(AbsenceRules::periodsOverlap('2026-11-01', '2026-11-04', '2026-11-05', '2026-11-08'));
    }

    public function testClosedAnomalyRequiresAReason(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AnomalyTreatmentRules::assertValid('REGULARISEE', '');
    }
}
