<?php

namespace App\Services\Bloc6;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class TempsTravailleService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function summary(string $from, string $to, array $filters = []): array
    {
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $to);
        if (! $start || ! $end || $end < $start || $end->diff($start)->days > 366) throw new InvalidArgumentException('Période invalide (maximum 366 jours).');

        $builder = $this->db->table('planning_travail pt')
            ->select('pt.*, p.id AS pointage_id, p.date_pointage, p.heure_entree, p.heure_sortie, p.pause_minutes, e.matricule, e.nom, e.prenom, e.departement_id, d.nom_departement')
            ->join('pointage p', 'p.employe_id = pt.employe_id AND p.date_pointage = pt.date_travail', 'left')
            ->join('employe e', 'e.id = pt.employe_id')->join('departement d', 'd.id = e.departement_id', 'left')
            ->where('pt.date_travail >=', $from)->where('pt.date_travail <=', $to);
        if (! empty($filters['employe_id'])) $builder->where('pt.employe_id', (int) $filters['employe_id']);
        if (! empty($filters['departement_id'])) $builder->where('e.departement_id', (int) $filters['departement_id']);
        $rows = $builder->orderBy('e.nom')->orderBy('pt.date_travail')->get()->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            if (WorkTimeCalculator::isTrue($row['est_jour_repos'] ?? false)) continue;
            $id = (int) $row['employe_id'];
            $result[$id] ??= $this->emptySummary($row);
            $punch = $row['pointage_id'] ? $row : null;
            $calc = WorkTimeCalculator::calculate($row, $punch);
            $result[$id]['planned_minutes'] += $calc['planned_minutes'];
            $result[$id]['worked_minutes'] += $calc['worked_minutes'];
            $result[$id]['delay_minutes'] += $calc['delay_minutes'];
            $result[$id]['overtime_minutes'] += $calc['overtime_minutes'];
            $result[$id]['planned_days']++;
            if ($punch !== null) $result[$id]['punched_days']++;
            $plannedEnd = WorkTimeCalculator::plannedEnd($row);
            if (! $calc['complete'] && $plannedEnd !== null && new DateTimeImmutable('now', new DateTimeZone('Indian/Antananarivo')) > $plannedEnd) $result[$id]['incomplete_days']++;
        }

        // Un pointage sans planning reste du temps réellement travaillé et doit apparaître.
        $unplanned = $this->db->table('pointage p')
            ->select('p.*, e.matricule, e.nom, e.prenom, e.departement_id, d.nom_departement')
            ->join('planning_travail pt', 'pt.employe_id = p.employe_id AND pt.date_travail = p.date_pointage', 'left')
            ->join('employe e', 'e.id = p.employe_id')->join('departement d', 'd.id = e.departement_id', 'left')
            ->where('p.date_pointage >=', $from)->where('p.date_pointage <=', $to)->where('pt.id IS NULL', null, false);
        if (! empty($filters['employe_id'])) $unplanned->where('p.employe_id', (int) $filters['employe_id']);
        if (! empty($filters['departement_id'])) $unplanned->where('e.departement_id', (int) $filters['departement_id']);
        foreach ($unplanned->get()->getResultArray() as $row) {
            $id = (int) $row['employe_id'];
            $result[$id] ??= $this->emptySummary($row);
            $syntheticPlanning = [
                'date_travail' => $row['date_pointage'], 'heures_prevues' => 0,
                'est_jour_repos' => false, 'heure_debut_prevue' => null, 'heure_fin_prevue' => null,
            ];
            $calc = WorkTimeCalculator::calculate($syntheticPlanning, $row);
            $result[$id]['worked_minutes'] += $calc['worked_minutes'];
            $result[$id]['punched_days']++;
            $today = new DateTimeImmutable('today', new DateTimeZone('Indian/Antananarivo'));
            $punchDate = new DateTimeImmutable(substr((string) $row['date_pointage'], 0, 10), new DateTimeZone('Indian/Antananarivo'));
            if (! $calc['complete'] && $punchDate < $today) $result[$id]['incomplete_days']++;
        }
        foreach ($result as &$item) $item['difference_minutes'] = $item['worked_minutes'] - $item['planned_minutes'];
        return array_values($result);
    }

    public function options(): array
    {
        return [
            'employes' => $this->db->table('employe')->select('id, matricule, nom, prenom')->where('actif', true)->orderBy('nom')->get()->getResultArray(),
            'departements' => $this->db->table('departement')->select('id, nom_departement')->orderBy('nom_departement')->get()->getResultArray(),
        ];
    }

    private function emptySummary(array $row): array
    {
        return [
            'employe_id' => (int) $row['employe_id'], 'matricule' => $row['matricule'], 'nom' => $row['nom'], 'prenom' => $row['prenom'],
            'departement' => $row['nom_departement'], 'planned_minutes' => 0, 'worked_minutes' => 0,
            'delay_minutes' => 0, 'overtime_minutes' => 0, 'incomplete_days' => 0, 'planned_days' => 0, 'punched_days' => 0,
        ];
    }
}
